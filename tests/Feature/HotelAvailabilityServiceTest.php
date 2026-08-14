<?php

namespace Tests\Feature;

use App\Exceptions\HotelAvailabilityException;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\HotelRoom;
use App\Models\HotelRoomReservation;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use App\Services\HotelAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HotelAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_out_must_be_after_check_in(): void
    {
        $context = $this->hotelContext();

        $this->expectException(ValidationException::class);
        app(HotelAvailabilityService::class)->findAvailableRooms($context['service'], '2026-08-20', '2026-08-20', 1);
    }

    public function test_same_day_checkout_and_checkin_do_not_overlap(): void
    {
        $context = $this->hotelContext();
        $room = $context['roomType']->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $this->reservation($context, 'confirmed', $room, '2026-08-20', '2026-08-23');

        $available = app(HotelAvailabilityService::class)->findAvailableRooms($context['service'], '2026-08-23', '2026-08-25', 1);

        $this->assertTrue($available->contains($room));
    }

    public function test_overlapping_intervals_exclude_a_reserved_room(): void
    {
        $context = $this->hotelContext();
        $room = $context['roomType']->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $this->reservation($context, 'confirmed', $room, '2026-08-20', '2026-08-23');

        $available = app(HotelAvailabilityService::class)->findAvailableRooms($context['service'], '2026-08-22', '2026-08-25', 1);

        $this->assertFalse($available->contains($room));
    }

    public function test_non_overlapping_intervals_are_available(): void
    {
        $context = $this->hotelContext();
        $room = $context['roomType']->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $this->reservation($context, 'confirmed', $room, '2026-08-20', '2026-08-23');

        $available = app(HotelAvailabilityService::class)->findAvailableRooms($context['service'], '2026-08-25', '2026-08-27', 1);

        $this->assertTrue($available->contains($room));
    }

    public function test_guest_count_equal_to_capacity_succeeds(): void
    {
        $context = $this->hotelContext(capacity: 2);
        $room = $context['roomType']->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);

        $this->assertTrue(app(HotelAvailabilityService::class)->isRoomAvailable($room, '2026-08-20', '2026-08-22', 2));
    }

    public function test_guest_count_above_capacity_fails(): void
    {
        $context = $this->hotelContext(capacity: 2);

        $this->expectException(ValidationException::class);
        app(HotelAvailabilityService::class)->findAvailableRooms($context['service'], '2026-08-20', '2026-08-22', 3);
    }

    public function test_zero_or_negative_guest_count_fails(): void
    {
        $context = $this->hotelContext(capacity: 2);
        $service = app(HotelAvailabilityService::class);

        try {
            $service->findAvailableRooms($context['service'], '2026-08-20', '2026-08-22', 0);
            $this->fail('Zero guest count should fail.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $this->expectException(ValidationException::class);
        $service->findAvailableRooms($context['service'], '2026-08-20', '2026-08-22', -1);
    }

    public function test_active_unreserved_room_is_returned_and_inactive_room_is_not(): void
    {
        $context = $this->hotelContext();
        $active = $context['roomType']->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $inactive = $context['roomType']->hotelRooms()->create(['room_number' => '102', 'status' => 'inactive']);

        $available = app(HotelAvailabilityService::class)->findAvailableRooms($context['service'], '2026-08-20', '2026-08-22', 1);

        $this->assertTrue($available->contains($active));
        $this->assertFalse($available->contains($inactive));
    }

    #[DataProvider('nonBlockingStatuses')]
    public function test_non_reserving_statuses_do_not_block_availability(string $status): void
    {
        $context = $this->hotelContext();
        $room = $context['roomType']->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $this->reservation($context, $status, $room, '2026-08-20', '2026-08-23');

        $available = app(HotelAvailabilityService::class)->findAvailableRooms($context['service'], '2026-08-20', '2026-08-23', 1);

        $this->assertTrue($available->contains($room));
    }

    public static function nonBlockingStatuses(): array
    {
        return [['pending'], ['rejected'], ['cancelled'], ['completed']];
    }

    #[DataProvider('blockingStatuses')]
    public function test_inventory_reserving_statuses_block_availability(string $status): void
    {
        $context = $this->hotelContext();
        $room = $context['roomType']->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $this->reservation($context, $status, $room, '2026-08-20', '2026-08-23');

        $available = app(HotelAvailabilityService::class)->findAvailableRooms($context['service'], '2026-08-20', '2026-08-23', 1);

        $this->assertFalse($available->contains($room));
    }

    public static function blockingStatuses(): array
    {
        return [['accepted'], ['payment_pending'], ['confirmed']];
    }

    public function test_allocation_requires_the_booked_room_type_and_hotel(): void
    {
        $context = $this->hotelContext();
        $otherService = $this->createService($context, 'Deluxe Room');
        $otherService->hotelRoomType()->create(['capacity' => 2, 'amenities' => []]);
        $room = $context['roomType']->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $booking = $this->booking($context, 'confirmed', $otherService);
        $reservation = HotelRoomReservation::create(['booking_id' => $booking->booking_id, 'room_id' => $room->room_id, 'check_in_date' => '2026-08-20', 'check_out_date' => '2026-08-23', 'guest_count' => 1]);

        $this->expectException(HotelAvailabilityException::class);
        app(HotelAvailabilityService::class)->allocateAvailableRoom($reservation);
    }

    public function test_room_from_another_hotel_cannot_be_allocated(): void
    {
        $context = $this->hotelContext();
        $other = $this->hotelContext(email: 'other-hotel@example.com');
        $foreignRoom = $other['roomType']->hotelRooms()->create(['room_number' => '201', 'status' => 'active']);
        $booking = $this->booking($context, 'confirmed', $context['service']);
        $reservation = HotelRoomReservation::create(['booking_id' => $booking->booking_id, 'room_id' => $foreignRoom->room_id, 'check_in_date' => '2026-08-20', 'check_out_date' => '2026-08-23', 'guest_count' => 1]);

        $this->expectException(HotelAvailabilityException::class);
        app(HotelAvailabilityService::class)->allocateAvailableRoom($reservation);
    }

    public function test_available_room_is_allocated_and_repeated_allocation_is_idempotent(): void
    {
        $context = $this->hotelContext();
        $room = $context['roomType']->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $booking = $this->booking($context, 'accepted', $context['service']);
        $reservation = HotelRoomReservation::create(['booking_id' => $booking->booking_id, 'check_in_date' => '2026-08-20', 'check_out_date' => '2026-08-23', 'guest_count' => 1]);
        $availability = app(HotelAvailabilityService::class);

        $allocated = $availability->allocateAvailableRoom($reservation);
        $allocatedAgain = $availability->allocateAvailableRoom($reservation->fresh());

        $this->assertTrue($allocated->is($room));
        $this->assertTrue($allocatedAgain->is($room));
        $this->assertSame($room->room_id, $reservation->fresh()->room_id);
    }

    public function test_pending_booking_is_not_allocated_and_no_room_returns_controlled_failure(): void
    {
        $context = $this->hotelContext();
        $booking = $this->booking($context, 'pending', $context['service']);
        $reservation = HotelRoomReservation::create(['booking_id' => $booking->booking_id, 'check_in_date' => '2026-08-20', 'check_out_date' => '2026-08-23', 'guest_count' => 1]);

        try {
            app(HotelAvailabilityService::class)->allocateAvailableRoom($reservation);
            $this->fail('Pending bookings must not allocate inventory.');
        } catch (HotelAvailabilityException) {
            $this->assertNull($reservation->fresh()->room_id);
        }

        $booking->update(['status' => 'confirmed']);
        $this->expectException(HotelAvailabilityException::class);
        app(HotelAvailabilityService::class)->allocateAvailableRoom($reservation->fresh());
    }

    public function test_allocation_failure_leaves_reservation_unassigned(): void
    {
        $context = $this->hotelContext();
        $booking = $this->booking($context, 'confirmed', $context['service']);
        $reservation = HotelRoomReservation::create(['booking_id' => $booking->booking_id, 'check_in_date' => '2026-08-20', 'check_out_date' => '2026-08-23', 'guest_count' => 1]);

        try {
            app(HotelAvailabilityService::class)->allocateAvailableRoom($reservation);
            $this->fail('Allocation with no rooms must fail.');
        } catch (HotelAvailabilityException) {
            $this->assertNull($reservation->fresh()->room_id);
        }
    }

    private function hotelContext(int $capacity = 2, string $email = 'hotel@example.com'): array
    {
        $user = User::factory()->create(['email' => $email, 'role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Test Hotel', 'provider_type' => 'hotel', 'status' => 'approved']);
        $officer = TourismBureauOfficer::create(['user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id]);
        $category = Category::create(['category_name' => 'Room '.str_replace(['@', '.'], '-', $email)]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar '.$email, 'location' => 'Gondar', 'description' => 'Test destination']);
        $service = $this->createService(compact('provider', 'category', 'destination'), 'Standard Room');
        $roomType = $service->hotelRoomType()->create(['capacity' => $capacity, 'amenities' => []]);

        return compact('user', 'provider', 'category', 'destination', 'service', 'roomType');
    }

    private function createService(array $context, string $name): TourismService
    {
        return TourismService::create(['provider_id' => $context['provider']->provider_id, 'category_id' => $context['category']->category_id, 'destination_id' => $context['destination']->destination_id, 'service_name' => $name, 'price' => 1000, 'description' => 'Room service']);
    }

    private function booking(array $context, string $status, TourismService $service): Booking
    {
        $tourist = Tourist::create(['user_id' => User::factory()->create(['role' => 'tourist'])->user_id, 'full_name' => 'Availability Tourist', 'nationality' => 'Ethiopian']);

        return Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $service->service_id, 'status' => $status]);
    }

    private function reservation(array $context, string $status, HotelRoom $room, string $checkIn, string $checkOut): HotelRoomReservation
    {
        $booking = $this->booking($context, $status, $context['service']);

        return HotelRoomReservation::create(['booking_id' => $booking->booking_id, 'room_id' => $room->room_id, 'check_in_date' => $checkIn, 'check_out_date' => $checkOut, 'guest_count' => 1]);
    }
}
