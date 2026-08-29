<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Administrator;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\ProviderLedgerEntry;
use App\Models\ProviderSubscription;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPlan;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\ProviderBalanceService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ProviderLedgerTest extends TestCase
{
    use RefreshDatabase;

    // ---------- payment → ledger integration ----------

    public function test_successful_commissioned_payment_creates_earning_and_commission_entries(): void
    {
        [$provider, $service] = $this->providerService();
        $this->subscribe($provider, '7.50');

        $owner = $this->touristUser();
        $booking = $this->serviceBooking($owner, $service, '2000.00');
        $payment = $this->pay($owner, $booking, '2000.00');

        $entries = ProviderLedgerEntry::where('payment_id', $payment->payment_id)->get();

        $this->assertCount(2, $entries);

        $earning = $entries->firstWhere('entry_type', 'earning');
        $this->assertSame('2000.00', $earning->amount);
        $this->assertSame(ServiceProvider::class, $earning->payable_type);
        $this->assertSame($provider->provider_id, $earning->payable_id);
        $this->assertSame($booking->booking_id, $earning->booking_id);
        $this->assertSame('ETB', $earning->currency);

        // Commission is stored as a NEGATIVE deduction — never double-counted.
        $commission = $entries->firstWhere('entry_type', 'commission');
        $this->assertSame('-150.00', $commission->amount);

        // Net effect reproducible from entries: 2000 - 150 = 1850.
        $totals = app(ProviderBalanceService::class)->totalsFor($provider);
        $this->assertSame('2000.00', $totals['gross_earnings']);
        $this->assertSame('150.00', $totals['commission_deductions']);
        $this->assertSame('1850.00', $totals['net_earnings']);
        $this->assertSame($payment->provider_net_amount, $totals['net_earnings']);
    }

    public function test_trial_provider_payment_creates_earning_without_commission(): void
    {
        [, $service] = $this->providerService(); // no subscription

        $owner = $this->touristUser();
        $booking = $this->serviceBooking($owner, $service, '1000.00');
        $payment = $this->pay($owner, $booking, '1000.00');

        $entries = ProviderLedgerEntry::where('payment_id', $payment->payment_id)->get();

        $this->assertCount(1, $entries);
        $this->assertSame('earning', $entries->first()->entry_type);
        $this->assertSame('1000.00', $entries->first()->amount);
    }

    public function test_guide_payment_creates_guide_earning_without_commission(): void
    {
        $owner = $this->touristUser();
        $booking = $this->guideBooking($owner, '800.00');
        $guide = $booking->tourGuide;
        $payment = $this->pay($owner, $booking, '800.00');

        $entries = ProviderLedgerEntry::where('payment_id', $payment->payment_id)->get();

        $this->assertCount(1, $entries);
        $this->assertSame(TourGuide::class, $entries->first()->payable_type);
        $this->assertSame($guide->guide_id, $entries->first()->payable_id);
        $this->assertSame('800.00', $entries->first()->amount);

        $totals = app(ProviderBalanceService::class)->totalsFor($guide);
        $this->assertSame('800.00', $totals['gross_earnings']);
        $this->assertSame('0.00', $totals['commission_deductions']);
        $this->assertSame('800.00', $totals['net_earnings']);
    }

    public function test_duplicate_callback_delivery_never_duplicates_ledger_entries(): void
    {
        [$provider, $service] = $this->providerService();
        $this->subscribe($provider, '10.00');

        $owner = $this->touristUser();
        $booking = $this->serviceBooking($owner, $service, '500.00');
        $payment = $this->pay($owner, $booking, '500.00');
        $reference = $payment->gateway_reference;

        // Repeated callback delivery (Chapa retries).
        $this->actingAs($owner)->get(route('payments.chapa.callback', ['tx_ref' => $reference]));
        $this->actingAs($owner)->get(route('payments.chapa.callback', ['tx_ref' => $reference]));
        app(PaymentService::class)->verifyAndFinalize($reference);

        $this->assertSame(2, ProviderLedgerEntry::where('payment_id', $payment->payment_id)->count());
        $this->assertSame(1, ProviderLedgerEntry::where('payment_id', $payment->payment_id)->where('entry_type', 'earning')->count());
        $this->assertSame(1, ProviderLedgerEntry::where('payment_id', $payment->payment_id)->where('entry_type', 'commission')->count());

        $totals = app(ProviderBalanceService::class)->totalsFor($provider);
        $this->assertSame('450.00', $totals['net_earnings']);
    }

    public function test_payment_commission_and_ledger_commit_atomically(): void
    {
        [$provider, $service] = $this->providerService();
        $this->subscribe($provider, '7.50');

        $owner = $this->touristUser();
        $booking = $this->serviceBooking($owner, $service, '1000.00');
        $payment = $this->pay($owner, $booking, '1000.00');

        // Invariant: payment snapshot and ledger agree, always.
        $this->assertSame('75.00', $payment->commission_amount);
        $ledgerCommission = ProviderLedgerEntry::where('payment_id', $payment->payment_id)->where('entry_type', 'commission')->firstOrFail();
        $this->assertSame(Money::negate($payment->commission_amount), $ledgerCommission->amount);
    }

    // ---------- money precision ----------

    public function test_money_precision_edge_cases_are_deterministic(): void
    {
        // 199.99 at 7.5% = 14.99925 -> 15.00 (half-up), net 184.99.
        $this->assertSame('15.00', Money::fromMinor(Money::percentage(Money::toMinor('199.99'), 750)));

        // 0.01 at 7.5% = 0.00075 -> 0.00 (no phantom cent).
        $this->assertSame('0.00', Money::fromMinor(Money::percentage(Money::toMinor('0.01'), 750)));

        // Large values stay exact — no binary floating-point drift.
        $this->assertSame('75000.00', Money::fromMinor(Money::percentage(Money::toMinor('1000000.00'), 750)));

        // Zero is zero.
        $this->assertSame('0.00', Money::fromMinor(Money::percentage(Money::toMinor('0.00'), 1000)));

        // Sum/minus via minor units: 0.1 + 0.2 == 0.30 exactly.
        $this->assertSame('0.30', Money::sum('0.10', '0.20'));
    }

    public function test_fractional_commission_rounds_consistently_end_to_end(): void
    {
        [$provider, $service] = $this->providerService();
        $this->subscribe($provider, '7.50');

        $owner = $this->touristUser();
        $booking = $this->serviceBooking($owner, $service, '199.99');
        $payment = $this->pay($owner, $booking, '199.99');

        $this->assertSame('15.00', $payment->commission_amount);
        $this->assertSame('184.99', $payment->provider_net_amount);
        $this->assertSame('-15.00', ProviderLedgerEntry::where('payment_id', $payment->payment_id)->where('entry_type', 'commission')->value('amount'));
    }

    // ---------- authorization ----------

    public function test_provider_can_view_own_earnings_page_and_only_own_entries(): void
    {
        [$providerA, $serviceA] = $this->providerService('Provider A Hotel');
        [$providerB, $serviceB] = $this->providerService('Provider B Hotel');
        $this->subscribe($providerA, '10.00');
        $this->subscribe($providerB, '10.00');

        $owner = $this->touristUser();
        $this->pay($owner, $this->serviceBooking($owner, $serviceA, '1000.00'), '1000.00');
        $this->pay($owner, $this->serviceBooking($owner, $serviceB, '2000.00'), '2000.00');

        $entryA = ProviderLedgerEntry::where('payable_id', $providerA->provider_id)->where('entry_type', 'earning')->firstOrFail();
        $entryB = ProviderLedgerEntry::where('payable_id', $providerB->provider_id)->where('entry_type', 'earning')->firstOrFail();

        // Policy: owner yes, other provider no, tourist no, admin yes.
        $this->assertTrue($providerA->user->can('view', $entryA));
        $this->assertFalse($providerA->user->can('view', $entryB));
        $this->assertFalse($owner->can('view', $entryA));
        $admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        Administrator::create(['user_id' => $admin->user_id]);
        $this->assertTrue($admin->can('view', $entryA));

        // Endpoint: payable is resolved from the session, so there is no ID to manipulate.
        $this->actingAs($providerA->user)->get(route('hotel.earnings'))
            ->assertOk()
            ->assertSee('1000.00')
            ->assertDontSee('2000.00');

        $this->actingAs($owner)->get(route('hotel.earnings'))->assertForbidden();
    }

    public function test_admin_provider_page_shows_ledger_financial_summary(): void
    {
        [$provider, $service] = $this->providerService();
        $this->subscribe($provider, '7.50');

        $owner = $this->touristUser();
        $this->pay($owner, $this->serviceBooking($owner, $service, '4000.00'), '4000.00');

        $admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        Administrator::create(['user_id' => $admin->user_id]);

        $this->actingAs($admin)->get(route('admin.providers.show', $provider))
            ->assertOk()
            ->assertSee('Financial ledger summary')
            ->assertSee('4000.00')
            ->assertSee('300.00')
            ->assertSee('3700.00');
    }

    public function test_ledger_has_no_update_or_delete_routes(): void
    {
        // Append-only by construction: no user-facing route references the model.
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->map(fn ($route) => $route->uri())
            ->implode(' ');

        $this->assertStringNotContainsString('ledger', strtolower($routes));
    }

    // ---------- migration ----------

    public function test_ledger_migration_rolls_back_and_reapplies(): void
    {
        $migration = require database_path('migrations/2026_08_30_000000_create_provider_ledger_entries_table.php');

        $this->assertTrue(Schema::hasTable('provider_ledger_entries'));

        $migration->down();
        $this->assertFalse(Schema::hasTable('provider_ledger_entries'));

        $migration->up();
        $this->assertTrue(Schema::hasTable('provider_ledger_entries'));
    }

    // ---------- helpers ----------

    private function touristUser(): User
    {
        $user = User::factory()->create(['role' => 'tourist']);
        Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Ledger Tourist', 'nationality' => 'Ethiopian']);

        return $user;
    }

    /** @return array{0: ServiceProvider, 1: TourismService} */
    private function providerService(string $name = 'Ledger Hotel'): array
    {
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar '.uniqid(), 'location' => 'Gondar', 'description' => 'Ledger test destination']);
        $category = Category::firstOrCreate(['category_name' => 'Room']);

        $providerUser = User::factory()->create(['role' => 'service_provider', 'is_active' => true]);
        $provider = ServiceProvider::create(['user_id' => $providerUser->user_id, 'business_name' => $name, 'provider_type' => 'hotel', 'status' => 'approved', 'verification_status' => 'verified']);
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => $name.' Room', 'price' => 1500.00, 'description' => 'Test room']);

        return [$provider, $service];
    }

    private function subscribe(ServiceProvider $provider, string $rate): void
    {
        $plan = SubscriptionPlan::create(['plan' => 'Plan '.$rate.' '.uniqid(), 'price' => 0, 'commission_rate' => $rate, 'duration' => 30, 'active' => true]);
        ProviderSubscription::create(['provider_id' => $provider->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today(), 'end_date' => today()->addDays(30), 'status' => 'active']);
    }

    private function serviceBooking(User $user, TourismService $service, string $amount): Booking
    {
        return Booking::create(['tourist_id' => $user->tourist->tourist_id, 'service_id' => $service->service_id, 'status' => 'accepted', 'booking_date' => now(), 'total_amount' => $amount, 'currency' => 'ETB']);
    }

    private function guideBooking(User $user, string $amount): Booking
    {
        $guideUser = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create(['user_id' => $guideUser->user_id, 'license_number' => 'LED-'.fake()->unique()->numerify('#####'), 'expertise' => 'History', 'availability_status' => 'available', 'verification_status' => 'verified', 'daily_rate' => 100]);

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
