<?php

namespace Tests\Feature;

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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelReservationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_create_booking(): void
    {
        $context = $this->setupHotelContext();

        $response = $this->post(route('tourist.reservations.store', $context['service']), [
            'check_in_date' => date('Y-m-d', strtotime('+1 day')),
            'check_out_date' => date('Y-m-d', strtotime('+3 days')),
            'guest_count' => 2,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_non_tourist_role_cannot_create_booking(): void
    {
        $context = $this->setupHotelContext();

        $response = $this->actingAs($context['providerUser'])->post(route('tourist.reservations.store', $context['service']), [
            'check_in_date' => date('Y-m-d', strtotime('+1 day')),
            'check_out_date' => date('Y-m-d', strtotime('+3 days')),
            'guest_count' => 2,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_invalid_date_range_rejected(): void
    {
        $context = $this->setupHotelContext();
        $tourist = $this->createTouristUser();

        $response = $this->actingAs($tourist['user'])->post(route('tourist.reservations.store', $context['service']), [
            'check_in_date' => date('Y-m-d', strtotime('+3 days')),
            'check_out_date' => date('Y-m-d', strtotime('+1 day')),
            'guest_count' => 2,
        ]);

        $response->assertSessionHasErrors('check_out_date');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_over_capacity_request_rejected(): void
    {
        $context = $this->setupHotelContext(capacity: 2);
        $tourist = $this->createTouristUser();

        $response = $this->actingAs($tourist['user'])->post(route('tourist.reservations.store', $context['service']), [
            'check_in_date' => date('Y-m-d', strtotime('+1 day')),
            'check_out_date' => date('Y-m-d', strtotime('+3 days')),
            'guest_count' => 5,
        ]);

        $response->assertSessionHasErrors('guest_count');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_tourist_can_create_valid_hotel_booking(): void
    {
        $context = $this->setupHotelContext();
        $tourist = $this->createTouristUser();

        $checkIn = date('Y-m-d', strtotime('+1 day'));
        $checkOut = date('Y-m-d', strtotime('+3 days'));

        $response = $this->actingAs($tourist['user'])->post(route('tourist.reservations.store', $context['service']), [
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'guest_count' => 2,
        ]);

        $booking = Booking::first();

        $this->assertNotNull($booking);
        $response->assertRedirect(route('tourist.reservations.show', $booking));
        $this->assertEquals((int) $tourist['tourist']->tourist_id, (int) $booking->tourist_id);
        $this->assertEquals((int) $context['service']->service_id, (int) $booking->service_id);
        $this->assertNull($booking->guide_id);
        $this->assertEquals('pending', $booking->status);

        $reservation = HotelRoomReservation::first();
        $this->assertNotNull($reservation);
        $this->assertEquals((int) $booking->booking_id, (int) $reservation->booking_id);
        $this->assertNull($reservation->room_id);
        $this->assertEquals($checkIn, $reservation->check_in_date->format('Y-m-d'));
        $this->assertEquals($checkOut, $reservation->check_out_date->format('Y-m-d'));
        $this->assertEquals(2, $reservation->guest_count);
    }

    public function test_tourist_can_see_own_booking(): void
    {
        $context = $this->setupHotelContext();
        $tourist = $this->createTouristUser();
        $booking = $this->createBooking($tourist['tourist'], $context['service']);

        $response = $this->actingAs($tourist['user'])->get(route('tourist.reservations.show', $booking));

        $response->assertOk();
        $response->assertSee('#BK-'.sprintf('%05d', $booking->booking_id));
        $response->assertSee($context['service']->service_name);
    }

    public function test_tourist_cannot_see_another_tourists_booking_idor(): void
    {
        $context = $this->setupHotelContext();
        $touristA = $this->createTouristUser();
        $touristB = $this->createTouristUser();
        $bookingA = $this->createBooking($touristA['tourist'], $context['service']);

        $response = $this->actingAs($touristB['user'])->get(route('tourist.reservations.show', $bookingA));

        $response->assertForbidden();
    }

    public function test_tourist_can_cancel_own_pending_booking(): void
    {
        $context = $this->setupHotelContext();
        $tourist = $this->createTouristUser();
        $booking = $this->createBooking($tourist['tourist'], $context['service']);

        $response = $this->actingAs($tourist['user'])->patch(route('tourist.reservations.cancel', $booking));

        $response->assertRedirect();
        $this->assertEquals('cancelled', $booking->fresh()->status);
    }

    public function test_hotel_provider_sees_only_own_reservations(): void
    {
        $hotelA = $this->setupHotelContext('Hotel A');
        $hotelB = $this->setupHotelContext('Hotel B');
        $tourist = $this->createTouristUser();

        $bookingA = $this->createBooking($tourist['tourist'], $hotelA['service']);
        $bookingB = $this->createBooking($tourist['tourist'], $hotelB['service']);

        $response = $this->actingAs($hotelA['providerUser'])->get(route('hotel.reservations.index'));

        $response->assertOk();
        $response->assertSee('#BK-'.sprintf('%05d', $bookingA->booking_id));
        $response->assertDontSee('#BK-'.sprintf('%05d', $bookingB->booking_id));
    }

    public function test_hotel_provider_cannot_access_another_hotels_reservation_idor(): void
    {
        $hotelA = $this->setupHotelContext('Hotel A');
        $hotelB = $this->setupHotelContext('Hotel B');
        $tourist = $this->createTouristUser();

        $bookingB = $this->createBooking($tourist['tourist'], $hotelB['service']);

        $responseView = $this->actingAs($hotelA['providerUser'])->get(route('hotel.reservations.show', $bookingB));
        $responseView->assertForbidden();

        $responseAccept = $this->actingAs($hotelA['providerUser'])->patch(route('hotel.reservations.accept', $bookingB));
        $responseAccept->assertForbidden();

        $responseReject = $this->actingAs($hotelA['providerUser'])->patch(route('hotel.reservations.reject', $bookingB));
        $responseReject->assertForbidden();
    }

    public function test_hotel_accepts_valid_reservation(): void
    {
        $context = $this->setupHotelContext();
        $tourist = $this->createTouristUser();
        $booking = $this->createBooking($tourist['tourist'], $context['service']);

        $response = $this->actingAs($context['providerUser'])->patch(route('hotel.reservations.accept', $booking));

        $response->assertRedirect();
        $booking->refresh();

        $this->assertEquals('payment_pending', $booking->status);
        $this->assertNotNull($booking->hotelRoomReservation->room_id);
        $this->assertEquals((int) $context['room']->room_id, (int) $booking->hotelRoomReservation->room_id);
    }

    public function test_acceptance_rechecks_availability_and_fails_gracefully_if_no_room_available(): void
    {
        $context = $this->setupHotelContext();
        $context['room']->update(['status' => 'inactive']); // deactivate physical room before acceptance

        $tourist = $this->createTouristUser();
        $booking = $this->createBooking($tourist['tourist'], $context['service']);

        $response = $this->actingAs($context['providerUser'])->patch(route('hotel.reservations.accept', $booking));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $booking->refresh();

        $this->assertEquals('pending', $booking->status);
        $this->assertNull($booking->hotelRoomReservation->room_id);
    }

    public function test_hotel_rejects_valid_reservation(): void
    {
        $context = $this->setupHotelContext();
        $tourist = $this->createTouristUser();
        $booking = $this->createBooking($tourist['tourist'], $context['service']);

        $response = $this->actingAs($context['providerUser'])->patch(route('hotel.reservations.reject', $booking));

        $response->assertRedirect();
        $booking->refresh();

        $this->assertEquals('rejected', $booking->status);
        $this->assertNull($booking->hotelRoomReservation->room_id);
    }

    private function setupHotelContext(string $businessName = 'Gondar Plaza Hotel', int $capacity = 2): array
    {
        $officerUser = User::create(['email' => 'bureau_'.uniqid().'@test.com', 'password' => 'secret', 'role' => 'tourism_bureau_officer', 'is_active' => true]);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);

        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar Castle', 'location' => 'Gondar', 'description' => 'Historical site']);
        $category = Category::firstOrCreate(['category_name' => 'Hotels & Lodging']);

        $providerUser = User::create(['email' => 'hotel_'.uniqid().'@test.com', 'password' => 'secret', 'role' => 'service_provider', 'is_active' => true]);
        $provider = ServiceProvider::create(['user_id' => $providerUser->user_id, 'business_name' => $businessName, 'provider_type' => 'hotel', 'status' => 'approved']);

        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => $businessName.' Deluxe Room', 'price' => 1500.00, 'description' => 'Spacious room']);

        $roomType = $service->hotelRoomType()->create(['capacity' => $capacity, 'amenities' => ['wifi', 'tv']]);
        $room = $roomType->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);

        return compact('officerUser', 'officer', 'destination', 'category', 'providerUser', 'provider', 'service', 'roomType', 'room');
    }

    private function createTouristUser(): array
    {
        $user = User::create(['email' => 'tourist_'.uniqid().'@test.com', 'password' => 'secret', 'role' => 'tourist', 'is_active' => true]);
        $tourist = Tourist::create(['user_id' => $user->user_id, 'full_name' => 'John Doe', 'nationality' => 'Ethiopian']);

        return compact('user', 'tourist');
    }

    private function createBooking(Tourist $tourist, TourismService $service, string $status = 'pending'): Booking
    {
        $booking = Booking::create([
            'tourist_id' => $tourist->tourist_id,
            'service_id' => $service->service_id,
            'guide_id' => null,
            'status' => $status,
            'booking_date' => now(),
        ]);

        $booking->hotelRoomReservation()->create([
            'room_id' => null,
            'check_in_date' => date('Y-m-d', strtotime('+1 day')),
            'check_out_date' => date('Y-m-d', strtotime('+3 days')),
            'guest_count' => 2,
        ]);

        return $booking;
    }
}
