<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TourGuide;
use App\Models\Tourist;
use App\Models\User;
use App\Services\BookingAmountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GuidePricingAndBookingAmountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_can_set_a_valid_daily_rate_from_their_profile(): void
    {
        $context = $this->guide();

        $this->actingAs($context['user'])->put(route('tour-guide.profile.update'), [
            'expertise' => 'History',
            'availability_status' => 'available',
            'daily_rate' => '1250.50',
        ])->assertRedirect(route('tour-guide.profile'));

        $this->assertDatabaseHas('tour_guides', ['guide_id' => $context['guide']->guide_id, 'daily_rate' => 1250.50]);
    }

    public function test_negative_daily_rate_is_rejected_and_tourist_cannot_edit_it(): void
    {
        $context = $this->guide();
        $this->actingAs($context['user'])->put(route('tour-guide.profile.update'), [
            'expertise' => 'History', 'availability_status' => 'available', 'daily_rate' => -1,
        ])->assertSessionHasErrors('daily_rate');

        $tourist = $this->tourist();
        $this->actingAs($tourist['user'])->put(route('tour-guide.profile.update'), ['daily_rate' => 9999])->assertForbidden();
    }

    public function test_booking_amount_service_uses_half_open_chargeable_days_and_validates_dates(): void
    {
        $guide = $this->guide()['guide'];
        $amount = app(BookingAmountService::class)->calculateGuideBooking($guide, '2026-09-10', '2026-09-13');

        $this->assertSame(3, $amount['chargeable_days']);
        $this->assertSame('3000.00', $amount['total_amount']);
        $this->assertSame('ETB', $amount['currency']);

        $this->expectException(ValidationException::class);
        app(BookingAmountService::class)->chargeableDays('2026-09-13', '2026-09-10');
    }

    public function test_zero_and_decimal_rates_are_calculated_without_client_total(): void
    {
        $guide = $this->guide()['guide'];
        $guide->forceFill(['daily_rate' => 0.01])->save();
        $amount = app(BookingAmountService::class)->calculateGuideBooking($guide, '2026-09-10', '2026-09-11');

        $this->assertSame('0.01', $amount['total_amount']);
    }

    public function test_tourist_booking_freezes_server_calculated_total_and_ignores_tampered_amount(): void
    {
        $guide = $this->guide()['guide'];
        $tourist = $this->tourist();

        $this->actingAs($tourist['user'])->post(route('tour-guides.book.store', $guide), [
            'start_date' => '2026-09-10',
            'end_date' => '2026-09-13',
            'number_of_tourists' => 1,
            'total_amount' => 1,
            'currency' => 'USD',
        ])->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame('3000.00', (string) $booking->total_amount);
        $this->assertSame('ETB', $booking->currency);
    }

    public function test_changing_guide_rate_does_not_change_existing_booking_amount(): void
    {
        $guide = $this->guide()['guide'];
        $tourist = $this->tourist();
        $this->actingAs($tourist['user'])->post(route('tour-guides.book.store', $guide), [
            'start_date' => '2026-09-10', 'end_date' => '2026-09-12', 'number_of_tourists' => 1,
        ])->assertRedirect();

        $booking = Booking::firstOrFail();
        $guide->forceFill(['daily_rate' => 2500])->save();

        $this->assertSame('2000.00', (string) $booking->fresh()->total_amount);
    }

    public function test_guide_acceptance_preserves_the_frozen_booking_amount(): void
    {
        $context = $this->guide();
        $tourist = $this->tourist();
        $booking = Booking::create([
            'tourist_id' => $tourist['tourist']->tourist_id,
            'guide_id' => $context['guide']->guide_id,
            'status' => 'pending',
            'total_amount' => '2000.00',
            'currency' => 'ETB',
            'booking_date' => now(),
        ]);
        $booking->tourGuideReservation()->create([
            'start_date' => '2026-09-10',
            'end_date' => '2026-09-12',
            'number_of_tourists' => 1,
        ]);

        $this->actingAs($context['user'])->patch(route('tour-guide.requests.accept', $booking))->assertRedirect();

        $this->assertSame('accepted', $booking->fresh()->status);
        $this->assertSame('2000.00', (string) $booking->fresh()->total_amount);
    }

    public function test_guide_without_a_rate_cannot_open_a_booking_request(): void
    {
        $context = $this->guide();
        $context['guide']->forceFill(['daily_rate' => null])->save();
        $tourist = $this->tourist();
        $this->actingAs($tourist['user'])->get(route('tour-guides.book', $context['guide']))->assertNotFound();
    }

    /** @return array{user: User, guide: TourGuide} */
    private function guide(): array
    {
        $user = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $user->user_id,
            'license_number' => 'RATE-'.uniqid(),
            'expertise' => 'History',
            'availability_status' => 'available',
        ]);
        $guide->forceFill(['verification_status' => 'verified', 'admin_approval_status' => 'approved', 'daily_rate' => 1000])->save();

        return compact('user', 'guide');
    }

    /** @return array{user: User, tourist: Tourist} */
    private function tourist(): array
    {
        $user = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Tourist', 'nationality' => 'Ethiopian']);

        return compact('user', 'tourist');
    }
}
