<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Notification;
use App\Models\ProviderSubscription;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPlan;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_database_foundation_preserves_approved_relationships(): void
    {
        $touristUser = User::factory()->create();
        $tourist = Tourist::create(['user_id' => $touristUser->user_id, 'full_name' => 'Test Tourist', 'nationality' => 'Ethiopian']);

        $guide = TourGuide::create([
            'user_id' => User::factory()->create(['role' => 'tour_guide'])->user_id,
            'license_number' => 'GUIDE-001',
            'expertise' => 'Historical tours',
            'availability_status' => 'available',
        ]);

        $officer = TourismBureauOfficer::create(['user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id]);
        $provider = ServiceProvider::create([
            'user_id' => User::factory()->create(['role' => 'service_provider'])->user_id,
            'business_name' => 'Test Hotel',
            'provider_type' => 'hotel',
            'status' => 'approved',
        ]);
        $category = Category::create(['category_name' => 'Room']);
        $service = TourismService::create([
            'provider_id' => $provider->provider_id,
            'category_id' => $category->category_id,
            'destination_id' => Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar', 'location' => 'Gondar', 'description' => 'Pilot destination'])->destination_id,
            'service_name' => 'Standard Room',
            'price' => 1000,
            'description' => 'Test service',
        ]);

        $serviceBooking = Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $service->service_id]);
        $guideBooking = Booking::create(['tourist_id' => $tourist->tourist_id, 'guide_id' => $guide->guide_id]);

        $this->assertNull($serviceBooking->guide_id);
        $this->assertNull($guideBooking->service_id);
        $this->assertTrue($serviceBooking->tourismService->is($service));
        $this->assertTrue($guideBooking->tourGuide->is($guide));

    }

    public function test_a_booking_can_have_only_one_review(): void
    {
        $tourist = Tourist::create([
            'user_id' => User::factory()->create()->user_id,
            'full_name' => 'Test Tourist',
            'nationality' => 'Ethiopian',
        ]);
        $guide = TourGuide::create([
            'user_id' => User::factory()->create(['role' => 'tour_guide'])->user_id,
            'license_number' => 'GUIDE-002',
            'expertise' => 'Historical tours',
            'availability_status' => 'available',
        ]);
        $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'guide_id' => $guide->guide_id]);

        Review::create(['booking_id' => $booking->booking_id, 'tourist_id' => $tourist->tourist_id, 'rating' => 5, 'comment' => 'Excellent', 'review_date' => today()]);
        $this->assertNotNull($booking->fresh()->review);

        $this->expectException(QueryException::class);
        Review::create(['booking_id' => $booking->booking_id, 'tourist_id' => $tourist->tourist_id, 'rating' => 4, 'comment' => 'Duplicate', 'review_date' => today()]);
    }

    public function test_subscription_and_notification_relationships_are_persisted(): void
    {
        $provider = ServiceProvider::create([
            'user_id' => User::factory()->create(['role' => 'service_provider'])->user_id,
            'business_name' => 'Test Hotel',
            'provider_type' => 'hotel',
            'status' => 'approved',
        ]);

        $plan = SubscriptionPlan::create(['plan' => 'Starter', 'price' => 100, 'commission_rate' => 5, 'duration' => 30]);
        $subscription = ProviderSubscription::create(['provider_id' => $provider->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today(), 'end_date' => today()->addDays(30), 'status' => 'active']);
        $this->assertTrue($subscription->serviceProvider->is($provider));
        $this->assertTrue($subscription->subscriptionPlan->is($plan));

        $user = User::factory()->create();
        $notification = Notification::create(['user_id' => $user->user_id, 'title' => 'Test', 'message' => 'Database foundation check', 'channel' => 'in_app']);
        $this->assertTrue($notification->user->is($user));
    }
}
