<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\TourGuide;
use App\Models\TourGuideReservation;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TourGuideReservationFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_verification_defaults_to_pending_and_cannot_be_changed_from_the_profile(): void
    {
        $context = $this->guideContext();

        $this->assertSame('pending', $context['guide']->verification_status);

        $this->actingAs($context['user'])->put(route('tour-guide.profile.update'), [
            'expertise' => 'Updated expertise',
            'availability_status' => 'available',
            'verification_status' => 'verified',
            'officer_id' => 999,
        ])->assertRedirect(route('tour-guide.profile'));

        $this->assertDatabaseHas('tour_guides', [
            'guide_id' => $context['guide']->guide_id,
            'expertise' => 'Updated expertise',
            'availability_status' => 'available',
            'verification_status' => 'pending',
        ]);

        $context['guide']->update(['verification_status' => 'verified']);
        $this->assertSame('pending', $context['guide']->fresh()->verification_status);
    }

    public function test_central_guide_booking_has_one_reservation_and_retains_payment_and_review_relationships(): void
    {
        $booking = $this->guideBooking();
        $reservation = $booking->tourGuideReservation()->create([
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12',
            'number_of_tourists' => 3,
        ]);

        Payment::create([
            'booking_id' => $booking->booking_id,
            'amount' => 1500,
            'status' => 'pending',
            'payment_method' => 'chapa',
        ]);
        Review::create([
            'booking_id' => $booking->booking_id,
            'tourist_id' => $booking->tourist_id,
            'rating' => 5,
            'comment' => 'Excellent guide',
            'review_date' => today(),
        ]);

        $booking->refresh();

        $this->assertTrue($reservation->booking->is($booking));
        $this->assertTrue($booking->tourGuideReservation->is($reservation));
        $this->assertSame('2026-10-10', $reservation->start_date->toDateString());
        $this->assertSame(3, $reservation->number_of_tourists);
        $this->assertNotNull($booking->payment);
        $this->assertNotNull($booking->review);
    }

    public function test_guide_reservation_rejects_invalid_dates_and_party_size(): void
    {
        $booking = $this->guideBooking();

        try {
            TourGuideReservation::create([
                'booking_id' => $booking->booking_id,
                'start_date' => '2026-10-12',
                'end_date' => '2026-10-12',
                'number_of_tourists' => 1,
            ]);
            $this->fail('Expected invalid tour dates to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('end_date', $exception->errors());
        }

        try {
            TourGuideReservation::create([
                'booking_id' => $booking->booking_id,
                'start_date' => '2026-10-12',
                'end_date' => '2026-10-13',
                'number_of_tourists' => 0,
            ]);
            $this->fail('Expected a zero party size to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('number_of_tourists', $exception->errors());
        }
    }

    public function test_guide_reservation_enforces_one_record_per_central_booking(): void
    {
        $booking = $this->guideBooking();
        TourGuideReservation::create([
            'booking_id' => $booking->booking_id,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12',
            'number_of_tourists' => 2,
        ]);

        $this->expectException(QueryException::class);

        TourGuideReservation::create([
            'booking_id' => $booking->booking_id,
            'start_date' => '2026-10-14',
            'end_date' => '2026-10-15',
            'number_of_tourists' => 1,
        ]);
    }

    /**
     * @return array{user: User, guide: TourGuide}
     */
    private function guideContext(): array
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $user->user_id,
            'license_number' => 'GUIDE-'.uniqid(),
            'expertise' => 'Historical tours',
            'availability_status' => 'unavailable',
        ]);

        return compact('user', 'guide');
    }

    private function guideBooking(): Booking
    {
        $context = $this->guideContext();
        /** @var User $touristUser */
        $touristUser = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create([
            'user_id' => $touristUser->user_id,
            'full_name' => 'Test Tourist',
            'nationality' => 'Ethiopian',
        ]);

        return Booking::create([
            'tourist_id' => $tourist->tourist_id,
            'guide_id' => $context['guide']->guide_id,
            'status' => 'pending',
            'booking_date' => now(),
        ]);
    }
}
