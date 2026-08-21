<?php

// Cleanup script: remove stale UAT records from the database
// Run with: php artisan tinker < database/cleanup_stale_uat.php

use App\Models\CulturalEvent;
use App\Models\EventTicketType;
use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use App\Models\TourismService;

// 1. Delete stale UAT events and their tickets
$oldEventNames = ['UAT Gondar Cultural Festival', 'UAT Heritage Celebration'];
$oldEvents = CulturalEvent::whereIn('event_name', $oldEventNames)->get();
foreach ($oldEvents as $event) {
    EventTicketType::where('event_id', $event->event_id)->delete();
    $event->delete();
}
echo 'Deleted '.count($oldEvents)." stale events\n";

// 2. Delete stale UAT services and their room types/rooms
$oldServiceNames = [
    'UAT Standard Room',
    'UAT Deluxe Room',
    'UAT Gondar Dining Reservation',
    'UAT Gondar Coffee & Breakfast',
    'UAT Gondar Car Rental',
    'UAT Gondar Cultural Festival',
    'UAT Heritage Celebration',
];
$oldServices = TourismService::whereIn('service_name', $oldServiceNames)->get();
foreach ($oldServices as $service) {
    $roomType = HotelRoomType::where('service_id', $service->service_id)->first();
    if ($roomType) {
        HotelRoom::where('room_type_id', $roomType->room_type_id)->delete();
        $roomType->delete();
    }
    $service->delete();
}
echo 'Deleted '.count($oldServices)." stale services\n";

// 3. Verify no UAT names remain
$remaining = TourismService::where('service_name', 'like', 'UAT%')->count();
$remainingEvents = CulturalEvent::where('event_name', 'like', 'UAT%')->count();
echo "Remaining UAT services: $remaining\n";
echo "Remaining UAT events: $remainingEvents\n";
