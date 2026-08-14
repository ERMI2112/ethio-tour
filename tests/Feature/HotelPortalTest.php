<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\HotelRoom;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_visitor_is_redirected_from_hotel_dashboard(): void
    {
        $this->get(route('hotel.dashboard'))->assertRedirect(route('login'));
    }

    public function test_tourist_is_denied_from_hotel_management_routes(): void
    {
        /** @var \App\Models\User $tourist */
        $tourist = User::factory()->create(['role' => 'tourist']);

        $this->actingAs($tourist)->get(route('hotel.dashboard'))->assertForbidden();
        $this->actingAs($tourist)->get(route('hotel.services.index'))->assertForbidden();
        $this->actingAs($tourist)->get(route('hotel.rooms.index'))->assertForbidden();
        $this->actingAs($tourist)->get(route('hotel.reservations.index'))->assertForbidden();
    }

    public function test_hotel_dashboard_is_accessible_and_derives_safe_metrics_only_from_own_data(): void
    {
        $context = $this->hotelContext();
        $roomType = $this->makeService($context);
        $roomType->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $roomType->hotelRooms()->create(['room_number' => '102', 'status' => 'inactive']);

        $touristUser = $this->touristUser();
        $this->makeBooking($touristUser['tourist'], $context['service'], 'pending', null);
        $this->makeBooking($touristUser['tourist'], $context['service'], 'payment_pending', '101');

        $response = $this->actingAs($context['user'])->get(route('hotel.dashboard'));

        $response->assertOk()
            ->assertSee($context['provider']->business_name)
            ->assertSee('Room-type services')
            ->assertSee('Physical rooms')
            ->assertSee('Active rooms')
            ->assertSee('Awaiting Payment');

        // Scope: create data for another hotel and confirm it is not shown on this dashboard.
        $other = $this->hotelContext('other@example.com');
        $otherType = $this->makeService($other);
        $otherType->hotelRooms()->create(['room_number' => '501', 'status' => 'active']);
        $this->makeBooking($touristUser['tourist'], $other['service'], 'payment_pending', '501');

        $response = $this->actingAs($context['user'])->get(route('hotel.dashboard'));
        $response->assertOk()->assertDontSee('501');
    }

    public function test_hotel_profile_is_accessible_and_shows_read_only_and_editable_state(): void
    {
        $context = $this->hotelContext();

        $response = $this->actingAs($context['user'])->get(route('hotel.profile'));

        $response->assertOk()
            ->assertSee($context['provider']->business_name)
            ->assertSee('Verification status')
            ->assertSee('Account state')
            ->assertSee('editable');
    }

    public function test_room_type_management_is_visibly_linked_to_physical_inventory(): void
    {
        $context = $this->hotelContext();
        $this->makeService($context);

        $response = $this->actingAs($context['user'])->get(route('hotel.services.index'));

        $response->assertOk()
            ->assertSee('Standard Room')
            ->assertSee('1,000.00')
            ->assertSee('Capacity: 2 guest(s)');
    }

    public function test_physical_room_inventory_is_grouped_by_room_type(): void
    {
        $context = $this->hotelContext();
        $roomType = $this->makeService($context);
        $roomType->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $roomType->hotelRooms()->create(['room_number' => '102', 'status' => 'inactive']);

        $response = $this->actingAs($context['user'])->get(route('hotel.rooms.index'));

        $response->assertOk()
            ->assertSee('Room type')
            ->assertSee('Standard Room')
            ->assertSee('101')
            ->assertSee('102')
            ->assertSee('Active')
            ->assertSee('Inactive')
            ->assertSee('No history');
    }

    public function test_reservation_management_uses_status_badge_and_keeps_accept_reject(): void
    {
        $context = $this->hotelContext();
        $this->makeService($context);
        $touristUser = $this->touristUser();
        $booking = $this->makeBooking($touristUser['tourist'], $context['service'], 'pending', null);

        $response = $this->actingAs($context['user'])->get(route('hotel.reservations.index'));

        $response->assertOk()
            ->assertSee('#BK-'.sprintf('%05d', $booking->booking_id))
            ->assertSee('Pending')
            ->assertSee('Accept')
            ->assertSee('Reject')
            ->assertSee('Reservation status guide');
    }

    public function test_unrelated_hotel_provider_is_denied_resource_management(): void
    {
        $first = $this->hotelContext('first@example.com');
        $second = $this->hotelContext('second@example.com');
        $roomType = $this->makeService($second);
        $room = $roomType->hotelRooms()->create(['room_number' => '201', 'status' => 'active']);
        $touristUser = $this->touristUser();
        $booking = $this->makeBooking($touristUser['tourist'], $second['service'], 'pending', null);

        $this->actingAs($first['user'])->get(route('hotel.services.edit', $second['service']))->assertForbidden();
        $this->actingAs($first['user'])->get(route('hotel.rooms.edit', $room))->assertForbidden();
        $this->actingAs($first['user'])->get(route('hotel.reservations.show', $booking))->assertForbidden();
    }

    public function test_status_badge_component_maps_database_statuses_to_operator_labels(): void
    {
        $cases = [
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'payment_pending' => 'Awaiting Payment',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
            'completed' => 'Completed',
        ];

        foreach ($cases as $status => $label) {
            $html = view('components.ui.status-badge', ['status' => $status])->render();
            $this->assertStringContainsString($label, $html);
        }
    }

    public function test_tourist_sees_capacity_amenities_and_clarifying_awaiting_payment_state(): void
    {
        $context = $this->hotelContext();
        $roomType = $this->makeService($context);
        $roomType->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $touristUser = $this->touristUser();
        $booking = $this->makeBooking($touristUser['tourist'], $context['service'], 'payment_pending', '101');

        $response = $this->actingAs($touristUser['user'])->get(route('tourist.reservations.show', $booking));

        $response->assertOk()
            ->assertSee('Standard Room')
            ->assertSee('1,000.00')
            ->assertSee('Capacity: 2 guest(s)')
            ->assertSee('Wi-Fi')
            ->assertSee('Awaiting Payment')
            ->assertSee('not yet confirmed')
            ->assertSee('Room 101');
    }

    public function test_role_aware_navigation_contains_hotel_portal_links_only_for_hotel_providers(): void
    {
        $hotel = $this->hotelContext();
        $this->actingAs($hotel['user'])->get(route('home'))
            ->assertSee('Hotel Dashboard')
            ->assertSee('Room Types')
            ->assertSee('Reservations');

        /** @var User $restaurantUser */
        $restaurantUser = User::factory()->create(['role' => 'service_provider']);
        ServiceProvider::create(['user_id' => $restaurantUser->user_id, 'business_name' => 'Cafe', 'provider_type' => 'restaurant', 'status' => 'approved']);
        $this->actingAs($restaurantUser)->get(route('home'))
            ->assertDontSee('Hotel Dashboard')
            ->assertDontSee(route('hotel.dashboard'));
    }

    private function hotelContext(string $email = 'portal@example.com')
    {
        $user = User::factory()->create(['email' => $email, 'role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Portal Hotel', 'provider_type' => 'hotel', 'status' => 'approved']);
        $officer = TourismBureauOfficer::create(['user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id]);
        $category = Category::create(['category_name' => 'Room '.str_replace(['@', '.'], '-', $email)]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Bahir Dar', 'location' => 'Bahir Dar', 'description' => 'Test destination']);

        $service = $provider->tourismServices()->create([
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Standard Room',
            'price' => 1000,
            'description' => 'Comfortable room service',
        ]);

        return compact('user', 'provider', 'category', 'destination', 'service');
    }

    private function makeService(array $context)
    {
        return $context['service']->hotelRoomType()->create([
            'capacity' => 2,
            'amenities' => ['Wi-Fi', 'TV'],
        ]);
    }

    private function touristUser(): array
    {
        $user = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Jane Doe', 'nationality' => 'Ethiopian']);

        return compact('user', 'tourist');
    }

    private function makeBooking(Tourist $tourist, TourismService $service, string $status, ?string $roomNumber): Booking
    {
        $booking = Booking::create([
            'tourist_id' => $tourist->tourist_id,
            'service_id' => $service->service_id,
            'guide_id' => null,
            'status' => $status,
            'booking_date' => now(),
        ]);

        $room = $roomNumber
            ? HotelRoom::where('room_number', $roomNumber)->whereHas('hotelRoomType', fn ($q) => $q->where('service_id', $service->service_id))->first()
            : null;

        $booking->hotelRoomReservation()->create([
            'room_id' => $room?->room_id,
            'check_in_date' => date('Y-m-d', strtotime('+5 days')),
            'check_out_date' => date('Y-m-d', strtotime('+7 days')),
            'guest_count' => 2,
        ]);

        return $booking;
    }
}
