<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\TransportationVehicle;
use App\Models\User;
use App\Services\TransportationAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransportationVerticalTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_transportation_discovery_and_provider_access_are_role_scoped(): void
    {
        $context = $this->context();
        $this->get(route('transportation.index'))->assertOk()->assertSee($context['service']->service_name);
        $this->get(route('transportation.show', $context['service']))->assertOk()->assertSee($context['vehicle']->vehicle_identifier);
        $this->actingAs($context['user'])->get(route('transportation.dashboard'))->assertOk();

        $tourist = $this->touristContext();
        $this->actingAs($tourist['user'])->get(route('transportation.dashboard'))->assertForbidden();
        $restaurant = User::factory()->create(['role' => 'service_provider']);
        ServiceProvider::create(['user_id' => $restaurant->user_id, 'business_name' => 'Restaurant', 'provider_type' => 'restaurant', 'status' => 'approved']);
        $this->actingAs($restaurant)->get(route('transportation.dashboard'))->assertForbidden();
    }

    public function test_provider_can_manage_owned_services_and_vehicles_but_not_another_provider_inventory(): void
    {
        $context = $this->context();
        $other = $this->context('other-transport@example.test');

        $this->actingAs($context['user'])->post(route('transportation.services.store'), [
            'service_name' => 'Airport Transfer', 'price' => 400, 'description' => 'Reliable transfer service',
            'category_id' => $context['category']->category_id, 'destination_id' => $context['destination']->destination_id,
        ])->assertRedirect(route('transportation.services.index'));
        $service = $context['provider']->tourismServices()->latest('service_id')->firstOrFail();
        $this->actingAs($context['user'])->post(route('transportation.vehicles.store'), [
            'service_id' => $service->service_id, 'vehicle_identifier' => 'TR-002', 'vehicle_type' => 'Minibus', 'capacity' => 12, 'status' => 'active',
        ])->assertRedirect(route('transportation.vehicles.index'));
        $vehicle = $context['provider']->transportationVehicles()->where('vehicle_identifier', 'TR-002')->firstOrFail();

        $this->actingAs($other['user'])->get(route('transportation.vehicles.edit', $vehicle))->assertForbidden();
        $this->actingAs($other['user'])->delete(route('transportation.vehicles.destroy', $vehicle))->assertForbidden();
        $this->assertDatabaseHas('transportation_vehicles', ['vehicle_id' => $vehicle->vehicle_id]);
    }

    public function test_vehicle_identifier_and_status_validation_are_enforced(): void
    {
        $context = $this->context();
        $this->actingAs($context['user'])->post(route('transportation.vehicles.store'), [
            'service_id' => $context['service']->service_id, 'vehicle_identifier' => $context['vehicle']->vehicle_identifier,
            'vehicle_type' => 'SUV', 'capacity' => 0, 'status' => 'broken',
        ])->assertSessionHasErrors(['vehicle_identifier', 'capacity', 'status']);
    }

    public function test_availability_uses_active_inventory_and_half_open_datetime_ranges(): void
    {
        $context = $this->context();
        $service = app(TransportationAvailabilityService::class);
        $this->assertCount(1, $service->findAvailableVehicles($context['service'], '2026-10-01 08:00', '2026-10-01 12:00', 3));
        $booking = $this->booking($context, 'confirmed');
        $booking->transportationReservation()->create(['vehicle_id' => $context['vehicle']->vehicle_id, 'pickup_location' => 'A', 'dropoff_location' => 'B', 'pickup_at' => '2026-10-01 08:00', 'dropoff_at' => '2026-10-01 12:00', 'passenger_count' => 2]);
        $this->assertCount(1, $service->findAvailableVehicles($context['service'], '2026-10-01 12:00', '2026-10-01 14:00', 3));
        $this->assertCount(0, $service->findAvailableVehicles($context['service'], '2026-10-01 11:00', '2026-10-01 14:00', 3));
        $this->expectException(ValidationException::class);
        $service->findAvailableVehicles($context['service'], '2026-10-02 12:00', '2026-10-02 12:00', 2);
    }

    public function test_tourist_request_creates_central_booking_and_provider_acceptance_allocates_vehicle(): void
    {
        $context = $this->context();
        $tourist = $this->touristContext();
        $this->actingAs($tourist['user'])->post(route('tourist.transportation-reservations.store', $context['service']), [
            'pickup_location' => 'Gondar Airport', 'dropoff_location' => 'Fasil Ghebbi', 'pickup_at' => '2026-10-04 08:00', 'dropoff_at' => '2026-10-04 12:00', 'passenger_count' => 2,
        ])->assertRedirect();
        $booking = Booking::latest('booking_id')->firstOrFail();
        $this->assertSame('pending', $booking->status);
        $this->assertNotNull($booking->transportationReservation);
        $this->assertSame($context['service']->service_id, $booking->service_id);

        $this->actingAs($context['user'])->patch(route('transportation.reservations.accept', $booking))->assertRedirect();
        $booking->refresh()->load('transportationReservation');
        $this->assertSame('accepted', $booking->status);
        $this->assertSame($context['vehicle']->vehicle_id, $booking->transportationReservation->vehicle_id);
        $this->assertTrue(method_exists($booking, 'payment'));
    }

    public function test_rejection_preserves_history_and_does_not_allocate_vehicle(): void
    {
        $context = $this->context();
        $booking = $this->booking($context, 'pending');
        $booking->transportationReservation()->create(['pickup_location' => 'A', 'dropoff_location' => 'B', 'pickup_at' => '2026-10-05 08:00', 'dropoff_at' => '2026-10-05 12:00', 'passenger_count' => 2]);

        $this->actingAs($context['user'])->patch(route('transportation.reservations.reject', $booking))->assertRedirect();
        $booking->refresh()->load('transportationReservation');
        $this->assertSame('rejected', $booking->status);
        $this->assertNull($booking->transportationReservation->vehicle_id);
        $this->assertDatabaseHas('transportation_reservations', ['booking_id' => $booking->booking_id]);
    }

    public function test_payment_and_review_relationships_remain_centralized(): void
    {
        $context = $this->context();
        $booking = $this->booking($context, 'confirmed');
        $booking->transportationReservation()->create(['pickup_location' => 'A', 'dropoff_location' => 'B', 'pickup_at' => '2026-10-06 08:00', 'dropoff_at' => '2026-10-06 12:00', 'passenger_count' => 2]);
        $booking->payment()->create(['amount' => 0, 'status' => 'pending', 'payment_method' => 'deferred']);
        $this->assertNotNull($booking->fresh()->payment);
        $this->assertTrue(method_exists($booking->fresh(), 'review'));
    }

    private function context(string $email = 'transport@example.test'): array
    {
        $user = User::factory()->create(['email' => $email, 'role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Gondar Transport', 'provider_type' => 'transportation_car_rental', 'status' => 'approved']);
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);
        $category = Category::create(['category_name' => 'Vehicle '.uniqid()]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar', 'location' => 'Gondar', 'description' => 'Historic city']);
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Airport Transfer', 'price' => 500, 'description' => 'Transfer vehicle service']);
        $vehicle = TransportationVehicle::create(['provider_id' => $provider->provider_id, 'service_id' => $service->service_id, 'vehicle_identifier' => 'TR-001', 'vehicle_type' => 'SUV', 'capacity' => 4, 'status' => 'active']);

        return compact('user', 'provider', 'category', 'destination', 'service', 'vehicle');
    }

    private function touristContext(): array
    {
        $user = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Test Tourist', 'nationality' => 'Ethiopian']);

        return compact('user', 'tourist');
    }

    private function booking(array $context, string $status, ?Tourist $tourist = null): Booking
    {
        $tourist ??= $this->touristContext()['tourist'];

        return Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $context['service']->service_id, 'status' => $status, 'booking_date' => now()]);
    }
}
