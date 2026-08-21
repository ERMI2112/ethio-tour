<?php

namespace App\Console\Commands;

use App\Models\CulturalEvent;
use App\Models\EventTicketType;
use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use App\Models\TourismService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupStaleUat extends Command
{
    protected $signature = 'cleanup:stale-uat';

    protected $description = 'Remove stale UAT-prefixed records left by prior seeder runs';

    public function handle(): int
    {
        // 1. Delete stale UAT events — handle full dependency chain
        $oldEvents = CulturalEvent::where('event_name', 'like', 'UAT%')->get();
        foreach ($oldEvents as $event) {
            $ticketIds = EventTicketType::where('event_id', $event->event_id)->pluck('ticket_type_id');

            if (Schema::hasTable('event_reservations') && $ticketIds->isNotEmpty()) {
                // Get booking IDs from event_reservations before deleting
                $bookingIds = DB::table('event_reservations')
                    ->whereIn('ticket_type_id', $ticketIds)
                    ->pluck('booking_id');

                DB::table('event_reservations')->whereIn('ticket_type_id', $ticketIds)->delete();

                // Clean up orphaned bookings from those reservations
                if (Schema::hasTable('bookings') && $bookingIds->isNotEmpty()) {
                    // Delete reviews first
                    if (Schema::hasTable('reviews')) {
                        DB::table('reviews')->whereIn('booking_id', $bookingIds)->delete();
                    }
                    // Delete payments first
                    if (Schema::hasTable('payments')) {
                        DB::table('payments')->whereIn('booking_id', $bookingIds)->delete();
                    }
                    DB::table('bookings')->whereIn('booking_id', $bookingIds)->delete();
                }
            }

            EventTicketType::where('event_id', $event->event_id)->delete();
            $event->delete();
        }
        $this->info("Deleted {$oldEvents->count()} stale UAT events");

        // 2. Delete stale UAT services and their room types/rooms
        $oldServices = TourismService::where('service_name', 'like', 'UAT%')->get();
        foreach ($oldServices as $service) {
            // Delete bookings referencing this service
            if (Schema::hasTable('bookings')) {
                $bookingIds = DB::table('bookings')->where('service_id', $service->service_id)->pluck('booking_id');
                if ($bookingIds->isNotEmpty()) {
                    if (Schema::hasTable('reviews')) {
                        DB::table('reviews')->whereIn('booking_id', $bookingIds)->delete();
                    }
                    if (Schema::hasTable('payments')) {
                        DB::table('payments')->whereIn('booking_id', $bookingIds)->delete();
                    }
                    if (Schema::hasTable('hotel_reservations')) {
                        DB::table('hotel_reservations')->whereIn('booking_id', $bookingIds)->delete();
                    }
                    if (Schema::hasTable('restaurant_reservations')) {
                        DB::table('restaurant_reservations')->whereIn('booking_id', $bookingIds)->delete();
                    }
                    if (Schema::hasTable('transportation_reservations')) {
                        DB::table('transportation_reservations')->whereIn('booking_id', $bookingIds)->delete();
                    }
                    DB::table('bookings')->whereIn('booking_id', $bookingIds)->delete();
                }
            }

            $roomType = HotelRoomType::where('service_id', $service->service_id)->first();
            if ($roomType) {
                HotelRoom::where('room_type_id', $roomType->room_type_id)->delete();
                $roomType->delete();
            }

            if (Schema::hasTable('transportation_vehicles')) {
                DB::table('transportation_vehicles')->where('service_id', $service->service_id)->delete();
            }

            $service->delete();
        }
        $this->info("Deleted {$oldServices->count()} stale UAT services");

        // 3. Verify
        $remainingServices = TourismService::where('service_name', 'like', 'UAT%')->count();
        $remainingEvents = CulturalEvent::where('event_name', 'like', 'UAT%')->count();
        $this->info("Remaining: {$remainingServices} UAT services, {$remainingEvents} UAT events");

        return self::SUCCESS;
    }
}
