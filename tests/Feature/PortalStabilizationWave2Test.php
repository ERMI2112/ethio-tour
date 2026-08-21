<?php

namespace Tests\Feature;

use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\MuseumInformation;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPlan;
use App\Models\TourGuide;
use App\Models\TourismService;
use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalStabilizationWave2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_bureau_uat_pages_and_decision_targets_render(): void
    {
        $bureau = User::where('email', 'bureau@test.com')->firstOrFail();
        $guide = TourGuide::where('verification_status', 'pending')->firstOrFail();
        $provider = ServiceProvider::where('verification_status', 'pending')->firstOrFail();
        $museum = MuseumInformation::firstOrFail();

        foreach ([
            route('bureau.dashboard'),
            route('bureau.guides.index'),
            route('bureau.guides.show', $guide),
            route('bureau.providers.index'),
            route('bureau.providers.show', $provider),
            route('bureau.museums.index'),
            route('bureau.museums.create'),
            route('bureau.museums.edit', $museum),
            route('bureau.reports.index'),
        ] as $url) {
            $this->actingAs($bureau)->get($url)->assertOk();
        }

        $this->actingAs($bureau)->patch(route('bureau.guides.decide', $guide), [
            'decision' => 'approve',
        ])->assertRedirect();
        $this->assertDatabaseHas('tour_guides', [
            'guide_id' => $guide->guide_id,
            'verification_status' => 'verified',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $guide->user_id,
            'read_status' => false,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $bureau->user_id,
            'action' => 'guide_verification_decided',
            'subject_id' => $guide->guide_id,
        ]);

        $this->actingAs($bureau)->patch(route('bureau.providers.decide', $provider), [
            'decision' => 'approve',
        ])->assertRedirect();
        $this->assertDatabaseHas('service_providers', [
            'provider_id' => $provider->provider_id,
            'verification_status' => 'verified',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $provider->user_id,
            'read_status' => false,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => $bureau->user_id,
            'action' => 'provider_verification_decided',
            'subject_id' => $provider->provider_id,
        ]);
    }

    public function test_administrator_uat_pages_and_provider_governance_render(): void
    {
        $admin = User::where('email', 'admin@test.com')->firstOrFail();
        $provider = ServiceProvider::where('verification_status', 'verified')->where('status', 'pending')->firstOrFail();

        foreach ([
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.providers.index'),
            route('admin.providers.show', $provider),
            route('admin.subscriptions.index'),
            route('admin.audit.index'),
            route('admin.reviews.index'),
            route('admin.reports.index'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        $this->actingAs($admin)->patch(route('admin.providers.status', $provider), [
            'status' => 'approved',
        ])->assertRedirect();
        $this->assertDatabaseHas('service_providers', [
            'provider_id' => $provider->provider_id,
            'verification_status' => 'verified',
            'status' => 'approved',
        ]);

        $plan = SubscriptionPlan::create([
            'plan' => 'UAT Wave 2 Plan',
            'price' => 100,
            'commission_rate' => 5,
            'duration' => 30,
        ]);
        $this->actingAs($admin)->put(route('admin.subscriptions.update', $plan), [
            'plan' => 'UAT Wave 2 Updated Plan',
            'price' => 125,
            'commission_rate' => 6.5,
            'duration' => 45,
        ])->assertRedirect();
        $this->assertDatabaseHas('subscription_plans', [
            'plan_id' => $plan->plan_id,
            'plan' => 'UAT Wave 2 Updated Plan',
            'duration' => 45,
        ]);
    }

    public function test_tourist_public_discovery_and_portal_pages_render_with_real_uat_records(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $destination = Destination::where('name', 'Gondar')->firstOrFail();
        $service = TourismService::where('service_name', 'Standard Heritage View Room')->firstOrFail();
        $event = CulturalEvent::where('event_name', 'Timkat Gondar Epiphany & Cultural Festival')->firstOrFail();

        foreach ([
            route('home'),
            route('destinations.index'),
            route('destinations.show', $destination),
            route('heritage-sites.index'),
            route('museums.index'),
            route('tourism-services.index'),
            route('tourism-services.show', $service),
            route('tour-guides.index'),
            route('transportation.index'),
            route('events.index'),
            route('events.show', $event),
            route('search'),
            route('map'),
            route('smart-trip.index'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        foreach ([
            route('tourist.reservations.index'),
            route('notifications.index'),
        ] as $url) {
            $this->actingAs($tourist)->get($url)->assertOk();
        }
    }
}
