<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TourGuide;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTourGuideDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_directory_lists_only_verified_active_guides(): void
    {
        $verified = $this->guide('verified@example.com', 'verified');
        $this->guide('pending@example.com', 'pending');
        $inactive = $this->guide('inactive@example.com', 'verified');
        $inactive['user']->update(['is_active' => false]);

        $this->get(route('tour-guides.index'))
            ->assertOk()
            ->assertSee($verified['guide']->license_number)
            ->assertDontSee('pending@example.com')
            ->assertDontSee($inactive['guide']->license_number);
    }

    public function test_verified_guide_detail_is_public_but_unverified_detail_is_hidden(): void
    {
        $verified = $this->guide('guide-detail@example.com', 'verified');
        $pending = $this->guide('guide-pending@example.com', 'pending');

        $this->get(route('tour-guides.show', $verified['guide']))->assertOk()->assertSee($verified['guide']->expertise);
        $this->get(route('tour-guides.show', $pending['guide']))->assertNotFound();
    }

    public function test_tourist_can_submit_one_pending_guide_request_and_history_displays_it(): void
    {
        $guide = $this->guide('bookable@example.com', 'verified');
        $tourist = $this->tourist();

        $response = $this->actingAs($tourist['user'])->post(route('tour-guides.book.store', $guide['guide']), [
            'start_date' => '2026-09-10',
            'end_date' => '2026-09-12',
            'number_of_tourists' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'tourist_id' => $tourist['tourist']->tourist_id,
            'guide_id' => $guide['guide']->guide_id,
            'service_id' => null,
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('tour_guide_reservations', 1);
        $this->actingAs($tourist['user'])->get(route('tourist.reservations.index'))->assertOk()->assertSee('Tour Guide');
    }

    public function test_invalid_dates_conflicts_and_duplicate_requests_are_rejected(): void
    {
        $guide = $this->guide('conflict@example.com', 'verified');
        $tourist = $this->tourist();
        $this->actingAs($tourist['user'])->post(route('tour-guides.book.store', $guide['guide']), [
            'start_date' => '2026-09-10', 'end_date' => '2026-09-12', 'number_of_tourists' => 1,
        ])->assertRedirect();

        $this->actingAs($tourist['user'])->post(route('tour-guides.book.store', $guide['guide']), [
            'start_date' => '2026-09-11', 'end_date' => '2026-09-13', 'number_of_tourists' => 1,
        ])->assertSessionHas('error');
        $this->assertDatabaseCount('bookings', 1);

        $this->actingAs($tourist['user'])->post(route('tour-guides.book.store', $guide['guide']), [
            'start_date' => '2026-09-20', 'end_date' => '2026-09-20', 'number_of_tourists' => 1,
        ])->assertSessionHasErrors('end_date');
    }

    public function test_booking_identity_is_derived_and_tourist_cannot_view_another_tourists_booking(): void
    {
        $guide = $this->guide('identity@example.com', 'verified');
        $first = $this->tourist('first@example.com');
        $second = $this->tourist('second@example.com');

        $this->actingAs($first['user'])->post(route('tour-guides.book.store', $guide['guide']), [
            'start_date' => '2026-10-01', 'end_date' => '2026-10-02', 'number_of_tourists' => 1,
            'tourist_id' => $second['tourist']->tourist_id, 'guide_id' => 999999,
        ])->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertSame($first['tourist']->tourist_id, $booking->tourist_id);
        $this->assertSame($guide['guide']->guide_id, $booking->guide_id);
        $this->actingAs($second['user'])->get(route('tourist.reservations.show', $booking))->assertForbidden();
    }

    public function test_unauthenticated_and_non_tourist_users_cannot_submit_guide_requests(): void
    {
        $guide = $this->guide('auth@example.com', 'verified');
        $this->post(route('tour-guides.book.store', $guide['guide']), [])->assertRedirect(route('login'));

        $user = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($user)->post(route('tour-guides.book.store', $guide['guide']), [
            'start_date' => '2026-09-10', 'end_date' => '2026-09-12', 'number_of_tourists' => 1,
        ])->assertForbidden();
    }

    /** @return array{user: User, guide: TourGuide} */
    private function guide(string $email, string $verification): array
    {
        $user = User::factory()->create(['email' => $email, 'role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $user->user_id,
            'license_number' => strtoupper(substr(md5($email), 0, 10)),
            'expertise' => 'Historical tours',
            'availability_status' => 'available',
        ]);
        $guide->forceFill(['verification_status' => $verification])->save();

        return compact('user', 'guide');
    }

    /** @return array{user: User, tourist: Tourist} */
    private function tourist(string $email = 'tourist@example.com'): array
    {
        $user = User::factory()->create(['email' => $email, 'role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Test Tourist', 'nationality' => 'Ethiopian']);

        return compact('user', 'tourist');
    }
}
