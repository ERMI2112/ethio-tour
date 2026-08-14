<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TourGuidePortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_tour_guide_can_access_dashboard_with_derived_statistics(): void
    {
        $context = $this->guideContext();
        $tourist = $this->tourist();

        $pending = $this->guideBooking($tourist, $context['guide'], 'pending');
        $this->guideBooking($tourist, $context['guide'], 'confirmed');
        $completed = $this->guideBooking($tourist, $context['guide'], 'completed');
        Review::create([
            'booking_id' => $completed->booking_id,
            'tourist_id' => $tourist->tourist_id,
            'rating' => 4,
            'comment' => 'Excellent guide',
            'review_date' => today(),
        ]);

        $this->actingAs($context['user'])->get(route('tour-guide.dashboard'))
            ->assertOk()
            ->assertSee('Tour Guide Portal')
            ->assertSee($context['user']->email)
            ->assertSee('Pending guide requests')
            ->assertSee('Average rating')
            ->assertSee('4.0')
            ->assertViewHas('stats', fn (array $stats) => $stats['pendingRequests'] === 1
                && $stats['activeBookings'] === 1
                && $stats['completedBookings'] === 1
                && (float) $stats['averageRating'] === 4.0);

        $this->assertSame('pending', $pending->status);
    }

    public function test_unauthenticated_user_is_redirected_from_tour_guide_portal(): void
    {
        $this->get(route('tour-guide.dashboard'))->assertRedirect(route('login'));
    }

    #[DataProvider('nonGuideRoles')]
    public function test_non_guide_roles_cannot_access_tour_guide_portal(string $role, ?string $providerType = null): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => $role]);

        if ($providerType) {
            ServiceProvider::create([
                'user_id' => $user->user_id,
                'business_name' => ucfirst($providerType).' Provider',
                'provider_type' => $providerType,
                'status' => 'approved',
            ]);
        }

        $this->actingAs($user)->get(route('tour-guide.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('tour-guide.profile'))->assertForbidden();
    }

    public static function nonGuideRoles(): array
    {
        return [
            'tourist' => ['tourist'],
            'hotel provider' => ['service_provider', 'hotel'],
            'restaurant provider' => ['service_provider', 'restaurant'],
            'transportation provider' => ['service_provider', 'transportation_car_rental'],
            'event organizer' => ['service_provider', 'event_organizer'],
            'bureau officer' => ['tourism_bureau_officer'],
            'administrator' => ['administrator'],
        ];
    }

    public function test_guide_can_view_and_update_only_their_allowed_profile_fields(): void
    {
        $context = $this->guideContext();

        $this->actingAs($context['user'])->get(route('tour-guide.profile'))
            ->assertOk()
            ->assertSee($context['guide']->license_number)
            ->assertSee('Platform controlled');

        $this->actingAs($context['user'])->put(route('tour-guide.profile.update'), [
            'expertise' => 'Cultural heritage and city walking tours',
            'availability_status' => 'available',
            'role' => 'administrator',
            'license_number' => 'REPLACED-LICENSE',
            'verification_status' => 'verified',
        ])->assertRedirect(route('tour-guide.profile'));

        $this->assertDatabaseHas('tour_guides', [
            'guide_id' => $context['guide']->guide_id,
            'expertise' => 'Cultural heritage and city walking tours',
            'availability_status' => 'available',
            'license_number' => $context['guide']->license_number,
        ]);
        $this->assertDatabaseHas('users', ['user_id' => $context['user']->user_id, 'role' => 'tour_guide']);
    }

    public function test_guide_cannot_access_or_update_another_guides_private_profile(): void
    {
        $first = $this->guideContext('first-guide@example.com', 'GUIDE-FIRST');
        $second = $this->guideContext('second-guide@example.com', 'GUIDE-SECOND');

        $this->actingAs($first['user'])->get(route('tour-guide.profile'))
            ->assertOk()
            ->assertSee('GUIDE-FIRST')
            ->assertDontSee('GUIDE-SECOND');

        $this->actingAs($first['user'])->put(route('tour-guide.profile.update'), [
            'expertise' => 'Updated first guide expertise',
            'availability_status' => 'available',
            'guide_id' => $second['guide']->guide_id,
        ])->assertRedirect(route('tour-guide.profile'));

        $this->assertDatabaseHas('tour_guides', ['guide_id' => $first['guide']->guide_id, 'expertise' => 'Updated first guide expertise']);
        $this->assertDatabaseHas('tour_guides', ['guide_id' => $second['guide']->guide_id, 'expertise' => 'Historical tours']);
    }

    public function test_profile_validation_and_navigation_are_limited_to_tg_one_features(): void
    {
        $context = $this->guideContext();

        $this->actingAs($context['user'])->put(route('tour-guide.profile.update'), [
            'expertise' => '',
            'availability_status' => 'scheduled',
        ])->assertSessionHasErrors(['expertise', 'availability_status']);

        $this->actingAs($context['user'])->get(route('tour-guide.dashboard'))
            ->assertSee(route('tour-guide.dashboard'))
            ->assertSee(route('tour-guide.profile'))
            ->assertSee(route('tour-guide.availability'))
            ->assertSee(route('tour-guide.requests.index'))
            ->assertSee('data-tour-guide-coming-soon="true"', false)
            ->assertSee('Earnings')
            ->assertDontSee('Hotel Dashboard');
    }

    /**
     * @return array{user: User, guide: TourGuide}
     */
    private function guideContext(string $email = 'guide@example.com', string $licenseNumber = 'GUIDE-TEST'): array
    {
        /** @var User $user */
        $user = User::factory()->create(['email' => $email, 'role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $user->user_id,
            'license_number' => $licenseNumber,
            'expertise' => 'Historical tours',
            'availability_status' => 'unavailable',
        ]);

        return compact('user', 'guide');
    }

    private function tourist(): Tourist
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'tourist']);

        return Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Test Tourist', 'nationality' => 'Ethiopian']);
    }

    private function guideBooking(Tourist $tourist, TourGuide $guide, string $status): Booking
    {
        return Booking::create([
            'tourist_id' => $tourist->tourist_id,
            'guide_id' => $guide->guide_id,
            'status' => $status,
            'booking_date' => now(),
        ]);
    }
}
