<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\HeritageSite;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\ProviderSubscription;
use App\Models\Review;
use App\Models\SubscriptionPlan;
use App\Models\TourPackage;
use App\Models\User;
use App\Services\ReviewEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_builds_the_complete_demo_environment(): void
    {
        $this->seed();

        // Required demo users for every important role.
        foreach (['tourist@test.com', 'admin@test.com', 'bureau@test.com', 'guide@test.com', 'hotel@test.com', 'restaurant@test.com', 'transport@test.com', 'event@test.com'] as $email) {
            $this->assertDatabaseHas('users', ['email' => $email, 'is_active' => true]);
        }

        // Heritage content for the public catalog.
        $this->assertGreaterThanOrEqual(5, HeritageSite::count());
        $this->assertDatabaseHas('heritage_sites', ['heritage_type' => 'Fasil Ghebbi Royal Enclosure (UNESCO World Heritage Site)']);

        // Subscription plans + an active provider subscription.
        $this->assertGreaterThanOrEqual(3, SubscriptionPlan::where('active', true)->count());
        $this->assertDatabaseHas('subscription_plans', ['plan' => 'Growth', 'commission_rate' => 7.50]);
        $this->assertSame(1, ProviderSubscription::where('status', 'active')->count());

        // Key tourism content.
        $this->assertDatabaseHas('destinations', ['name' => 'Gondar']);
        $this->assertDatabaseHas('tourism_services', ['service_name' => 'Standard Heritage View Room']);
        $this->assertDatabaseHas('tour_guides', ['license_number' => 'TG-GDR-001', 'verification_status' => 'verified', 'admin_approval_status' => 'approved']);
        $this->assertGreaterThanOrEqual(2, TourPackage::where('is_active', true)->count());

        // At least one completed booking with a safe demo payment.
        $completed = Booking::where('status', 'completed')->count();
        $this->assertGreaterThanOrEqual(4, $completed);
        $this->assertDatabaseHas('payments', ['gateway_reference' => 'DEMO-SEED-HOTEL-001', 'status' => 'success', 'payment_method' => 'chapa']);

        // No real-looking references and no payment for the pending booking.
        $pending = Booking::where('status', 'pending')->firstOrFail();
        $this->assertNull($pending->payment);

        // At least one valid review attached to a completed booking, and it
        // satisfies the real eligibility rules.
        $review = Review::query()->with('booking')->firstOrFail();
        $this->assertSame('completed', $review->booking->status);
        $this->assertFalse(app(ReviewEligibilityService::class)->isEligible($review->booking));

        $reviewable = Booking::where('status', 'completed')->whereDoesntHave('review')->first();
        if ($reviewable) {
            $this->assertTrue(app(ReviewEligibilityService::class)->isEligible($reviewable));
        }

        // In-app notifications for the demo tourist.
        $touristUser = User::where('email', 'tourist@test.com')->firstOrFail();
        $this->assertGreaterThanOrEqual(3, Notification::where('user_id', $touristUser->user_id)->where('channel', 'in_app')->count());
    }

    public function test_consolidated_seeder_is_idempotent(): void
    {
        $this->seed();

        $snapshot = [
            'users' => User::count(),
            'heritage_sites' => HeritageSite::count(),
            'subscription_plans' => SubscriptionPlan::count(),
            'bookings' => Booking::count(),
            'payments' => Payment::count(),
            'reviews' => Review::count(),
            'notifications' => Notification::count(),
            'provider_subscriptions' => ProviderSubscription::count(),
        ];

        // Second full run of the complete chain.
        $this->seed();

        foreach ($snapshot as $table => $count) {
            $model = match ($table) {
                'users' => User::class,
                'heritage_sites' => HeritageSite::class,
                'subscription_plans' => SubscriptionPlan::class,
                'bookings' => Booking::class,
                'payments' => Payment::class,
                'reviews' => Review::class,
                'notifications' => Notification::class,
                'provider_subscriptions' => ProviderSubscription::class,
            };

            $this->assertSame($count, $model::count(), "Seeder re-run duplicated rows in {$table}.");
        }
    }
}
