<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Review;
use App\Models\TourGuide;
use App\Models\TourGuideReservation;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewVerticalTest extends TestCase
{
    use RefreshDatabase;

    public function test_tourist_can_review_own_completed_booking_once(): void
    {
        $context = $this->booking('completed');

        $this->actingAs($context['user'])->post(route('tourist.reservations.reviews.store', $context['booking']), ['rating' => 5, 'comment' => 'Excellent guide and a memorable experience.'])->assertRedirect();
        $this->assertDatabaseHas('reviews', ['booking_id' => $context['booking']->booking_id, 'tourist_id' => $context['tourist']->tourist_id, 'rating' => 5]);
        $this->assertDatabaseCount('reviews', 1);
        $this->actingAs($context['user'])->post(route('tourist.reservations.reviews.store', $context['booking']), ['rating' => 4, 'comment' => 'Another review should not be created.'])->assertSessionHas('error');
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_ineligible_and_other_tourist_bookings_are_protected(): void
    {
        $pending = $this->booking('pending');
        $this->actingAs($pending['user'])->post(route('tourist.reservations.reviews.store', $pending['booking']), ['rating' => 5, 'comment' => 'This should not be accepted.'])->assertForbidden();
        $other = $this->booking('completed');
        $this->actingAs($pending['user'])->post(route('tourist.reservations.reviews.store', $other['booking']), ['rating' => 5, 'comment' => 'This belongs to someone else.'])->assertForbidden();
    }

    public function test_confirmed_ended_guide_booking_is_reviewable_but_future_is_not(): void
    {
        $context = $this->booking('confirmed', '2020-01-01', '2020-01-02');
        $this->actingAs($context['user'])->post(route('tourist.reservations.reviews.store', $context['booking']), ['rating' => 4, 'comment' => 'The completed dates are now reviewable.'])->assertRedirect();

        $future = $this->booking('confirmed', '2099-01-01', '2099-01-02');
        $this->actingAs($future['user'])->post(route('tourist.reservations.reviews.store', $future['booking']), ['rating' => 4, 'comment' => 'This future booking is not reviewable.'])->assertForbidden();
    }

    public function test_review_validation_and_tourist_identity_cannot_be_tampered_with(): void
    {
        $context = $this->booking('completed');
        $response = $this->actingAs($context['user'])->post(route('tourist.reservations.reviews.store', $context['booking']), ['rating' => 6, 'comment' => 'short', 'tourist_id' => 999999]);
        $response->assertSessionHasErrors(['rating', 'comment']);
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_public_rating_aggregation_and_admin_moderation_are_centralized(): void
    {
        $context = $this->booking('completed');
        Review::create(['booking_id' => $context['booking']->booking_id, 'tourist_id' => $context['tourist']->tourist_id, 'rating' => 5, 'comment' => 'A properly recorded public review.', 'review_date' => today()]);
        $this->assertEquals(5.0, (float) Review::where('booking_id', $context['booking']->booking_id)->avg('rating'));

        $admin = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($admin)->get(route('admin.reviews.index'))->assertOk()->assertSee('Review moderation');
        $this->actingAs($admin)->delete(route('admin.reviews.destroy', Review::first()))->assertRedirect();
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_submitted_review_alerts_active_administrators(): void
    {
        $administrator = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $context = $this->booking('completed');

        $this->actingAs($context['user'])->post(route('tourist.reservations.reviews.store', $context['booking']), ['rating' => 5, 'comment' => 'Excellent guide and a memorable experience.'])->assertRedirect();

        $this->assertDatabaseHas('reviews', ['booking_id' => $context['booking']->booking_id, 'rating' => 5]);
        $this->assertDatabaseHas('notifications', ['user_id' => $administrator->user_id, 'type' => 'review_submitted']);
    }

    private function booking(string $status, string $start = '2026-09-01', string $end = '2026-09-03'): array
    {
        $user = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Test Tourist', 'nationality' => 'Ethiopian']);
        $guideUser = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create(['user_id' => $guideUser->user_id, 'license_number' => fake()->unique()->bothify('LIC###'), 'expertise' => 'History', 'availability_status' => 'available', 'verification_status' => 'verified', 'daily_rate' => 100]);
        $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'guide_id' => $guide->guide_id, 'service_id' => null, 'status' => $status, 'booking_date' => now()]);
        TourGuideReservation::create(['booking_id' => $booking->booking_id, 'start_date' => $start, 'end_date' => $end, 'number_of_tourists' => 1]);

        return compact('user', 'tourist', 'guide', 'booking');
    }
}
