<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TourGuide;
use App\Models\Tourist;
use App\Models\User;
use App\Services\TourGuideAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TourGuideAvailabilityAndRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_can_access_availability_and_sees_only_own_booking_requests(): void
    {
        $first = $this->guideContext('first-guide@example.com');
        $second = $this->guideContext('second-guide@example.com');
        $firstBooking = $this->guideBooking($first['guide'], 'pending', 'First Tourist', '2026-10-10', '2026-10-12');
        $secondBooking = $this->guideBooking($second['guide'], 'pending', 'Second Tourist', '2026-10-13', '2026-10-15');

        $this->actingAs($first['user'])->get(route('tour-guide.availability'))
            ->assertOk()
            ->assertSee('Pending requests')
            ->assertSee('First Tourist')
            ->assertDontSee('Second Tourist');

        $this->actingAs($first['user'])->get(route('tour-guide.requests.index'))
            ->assertOk()
            ->assertSee('First Tourist')
            ->assertDontSee('Second Tourist');

        $this->actingAs($first['user'])->get(route('tour-guide.requests.show', $firstBooking))->assertOk();
        $this->actingAs($first['user'])->get(route('tour-guide.requests.show', $secondBooking))->assertForbidden();
    }

    public function test_non_guide_and_inactive_guide_cannot_access_protected_guide_functions(): void
    {
        /** @var User $touristUser */
        $touristUser = User::factory()->create(['role' => 'tourist']);
        $this->actingAs($touristUser)->get(route('tour-guide.availability'))->assertForbidden();

        $inactive = $this->guideContext('inactive-guide@example.com');
        $inactive['user']->update(['is_active' => false]);
        $this->actingAs($inactive['user'])->get(route('tour-guide.requests.index'))->assertRedirect(route('login'));
    }

    public function test_verified_available_guide_can_accept_pending_request_and_unverified_guide_cannot(): void
    {
        $verified = $this->guideContext('verified-guide@example.com', 'verified');
        $verifiedBooking = $this->guideBooking($verified['guide'], 'pending', 'Verified Tourist', '2026-10-10', '2026-10-12');

        $this->actingAs($verified['user'])->patch(route('tour-guide.requests.accept', $verifiedBooking))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('bookings', ['booking_id' => $verifiedBooking->booking_id, 'status' => 'accepted']);

        $unverified = $this->guideContext('pending-guide@example.com', 'pending');
        $unverifiedBooking = $this->guideBooking($unverified['guide'], 'pending', 'Pending Tourist', '2026-10-14', '2026-10-16');

        $this->actingAs($unverified['user'])->patch(route('tour-guide.requests.accept', $unverifiedBooking))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('bookings', ['booking_id' => $unverifiedBooking->booking_id, 'status' => 'pending']);
    }

    public function test_pending_request_can_be_rejected_but_non_pending_requests_cannot_be_changed(): void
    {
        $context = $this->guideContext('decision-guide@example.com', 'verified');
        $pending = $this->guideBooking($context['guide'], 'pending', 'Reject Tourist', '2026-10-10', '2026-10-12');

        $this->actingAs($context['user'])->patch(route('tour-guide.requests.reject', $pending))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('bookings', ['booking_id' => $pending->booking_id, 'status' => 'rejected']);

        $accepted = $this->guideBooking($context['guide'], 'accepted', 'Locked Tourist', '2026-10-14', '2026-10-16');
        $this->actingAs($context['user'])->patch(route('tour-guide.requests.accept', $accepted))->assertSessionHas('error');
        $this->actingAs($context['user'])->patch(route('tour-guide.requests.reject', $accepted))->assertSessionHas('error');
        $this->assertDatabaseHas('bookings', ['booking_id' => $accepted->booking_id, 'status' => 'accepted']);
    }

    public function test_half_open_date_conflicts_and_non_blocking_statuses_are_calculated_correctly(): void
    {
        $context = $this->guideContext('availability-guide@example.com', 'verified');
        $service = app(TourGuideAvailabilityService::class);

        $this->guideBooking($context['guide'], 'accepted', 'Reserved Tourist', '2026-10-10', '2026-10-12');
        $this->assertFalse($service->isGuideAvailable($context['guide'], '2026-10-11', '2026-10-13'));
        $this->assertTrue($service->isGuideAvailable($context['guide'], '2026-10-12', '2026-10-14'));

        $this->guideBooking($context['guide'], 'pending', 'Pending Tourist', '2026-10-14', '2026-10-16');
        $this->guideBooking($context['guide'], 'rejected', 'Rejected Tourist', '2026-10-16', '2026-10-18');
        $this->guideBooking($context['guide'], 'cancelled', 'Cancelled Tourist', '2026-10-18', '2026-10-20');
        $this->guideBooking($context['guide'], 'completed', 'Completed Tourist', '2026-10-20', '2026-10-22');

        $this->assertTrue($service->isGuideAvailable($context['guide'], '2026-10-14', '2026-10-22'));
    }

    #[DataProvider('inventoryReservingStatuses')]
    public function test_inventory_reserving_statuses_block_guide_availability(string $status): void
    {
        $context = $this->guideContext("{$status}-guide@example.com", 'verified');
        $this->guideBooking($context['guide'], $status, 'Blocking Tourist', '2026-10-10', '2026-10-12');

        $this->assertFalse(app(TourGuideAvailabilityService::class)->isGuideAvailable($context['guide'], '2026-10-11', '2026-10-13'));
    }

    public static function inventoryReservingStatuses(): array
    {
        return [['accepted'], ['payment_pending'], ['confirmed']];
    }

    public function test_competing_overlapping_acceptance_is_prevented_and_guide_id_input_cannot_bypass_ownership(): void
    {
        $first = $this->guideContext('concurrent-guide@example.com', 'verified');
        $second = $this->guideContext('other-guide@example.com', 'verified');
        $firstRequest = $this->guideBooking($first['guide'], 'pending', 'First Request', '2026-10-10', '2026-10-12');
        $secondRequest = $this->guideBooking($first['guide'], 'pending', 'Second Request', '2026-10-11', '2026-10-13');

        $this->actingAs($first['user'])->patch(route('tour-guide.requests.accept', $firstRequest))->assertSessionHas('success');
        $this->actingAs($first['user'])->patch(route('tour-guide.requests.accept', $secondRequest))->assertSessionHas('error');
        $this->assertDatabaseHas('bookings', ['booking_id' => $secondRequest->booking_id, 'status' => 'pending']);

        $this->actingAs($second['user'])->patch(route('tour-guide.requests.accept', $firstRequest), ['guide_id' => $second['guide']->guide_id])
            ->assertForbidden();
        $this->assertDatabaseHas('bookings', ['booking_id' => $firstRequest->booking_id, 'status' => 'accepted']);
    }

    /**
     * @return array{user: User, guide: TourGuide}
     */
    private function guideContext(string $email, string $verificationStatus = 'verified'): array
    {
        /** @var User $user */
        $user = User::factory()->create(['email' => $email, 'role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $user->user_id,
            'license_number' => 'GUIDE-'.uniqid(),
            'expertise' => 'Historical tours',
            'availability_status' => 'available',
        ]);
        $guide->forceFill([
            'verification_status' => $verificationStatus,
            'admin_approval_status' => $verificationStatus === 'verified' ? 'approved' : 'pending',
        ])->save();

        return compact('user', 'guide');
    }

    private function guideBooking(TourGuide $guide, string $status, string $touristName, string $startDate, string $endDate): Booking
    {
        /** @var User $touristUser */
        $touristUser = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $touristUser->user_id, 'full_name' => $touristName, 'nationality' => 'Ethiopian']);
        $booking = Booking::create([
            'tourist_id' => $tourist->tourist_id,
            'guide_id' => $guide->guide_id,
            'status' => $status,
            'booking_date' => now(),
        ]);
        $booking->tourGuideReservation()->create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'number_of_tourists' => 2,
        ]);

        return $booking;
    }
}
