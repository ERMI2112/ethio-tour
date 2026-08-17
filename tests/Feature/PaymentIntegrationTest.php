<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Booking;
use App\Models\TourGuide;
use App\Models\Tourist;
use App\Models\User;
use App\Services\ChapaGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_booking_owner_can_initialize_payment_and_amount_is_server_sourced(): void
    {
        $owner = $this->touristUser('owner@example.com');
        $other = $this->touristUser('other@example.com');
        $booking = $this->booking($owner, 'accepted', '125.50');
        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('initializeTransaction')->once()->with(Mockery::on(function (array $payload): bool {
            return $payload['amount'] === '125.50'
                && $payload['currency'] === 'ETB'
                && $payload['email'] === 'owner@example.com'
                && ! array_key_exists('total_amount', $payload);
        }))->andReturn(['status' => 'success', 'data' => ['checkout_url' => 'https://checkout.test/tx']]);
        $this->app->instance(PaymentGatewayInterface::class, $gateway);

        $this->actingAs($other)->post(route('payments.initialize', $booking), ['amount' => '0.01'])->assertForbidden();
        $this->actingAs($owner)->post(route('payments.initialize', $booking), ['amount' => '0.01'])->assertRedirect('https://checkout.test/tx');

        $this->assertDatabaseHas('payments', ['booking_id' => $booking->booking_id, 'amount' => '125.50', 'status' => 'pending', 'payment_method' => 'chapa']);
        $this->assertSame('payment_pending', $booking->fresh()->status);
    }

    public function test_pay_now_is_unavailable_for_non_payable_booking_states(): void
    {
        $owner = $this->touristUser();

        foreach (['pending', 'rejected', 'cancelled', 'completed'] as $status) {
            $booking = $this->booking($owner, $status, '100.00');
            $this->actingAs($owner)->post(route('payments.initialize', $booking))->assertForbidden();
        }
    }

    public function test_initialization_failure_leaves_a_retryable_failed_payment_without_confirming(): void
    {
        $owner = $this->touristUser();
        $booking = $this->booking($owner, 'accepted', '100.00');
        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('initializeTransaction')->andThrow(new \RuntimeException('sandbox unavailable'));
        $this->app->instance(PaymentGatewayInterface::class, $gateway);

        $this->actingAs($owner)->post(route('payments.initialize', $booking))->assertSessionHas('error', 'Chapa payment initialization failed.');
        $this->assertSame('accepted', $booking->fresh()->status);
        $this->assertSame('failed', $booking->fresh()->payment->status);
    }

    public function test_an_active_payment_attempt_is_not_initialized_again(): void
    {
        $owner = $this->touristUser();
        $booking = $this->booking($owner, 'accepted', '100.00');
        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('initializeTransaction')->once()->andReturn(['status' => 'success', 'data' => ['checkout_url' => 'https://checkout.test/tx']]);
        $this->app->instance(PaymentGatewayInterface::class, $gateway);

        $this->actingAs($owner)->post(route('payments.initialize', $booking))->assertRedirect();
        $this->actingAs($owner)->post(route('payments.initialize', $booking))->assertSessionHas('error', 'A payment attempt is already in progress for this booking.');
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_callback_verifies_amount_and_confirms_booking_once(): void
    {
        $owner = $this->touristUser();
        $booking = $this->booking($owner, 'accepted', '200.00');
        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('initializeTransaction')->andReturn(['status' => 'success', 'data' => ['checkout_url' => 'https://checkout.test/tx']]);
        $gateway->shouldReceive('verifyTransaction')->once()->with(Mockery::type('string'))->andReturnUsing(function (string $reference): array {
            return ['status' => 'success', 'data' => ['tx_ref' => $reference, 'status' => 'success', 'amount' => '200.00', 'currency' => 'ETB']];
        });
        $this->app->instance(PaymentGatewayInterface::class, $gateway);
        $this->actingAs($owner)->post(route('payments.initialize', $booking))->assertRedirect();
        $reference = $booking->fresh()->payment->gateway_reference;
        $this->actingAs($owner)->get(route('payments.chapa.callback', ['tx_ref' => $reference]))->assertRedirect(route('tourist.reservations.show', $booking));
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame('success', $booking->fresh()->payment->status);
    }

    public function test_invalid_verification_does_not_confirm_booking(): void
    {
        $owner = $this->touristUser();
        $booking = $this->booking($owner, 'accepted', '200.00');
        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('initializeTransaction')->andReturn(['status' => 'success', 'data' => ['checkout_url' => 'https://checkout.test/tx']]);
        $gateway->shouldReceive('verifyTransaction')->andReturn(['status' => 'success', 'data' => ['tx_ref' => 'wrong-ref', 'status' => 'success', 'amount' => '999.00', 'currency' => 'USD']]);
        $this->app->instance(PaymentGatewayInterface::class, $gateway);
        $this->actingAs($owner)->post(route('payments.initialize', $booking));
        $reference = $booking->fresh()->payment->gateway_reference;

        $this->actingAs($owner)->get(route('payments.chapa.callback', ['tx_ref' => $reference]))->assertRedirect(route('login'));
        $this->assertSame('payment_pending', $booking->fresh()->status);
        $this->assertSame('failed', $booking->fresh()->payment->status);
    }

    public function test_stale_payment_amount_cannot_override_the_booking_amount(): void
    {
        $owner = $this->touristUser();
        $booking = $this->booking($owner, 'accepted', '200.00');
        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('initializeTransaction')->andReturn(['status' => 'success', 'data' => ['checkout_url' => 'https://checkout.test/tx']]);
        $gateway->shouldReceive('verifyTransaction')->never();
        $this->app->instance(PaymentGatewayInterface::class, $gateway);
        $this->actingAs($owner)->post(route('payments.initialize', $booking));
        $payment = $booking->fresh()->payment;
        $payment->update(['amount' => '999.00']);

        $this->actingAs($owner)->get(route('payments.chapa.callback', ['tx_ref' => $payment->gateway_reference]))->assertRedirect(route('login'));
        $this->assertSame('payment_pending', $booking->fresh()->status);
        $this->assertSame('failed', $booking->fresh()->payment->status);
    }

    public function test_webhook_signature_and_duplicate_delivery_are_safe(): void
    {
        config(['services.chapa.webhook_secret' => 'webhook-test-secret']);
        $owner = $this->touristUser();
        $booking = $this->booking($owner, 'accepted', '75.00');
        $gateway = Mockery::mock(PaymentGatewayInterface::class);
        $gateway->shouldReceive('initializeTransaction')->andReturn(['status' => 'success', 'data' => ['checkout_url' => 'https://checkout.test/tx']]);
        $gateway->shouldReceive('verifyTransaction')->once()->andReturnUsing(function (string $reference) {
            return ['status' => 'success', 'data' => ['tx_ref' => $reference, 'status' => 'success', 'amount' => '75.00', 'currency' => 'ETB']];
        });
        $this->app->instance(PaymentGatewayInterface::class, $gateway);
        $this->actingAs($owner)->post(route('payments.initialize', $booking));
        $reference = $booking->fresh()->payment->gateway_reference;
        $payload = json_encode(['tx_ref' => $reference]);
        $signature = hash_hmac('sha256', $payload, 'webhook-test-secret');

        $this->call('POST', route('payments.chapa.webhook'), [], [], [], ['HTTP_X_CHAPA_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'], $payload)->assertOk();
        $this->call('POST', route('payments.chapa.webhook'), [], [], [], ['HTTP_X_CHAPA_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'], $payload)->assertOk();
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_chapa_gateway_uses_server_configuration_and_expected_endpoints(): void
    {
        config(['services.chapa.secret_key' => 'CHASECK_TEST-example', 'services.chapa.base_url' => 'https://chapa.test/v1']);
        Http::fake([
            'https://chapa.test/v1/transaction/initialize' => Http::response(['status' => 'success', 'data' => ['checkout_url' => 'https://checkout.test/abc']], 200),
            'https://chapa.test/v1/transaction/verify/ETHIO-ABC' => Http::response(['status' => 'success', 'data' => ['tx_ref' => 'ETHIO-ABC', 'status' => 'success', 'amount' => '10.00', 'currency' => 'ETB']], 200),
        ]);
        $gateway = app(ChapaGateway::class);

        $result = $gateway->initializeTransaction(['amount' => '10.00', 'currency' => 'ETB', 'tx_ref' => 'ETHIO-ABC']);
        $gateway->verifyTransaction('ETHIO-ABC');

        $this->assertSame('https://checkout.test/abc', data_get($result, 'data.checkout_url'));
        Http::assertSent(fn ($request) => $request->url() === 'https://chapa.test/v1/transaction/initialize' && $request->header('Authorization')[0] === 'Bearer CHASECK_TEST-example');
        Http::assertSent(fn ($request) => $request->url() === 'https://chapa.test/v1/transaction/verify/ETHIO-ABC');
    }

    private function touristUser(?string $email = null): User
    {
        $user = User::factory()->create(['email' => $email ?? fake()->safeEmail(), 'role' => 'tourist']);
        Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Payment Tourist', 'nationality' => 'Ethiopian']);

        return $user;
    }

    private function booking(User $user, string $status, string $amount): Booking
    {
        $guideUser = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create(['user_id' => $guideUser->user_id, 'license_number' => 'PAY-'.$user->user_id.'-'.$status.'-'.fake()->unique()->numerify('###'), 'expertise' => 'History', 'availability_status' => 'available', 'verification_status' => 'verified', 'daily_rate' => 100]);

        return Booking::create(['tourist_id' => $user->tourist->tourist_id, 'guide_id' => $guide->guide_id, 'status' => $status, 'booking_date' => now(), 'total_amount' => $amount, 'currency' => 'ETB']);
    }
}
