<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Administrator;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\ProviderSubscription;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPlan;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class MonetizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_is_captured_when_a_subscribed_provider_is_paid(): void
    {
        [$provider, $service] = $this->providerService();
        $plan = SubscriptionPlan::create(['plan' => 'Growth', 'price' => 500, 'commission_rate' => 7.50, 'duration' => 30, 'active' => true]);
        ProviderSubscription::create(['provider_id' => $provider->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today(), 'end_date' => today()->addDays(30), 'status' => 'active']);

        $owner = $this->touristUser();
        $booking = $this->serviceBooking($owner, $service, '2000.00');
        $payment = $this->pay($owner, $booking, '2000.00');

        $this->assertSame('success', $payment->status);
        $this->assertSame('7.50', $payment->commission_rate);
        $this->assertSame('150.00', $payment->commission_amount);
        $this->assertSame('1850.00', $payment->provider_net_amount);
    }

    public function test_trial_provider_payment_captures_no_commission(): void
    {
        [, $service] = $this->providerService(); // no subscription assigned

        $owner = $this->touristUser();
        $booking = $this->serviceBooking($owner, $service, '1000.00');
        $payment = $this->pay($owner, $booking, '1000.00');

        $this->assertSame('success', $payment->status);
        $this->assertNull($payment->commission_rate);
        $this->assertNull($payment->commission_amount);
        $this->assertNull($payment->provider_net_amount);
    }

    public function test_guide_booking_payment_captures_no_commission(): void
    {
        $owner = $this->touristUser();
        $booking = $this->guideBooking($owner, '800.00');
        $payment = $this->pay($owner, $booking, '800.00');

        $this->assertSame('success', $payment->status);
        $this->assertNull($payment->commission_rate);
    }

    public function test_expired_subscription_blocks_new_hotel_bookings(): void
    {
        [$provider, $service] = $this->providerService();
        $service->hotelRoomType()->create(['capacity' => 2, 'amenities' => ['wifi']])->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $plan = SubscriptionPlan::create(['plan' => 'Starter', 'price' => 0, 'commission_rate' => 10.00, 'duration' => 30, 'active' => true]);
        ProviderSubscription::create(['provider_id' => $provider->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today()->subDays(40), 'end_date' => today()->subDays(10), 'status' => 'expired']);

        $tourist = $this->touristUser();
        $this->actingAs($tourist)->post(route('tourist.reservations.store', $service), [
            'check_in_date' => date('Y-m-d', strtotime('+1 day')),
            'check_out_date' => date('Y-m-d', strtotime('+3 days')),
            'guest_count' => 2,
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_active_subscription_keeps_bookings_flowing(): void
    {
        [$provider, $service] = $this->providerService();
        $service->hotelRoomType()->create(['capacity' => 2, 'amenities' => ['wifi']])->hotelRooms()->create(['room_number' => '101', 'status' => 'active']);
        $plan = SubscriptionPlan::create(['plan' => 'Starter', 'price' => 0, 'commission_rate' => 10.00, 'duration' => 30, 'active' => true]);
        ProviderSubscription::create(['provider_id' => $provider->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today(), 'end_date' => today()->addDays(30), 'status' => 'active']);

        $tourist = $this->touristUser();
        $this->actingAs($tourist)->post(route('tourist.reservations.store', $service), [
            'check_in_date' => date('Y-m-d', strtotime('+1 day')),
            'check_out_date' => date('Y-m-d', strtotime('+3 days')),
            'guest_count' => 2,
        ]);

        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_expire_command_transitions_lapsed_subscriptions_and_reminds(): void
    {
        [$provider] = $this->providerService();
        $plan = SubscriptionPlan::create(['plan' => 'Growth', 'price' => 500, 'commission_rate' => 7.50, 'duration' => 30, 'active' => true]);
        $lapsed = ProviderSubscription::create(['provider_id' => $provider->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today()->subDays(40), 'end_date' => today()->subDay(), 'status' => 'active']);

        [, $service2] = $this->providerService('Second Hotel');
        $expiringSoon = ProviderSubscription::create(['provider_id' => $service2->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today()->subDays(23), 'end_date' => today()->addDays(7), 'status' => 'active']);

        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame('expired', $lapsed->fresh()->status);
        $this->assertSame('active', $expiringSoon->fresh()->status);
        $this->assertDatabaseHas('notifications', ['type' => 'subscription_expired']);
        $this->assertDatabaseHas('notifications', ['type' => 'subscription_expiring']);
    }

    public function test_scheduler_registers_subscription_expiry(): void
    {
        Artisan::call('schedule:list');
        $this->assertStringContainsString('subscriptions:expire', Artisan::output());
    }

    public function test_admin_report_displays_platform_revenue(): void
    {
        [$provider, $service] = $this->providerService();
        $plan = SubscriptionPlan::create(['plan' => 'Premium', 'price' => 1500, 'commission_rate' => 5.00, 'duration' => 90, 'active' => true]);
        ProviderSubscription::create(['provider_id' => $provider->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today(), 'end_date' => today()->addDays(90), 'status' => 'active']);

        $owner = $this->touristUser();
        $booking = $this->serviceBooking($owner, $service, '4000.00');
        $this->pay($owner, $booking, '4000.00');

        $adminUser = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        Administrator::create(['user_id' => $adminUser->user_id]);

        $this->actingAs($adminUser)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Platform revenue')
            ->assertSee('4,000.00 ETB')
            ->assertSee('200.00 ETB')   // 5% commission
            ->assertSee('3,800.00 ETB'); // provider net
    }

    public function test_admin_report_shows_seeded_demo_commission(): void
    {
        $this->seed(); // full consolidated demo chain

        $adminUser = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        Administrator::create(['user_id' => $adminUser->user_id]);

        // Seeded commission: 225.00 (hotel) + 35.00 (restaurant) + 360.00 (transport) + 50.00 (event).
        $this->actingAs($adminUser)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Platform revenue')
            ->assertSee('670.00 ETB');
    }

    // ---------- helpers ----------

    private function touristUser(): User
    {
        $user = User::factory()->create(['role' => 'tourist']);
        Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Monetization Tourist', 'nationality' => 'Ethiopian']);

        return $user;
    }

    /** @return array{0: ServiceProvider, 1: TourismService} */
    private function providerService(string $name = 'Monetization Hotel'): array
    {
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar '.uniqid(), 'location' => 'Gondar', 'description' => 'Monetization test destination']);
        $category = Category::firstOrCreate(['category_name' => 'Room']);

        $providerUser = User::factory()->create(['role' => 'service_provider', 'is_active' => true]);
        $provider = ServiceProvider::create(['user_id' => $providerUser->user_id, 'business_name' => $name, 'provider_type' => 'hotel', 'status' => 'approved', 'verification_status' => 'verified']);
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => $name.' Room', 'price' => 1500.00, 'description' => 'Test room']);

        return [$provider, $service];
    }

    private function serviceBooking(User $user, TourismService $service, string $amount): Booking
    {
        return Booking::create(['tourist_id' => $user->tourist->tourist_id, 'service_id' => $service->service_id, 'status' => 'accepted', 'booking_date' => now(), 'total_amount' => $amount, 'currency' => 'ETB']);
    }

    private function guideBooking(User $user, string $amount): Booking
    {
        $guideUser = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create(['user_id' => $guideUser->user_id, 'license_number' => 'MON-'.fake()->unique()->numerify('#####'), 'expertise' => 'History', 'availability_status' => 'available', 'verification_status' => 'verified', 'daily_rate' => 100]);

        return Booking::create(['tourist_id' => $user->tourist->tourist_id, 'guide_id' => $guide->guide_id, 'status' => 'accepted', 'booking_date' => now(), 'total_amount' => $amount, 'currency' => 'ETB']);
    }

    private function pay(User $owner, Booking $booking, string $amount): Payment
    {
        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('initializeTransaction')->andReturn(['status' => 'success', 'data' => ['checkout_url' => 'https://checkout.test/tx']]);
        $gateway->shouldReceive('verifyTransaction')->andReturnUsing(fn (string $reference): array => ['status' => 'success', 'data' => ['tx_ref' => $reference, 'status' => 'success', 'amount' => $amount, 'currency' => 'ETB']]);
        $this->app->instance(PaymentGatewayInterface::class, $gateway);

        $this->actingAs($owner)->post(route('payments.initialize', $booking))->assertRedirect();
        $reference = $booking->fresh()->payment->gateway_reference;
        $this->actingAs($owner)->get(route('payments.chapa.callback', ['tx_ref' => $reference]));

        return $booking->fresh()->payment;
    }
}
