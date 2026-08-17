<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\RestaurantReservation;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use App\Services\RestaurantAvailabilityService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RestaurantVerticalTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_provider_can_manage_own_portal_and_tourist_cannot(): void
    {
        $context = $this->restaurantContext();
        $this->actingAs($context['user'])->get(route('restaurant.dashboard'))->assertOk()->assertSee('Restaurant Portal');
        $this->actingAs($context['touristUser'])->get(route('restaurant.dashboard'))->assertForbidden();

        $hotelUser = User::factory()->create(['role' => 'service_provider']);
        ServiceProvider::create(['user_id' => $hotelUser->user_id, 'business_name' => 'Hotel', 'provider_type' => 'hotel', 'status' => 'approved']);
        $this->actingAs($hotelUser)->get(route('restaurant.dashboard'))->assertForbidden();
    }

    public function test_restaurant_can_create_services_and_tables_with_ownership_protection(): void
    {
        $context = $this->restaurantContext();
        $other = $this->restaurantContext('other-restaurant@example.com');

        $response = $this->actingAs($context['user'])->post(route('restaurant.services.store'), [
            'service_name' => 'Dining Reservation', 'price' => 125, 'description' => 'Table reservation offering',
            'category_id' => $context['category']->category_id, 'destination_id' => $context['destination']->destination_id,
        ]);
        $response->assertRedirect(route('restaurant.services.index'));
        $service = TourismService::where('provider_id', $context['provider']->provider_id)->firstOrFail();

        $this->actingAs($context['user'])->post(route('restaurant.tables.store'), ['table_number' => 'T1', 'capacity' => 4, 'status' => 'active'])->assertRedirect();
        $table = $context['provider']->restaurantTables()->firstOrFail();

        $this->actingAs($other['user'])->get(route('restaurant.services.edit', $service))->assertForbidden();
        $this->actingAs($other['user'])->get(route('restaurant.tables.edit', $table))->assertForbidden();
    }

    public function test_table_number_is_unique_within_restaurant_and_capacity_is_positive(): void
    {
        $context = $this->restaurantContext();
        $context['provider']->restaurantTables()->create(['table_number' => 'T1', 'capacity' => 2, 'status' => 'active']);

        $this->actingAs($context['user'])->post(route('restaurant.tables.store'), ['table_number' => 'T1', 'capacity' => 4, 'status' => 'active'])->assertSessionHasErrors('table_number');
        $this->actingAs($context['user'])->post(route('restaurant.tables.store'), ['table_number' => 'T2', 'capacity' => 0, 'status' => 'active'])->assertSessionHasErrors('capacity');
    }

    public function test_availability_uses_capacity_and_half_open_time_ranges(): void
    {
        $context = $this->restaurantContext();
        $table = $context['provider']->restaurantTables()->create(['table_number' => 'T1', 'capacity' => 4, 'status' => 'active']);
        $booking = $this->booking($context, 'confirmed');
        $booking->restaurantReservation()->create(['table_id' => $table->table_id, 'reservation_date' => '2026-09-01', 'start_time' => '18:00', 'end_time' => '19:00', 'guest_count' => 2]);

        $service = app(RestaurantAvailabilityService::class);
        $this->assertCount(0, $service->findAvailableTables($context['service'], '2026-09-01', '18:30', '19:30', 2));
        $this->assertCount(1, $service->findAvailableTables($context['service'], '2026-09-01', '19:00', '20:00', 2));
        $this->expectException(ValidationException::class);
        $service->findAvailableTables($context['service'], '2026-09-01', '19:00', '19:00', 2);
    }

    public function test_pending_and_cancelled_reservations_do_not_block_but_accepted_does(): void
    {
        $context = $this->restaurantContext();
        $table = $context['provider']->restaurantTables()->create(['table_number' => 'T1', 'capacity' => 4, 'status' => 'active']);
        $service = app(RestaurantAvailabilityService::class);

        foreach (['pending', 'cancelled', 'rejected', 'completed'] as $status) {
            $booking = $this->booking($context, $status);
            $booking->restaurantReservation()->create(['table_id' => $table->table_id, 'reservation_date' => '2026-09-02', 'start_time' => '18:00', 'end_time' => '19:00', 'guest_count' => 2]);
            $this->assertCount(1, $service->findAvailableTables($context['service'], '2026-09-02', '18:00', '19:00', 2));
        }

        $booking = $this->booking($context, 'accepted');
        $booking->restaurantReservation()->create(['table_id' => $table->table_id, 'reservation_date' => '2026-09-02', 'start_time' => '18:00', 'end_time' => '19:00', 'guest_count' => 2]);
        $this->assertCount(0, $service->findAvailableTables($context['service'], '2026-09-02', '18:00', '19:00', 2));
    }

    public function test_tourist_request_creates_central_booking_and_provider_accepts_with_table_allocation(): void
    {
        $context = $this->restaurantContext();
        $context['provider']->restaurantTables()->create(['table_number' => 'T1', 'capacity' => 4, 'status' => 'active']);

        $this->actingAs($context['touristUser'])->post(route('tourist.restaurant-reservations.store', $context['service']), [
            'reservation_date' => '2026-09-05', 'start_time' => '18:00', 'end_time' => '19:00', 'guest_count' => 2,
        ])->assertRedirect();

        $booking = Booking::latest('booking_id')->firstOrFail();
        $this->assertSame('pending', $booking->status);
        $this->assertNull($booking->guide_id);
        $this->assertSame($context['service']->service_id, $booking->service_id);
        $this->assertNotNull($booking->restaurantReservation);

        $this->actingAs($context['user'])->patch(route('restaurant.reservations.accept', $booking))->assertRedirect();
        $booking->refresh();
        $this->assertSame('accepted', $booking->status);
        $this->assertNotNull($booking->restaurantReservation->table_id);
    }

    public function test_provider_can_reject_only_own_pending_request_and_tourist_idor_is_blocked(): void
    {
        $context = $this->restaurantContext();
        $otherTourist = $this->touristContext('other-tourist@example.com');
        $booking = $this->booking($context, 'pending', $otherTourist['tourist']);
        $booking->restaurantReservation()->create(['reservation_date' => '2026-09-06', 'start_time' => '18:00', 'end_time' => '19:00', 'guest_count' => 2]);

        $this->actingAs($context['touristUser'])->get(route('tourist.reservations.show', $booking))->assertForbidden();
        $this->actingAs($context['user'])->patch(route('restaurant.reservations.reject', $booking))->assertRedirect();
        $this->assertSame('rejected', $booking->fresh()->status);
    }

    public function test_restaurant_reservation_booking_id_is_unique_and_payment_review_relationships_remain_available(): void
    {
        $context = $this->restaurantContext();
        $booking = $this->booking($context, 'pending');
        $booking->restaurantReservation()->create(['reservation_date' => '2026-09-07', 'start_time' => '18:00', 'end_time' => '19:00', 'guest_count' => 2]);
        $booking->payment()->create(['amount' => 0, 'status' => 'pending', 'payment_method' => 'future']);
        $booking->review()->create(['tourist_id' => $context['tourist']->tourist_id, 'rating' => 5, 'comment' => 'Great', 'review_date' => '2026-09-08']);

        $this->assertNotNull($booking->fresh()->payment);
        $this->assertNotNull($booking->fresh()->review);

        $this->expectException(QueryException::class);
        RestaurantReservation::create(['booking_id' => $booking->booking_id, 'reservation_date' => '2026-09-07', 'start_time' => '19:00', 'end_time' => '20:00', 'guest_count' => 2]);
    }

    private function restaurantContext(string $email = 'restaurant@example.com'): array
    {
        $user = User::factory()->create(['email' => $email, 'role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Test Restaurant', 'provider_type' => 'restaurant', 'status' => 'approved']);
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar', 'location' => 'Gondar', 'description' => 'City']);
        $category = Category::create(['category_name' => 'Restaurant Reservation '.$email]);
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Dining Reservation', 'price' => 125, 'description' => 'Reservation offering']);
        $tourist = $this->touristContext('tourist-'.$email);

        return compact('user', 'provider', 'destination', 'category', 'service') + ['touristUser' => $tourist['user'], 'tourist' => $tourist['tourist']];
    }

    private function touristContext(string $email): array
    {
        $user = User::factory()->create(['email' => $email, 'role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Test Tourist', 'nationality' => 'Ethiopian']);

        return compact('user', 'tourist');
    }

    private function booking(array $context, string $status, ?Tourist $tourist = null): Booking
    {
        return Booking::create(['tourist_id' => ($tourist ?? $context['tourist'])->tourist_id, 'service_id' => $context['service']->service_id, 'status' => $status, 'booking_date' => now()]);
    }
}
