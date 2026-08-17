<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\RestaurantTable;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\TransportationVehicle;
use App\Models\User;
use App\Services\BookingAmountService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RestaurantTransportationPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_booking_freezes_the_server_derived_offering_price_and_becomes_payable_after_acceptance(): void
    {
        $context = $this->restaurantContext();
        RestaurantTable::create(['provider_id' => $context['provider']->provider_id, 'table_number' => 'P-1', 'capacity' => 4, 'status' => 'active']);

        $this->actingAs($context['touristUser'])->post(route('tourist.restaurant-reservations.store', $context['service']), [
            'reservation_date' => now()->addDays(2)->toDateString(),
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 2,
            'total_amount' => '0.01',
            'currency' => 'USD',
            'price' => '0.01',
        ])->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame('275.50', (string) $booking->total_amount);
        $this->assertSame('ETB', $booking->currency);

        $context['service']->update(['price' => 999]);
        $this->assertSame('275.50', (string) $booking->fresh()->total_amount);

        $this->actingAs($context['providerUser'])->patch(route('restaurant.reservations.accept', $booking))->assertRedirect();
        $this->assertSame('accepted', $booking->fresh()->status);
        $this->assertSame('275.50', (string) $booking->fresh()->total_amount);

        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('initializeTransaction')->once()->with(Mockery::on(fn (array $payload) => $payload['amount'] === '275.50' && $payload['currency'] === 'ETB'))->andReturn([
            'status' => 'success', 'data' => ['checkout_url' => 'https://checkout.test/restaurant'],
        ]);
        $this->app->instance(PaymentGatewayInterface::class, $gateway);

        $this->actingAs($context['touristUser'])->post(route('payments.initialize', $booking), ['amount' => '1.00'])->assertRedirect('https://checkout.test/restaurant');
        $this->assertDatabaseHas('payments', ['booking_id' => $booking->booking_id, 'amount' => '275.50']);
    }

    public function test_restaurant_booking_rejects_a_zero_priced_offering(): void
    {
        $context = $this->restaurantContext();
        RestaurantTable::create(['provider_id' => $context['provider']->provider_id, 'table_number' => 'P-2', 'capacity' => 4, 'status' => 'active']);
        $context['service']->update(['price' => 0]);

        $this->actingAs($context['touristUser'])->post(route('tourist.restaurant-reservations.store', $context['service']), [
            'reservation_date' => now()->addDays(2)->toDateString(),
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 2,
        ])->assertSessionHasErrors('price');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_transportation_pricing_charges_a_minimum_one_day_and_ignores_client_amount_inputs(): void
    {
        $context = $this->transportationContext();
        $pickup = CarbonImmutable::now()->addDays(2)->setTime(9, 0);

        $this->actingAs($context['touristUser'])->post(route('tourist.transportation-reservations.store', $context['service']), [
            'pickup_location' => 'Airport',
            'dropoff_location' => 'Castle',
            'pickup_at' => $pickup->toDateTimeString(),
            'dropoff_at' => $pickup->addHours(4)->toDateTimeString(),
            'passenger_count' => 2,
            'total_amount' => '1.00',
            'daily_rate' => '1.00',
            'currency' => 'USD',
            'provider_id' => 999999,
        ])->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame('800.00', (string) $booking->total_amount);
        $this->assertSame('ETB', $booking->currency);
        $this->assertSame(1, app(BookingAmountService::class)->transportationRentalDays($pickup, $pickup->addHours(4)));
    }

    public function test_transportation_pricing_uses_complete_twenty_four_hour_rental_blocks_and_preserves_history(): void
    {
        $context = $this->transportationContext();
        $pickup = CarbonImmutable::now()->addDays(3)->setTime(9, 0);

        $this->actingAs($context['touristUser'])->post(route('tourist.transportation-reservations.store', $context['service']), [
            'pickup_location' => 'Airport',
            'dropoff_location' => 'Bahir Dar',
            'pickup_at' => $pickup->toDateTimeString(),
            'dropoff_at' => $pickup->addDays(2)->toDateTimeString(),
            'passenger_count' => 2,
        ])->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame('1600.00', (string) $booking->total_amount);
        $this->assertSame(2, app(BookingAmountService::class)->transportationRentalDays($pickup, $pickup->addDays(2)));

        $context['service']->update(['price' => 1200]);
        $this->assertSame('1600.00', (string) $booking->fresh()->total_amount);

        $this->actingAs($context['providerUser'])->patch(route('transportation.reservations.accept', $booking))->assertRedirect();
        $this->assertSame('accepted', $booking->fresh()->status);
        $this->assertSame('1600.00', (string) $booking->fresh()->total_amount);

        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('initializeTransaction')->once()->with(Mockery::on(fn (array $payload) => $payload['amount'] === '1600.00' && $payload['currency'] === 'ETB'))->andReturn([
            'status' => 'success', 'data' => ['checkout_url' => 'https://checkout.test/transportation'],
        ]);
        $this->app->instance(PaymentGatewayInterface::class, $gateway);

        $this->actingAs($context['touristUser'])->post(route('payments.initialize', $booking), ['amount' => '0.01'])->assertRedirect('https://checkout.test/transportation');
        $this->assertDatabaseHas('payments', ['booking_id' => $booking->booking_id, 'amount' => '1600.00']);
    }

    public function test_transportation_pricing_rejects_invalid_date_ranges_and_zero_daily_rates(): void
    {
        $context = $this->transportationContext();
        $pickup = CarbonImmutable::now()->addDays(2)->setTime(9, 0);

        $this->actingAs($context['touristUser'])->post(route('tourist.transportation-reservations.store', $context['service']), [
            'pickup_location' => 'Airport',
            'dropoff_location' => 'Castle',
            'pickup_at' => $pickup->toDateTimeString(),
            'dropoff_at' => $pickup->toDateTimeString(),
            'passenger_count' => 2,
        ])->assertSessionHasErrors('dropoff_at');

        $context['service']->update(['price' => 0]);
        $this->actingAs($context['touristUser'])->post(route('tourist.transportation-reservations.store', $context['service']), [
            'pickup_location' => 'Airport',
            'dropoff_location' => 'Castle',
            'pickup_at' => $pickup->toDateTimeString(),
            'dropoff_at' => $pickup->addDay()->toDateTimeString(),
            'passenger_count' => 2,
        ])->assertSessionHasErrors('price');
    }

    /** @return array{providerUser: User, provider: ServiceProvider, service: TourismService, touristUser: User} */
    private function restaurantContext(): array
    {
        [$providerUser, $provider, $service] = $this->serviceContext('restaurant', 'Dining Reservation', '275.50');
        $tourist = $this->tourist();

        return compact('providerUser', 'provider', 'service') + ['touristUser' => $tourist];
    }

    /** @return array{providerUser: User, provider: ServiceProvider, service: TourismService, touristUser: User} */
    private function transportationContext(): array
    {
        [$providerUser, $provider, $service] = $this->serviceContext('transportation_car_rental', 'Daily Car Rental', '800.00');
        TransportationVehicle::create([
            'provider_id' => $provider->provider_id,
            'service_id' => $service->service_id,
            'vehicle_identifier' => 'PRICE-1',
            'vehicle_type' => 'SUV',
            'capacity' => 4,
            'status' => 'active',
        ]);

        return compact('providerUser', 'provider', 'service') + ['touristUser' => $this->tourist()];
    }

    /** @return array{0: User, 1: ServiceProvider, 2: TourismService} */
    private function serviceContext(string $providerType, string $serviceName, string $price): array
    {
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Pricing Provider',
            'provider_type' => $providerType,
            'status' => 'approved',
        ]);
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar '.uniqid(), 'location' => 'Gondar', 'description' => 'Pricing test destination']);
        $category = Category::create(['category_name' => $providerType === 'restaurant' ? 'Dining Reservation' : 'Transportation']);
        $service = TourismService::create([
            'provider_id' => $provider->provider_id,
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => $serviceName,
            'price' => $price,
            'description' => 'Pricing test offering',
        ]);

        return [$providerUser, $provider, $service];
    }

    private function tourist(): User
    {
        $user = User::factory()->create(['role' => 'tourist']);
        Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Pricing Tourist', 'nationality' => 'Ethiopian']);

        return $user;
    }
}
