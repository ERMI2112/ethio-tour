<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\HotelRoom;
use App\Models\HotelRoomReservation;
use App\Models\HotelRoomType;
use App\Models\Payment;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HotelRoomInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_hotel_room_type_belongs_to_a_tourism_service_and_casts_amenities(): void
    {
        $context = $this->hotelContext();
        $roomType = HotelRoomType::create([
            'service_id' => $context['service']->service_id,
            'capacity' => 2,
            'amenities' => ['Wi-Fi', 'TV', 'Private Bathroom'],
        ]);

        $this->assertTrue($roomType->tourismService->is($context['service']));
        $this->assertSame(['Wi-Fi', 'TV', 'Private Bathroom'], $roomType->fresh()->amenities);
        $this->assertTrue($context['service']->fresh()->hotelRoomType->is($roomType));
    }

    public function test_room_type_service_is_unique_and_capacity_must_be_positive(): void
    {
        $context = $this->hotelContext();
        HotelRoomType::create(['service_id' => $context['service']->service_id, 'capacity' => 2, 'amenities' => []]);

        try {
            HotelRoomType::create(['service_id' => $context['service']->service_id, 'capacity' => 3, 'amenities' => []]);
            $this->fail('A tourism service must have only one hotel room type.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }

        try {
            HotelRoomType::create(['service_id' => $this->hotelService($context, 'Deluxe Room')->service_id, 'capacity' => 0, 'amenities' => []]);
            $this->fail('Zero capacity must be rejected.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $this->expectException(ValidationException::class);
        HotelRoomType::create(['service_id' => $this->hotelService($context, 'Suite')->service_id, 'capacity' => -1, 'amenities' => []]);
    }

    public function test_room_types_have_multiple_rooms_with_unique_numbers_per_room_type(): void
    {
        $context = $this->hotelContext();
        $standard = HotelRoomType::create(['service_id' => $context['service']->service_id, 'capacity' => 2, 'amenities' => []]);
        $deluxe = HotelRoomType::create(['service_id' => $this->hotelService($context, 'Deluxe Room')->service_id, 'capacity' => 3, 'amenities' => []]);

        $room101 = HotelRoom::create(['room_type_id' => $standard->room_type_id, 'room_number' => '101']);
        HotelRoom::create(['room_type_id' => $standard->room_type_id, 'room_number' => '102', 'status' => 'inactive']);
        $deluxe101 = HotelRoom::create(['room_type_id' => $deluxe->room_type_id, 'room_number' => '101']);

        $this->assertTrue($room101->hotelRoomType->is($standard));
        $this->assertCount(2, $standard->hotelRooms);
        $this->assertSame('active', $room101->status);
        $this->assertSame('101', $deluxe101->room_number);

        $this->expectException(QueryException::class);
        HotelRoom::create(['room_type_id' => $standard->room_type_id, 'room_number' => '101']);
    }

    public function test_room_number_and_status_are_validated(): void
    {
        $context = $this->hotelContext();
        $roomType = HotelRoomType::create(['service_id' => $context['service']->service_id, 'capacity' => 2, 'amenities' => []]);

        try {
            HotelRoom::create(['room_type_id' => $roomType->room_type_id, 'room_number' => '   ']);
            $this->fail('An empty room number must be rejected.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $this->expectException(ValidationException::class);
        HotelRoom::create(['room_type_id' => $roomType->room_type_id, 'room_number' => '103', 'status' => 'unavailable']);
    }

    public function test_hotel_reservation_extends_a_central_booking_and_can_be_allocated_later(): void
    {
        $context = $this->hotelContext();
        $roomType = HotelRoomType::create(['service_id' => $context['service']->service_id, 'capacity' => 2, 'amenities' => ['Wi-Fi']]);
        $room = HotelRoom::create(['room_type_id' => $roomType->room_type_id, 'room_number' => '101']);

        $reservation = HotelRoomReservation::create([
            'booking_id' => $context['booking']->booking_id,
            'check_in_date' => '2026-09-01',
            'check_out_date' => '2026-09-03',
            'guest_count' => 2,
        ]);

        $this->assertNull($reservation->room_id);
        $this->assertNull($reservation->hotelRoom);
        $this->assertTrue($reservation->booking->is($context['booking']));
        $this->assertTrue($context['booking']->fresh()->hotelRoomReservation->is($reservation));

        $reservation->update(['room_id' => $room->room_id]);

        Payment::create([
            'booking_id' => $context['booking']->booking_id,
            'amount' => 2000,
            'status' => 'pending',
            'payment_method' => 'chapa',
        ]);
        Review::create([
            'booking_id' => $context['booking']->booking_id,
            'tourist_id' => $context['tourist']->tourist_id,
            'rating' => 5,
            'comment' => 'Great stay',
            'review_date' => today(),
        ]);

        $this->assertTrue($reservation->fresh()->hotelRoom->is($room));
        $this->assertTrue($room->hotelRoomReservations->first()->is($reservation));
        $this->assertTrue($context['booking']->fresh()->payment->booking->is($context['booking']));
        $this->assertTrue($context['booking']->fresh()->review->booking->is($context['booking']));
    }

    public function test_hotel_reservation_booking_is_unique_and_dates_and_guest_count_are_validated(): void
    {
        $context = $this->hotelContext();
        HotelRoomReservation::create([
            'booking_id' => $context['booking']->booking_id,
            'check_in_date' => '2026-09-01',
            'check_out_date' => '2026-09-03',
            'guest_count' => 1,
        ]);

        try {
            HotelRoomReservation::create([
                'booking_id' => $context['booking']->booking_id,
                'check_in_date' => '2026-10-01',
                'check_out_date' => '2026-10-03',
                'guest_count' => 1,
            ]);
            $this->fail('A booking must have only one hotel room reservation.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }

        $invalidBooking = Booking::create(['tourist_id' => $context['tourist']->tourist_id, 'service_id' => $context['service']->service_id]);

        try {
            HotelRoomReservation::create([
                'booking_id' => $invalidBooking->booking_id,
                'check_in_date' => '2026-10-03',
                'check_out_date' => '2026-10-03',
                'guest_count' => 1,
            ]);
            $this->fail('An invalid stay date range must be rejected.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        try {
            HotelRoomReservation::create([
                'booking_id' => $invalidBooking->booking_id,
                'check_in_date' => '2026-10-03',
                'check_out_date' => '2026-10-05',
                'guest_count' => 0,
            ]);
            $this->fail('Zero guest count must be rejected.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $this->expectException(ValidationException::class);
        HotelRoomReservation::create([
            'booking_id' => $invalidBooking->booking_id,
            'check_in_date' => '2026-10-03',
            'check_out_date' => '2026-10-05',
            'guest_count' => -1,
        ]);
    }

    private function hotelContext(): array
    {
        $tourist = Tourist::create([
            'user_id' => User::factory()->create(['role' => 'tourist'])->user_id,
            'full_name' => 'Hotel Test Tourist',
            'nationality' => 'Ethiopian',
        ]);
        $officer = TourismBureauOfficer::create([
            'user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id,
        ]);
        $provider = ServiceProvider::create([
            'user_id' => User::factory()->create(['role' => 'service_provider'])->user_id,
            'business_name' => 'Inventory Test Hotel',
            'provider_type' => 'hotel',
            'status' => 'approved',
        ]);
        $category = Category::create(['category_name' => 'Room']);
        $destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Gondar',
            'description' => 'Test destination',
        ]);
        $service = TourismService::create([
            'provider_id' => $provider->provider_id,
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Standard Room',
            'price' => 1000,
            'description' => 'Price per night',
        ]);
        $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $service->service_id]);

        return compact('tourist', 'provider', 'category', 'destination', 'service', 'booking');
    }

    private function hotelService(array $context, string $name): TourismService
    {
        return TourismService::create([
            'provider_id' => $context['provider']->provider_id,
            'category_id' => $context['category']->category_id,
            'destination_id' => $context['destination']->destination_id,
            'service_name' => $name,
            'price' => 1500,
            'description' => 'Price per night',
        ]);
    }
}
