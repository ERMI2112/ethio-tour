<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Destination;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPlan;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderGovernanceV2Test extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_provider_governance_and_commercial_dossier(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Loza Hotel Gondar',
            'provider_type' => 'hotel',
            'status' => 'pending',
            'manager_name' => 'Ato Abnet Kebede',
            'tin_number' => '0084920194',
            'trade_license_number' => 'TRD-GDR-2024-8891',
        ]);
        $plan = SubscriptionPlan::create([
            'plan' => 'Enterprise Luxury',
            'price' => 5000,
            'commission_rate' => 7,
            'duration' => 30,
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.providers.show', $provider));

        $response->assertOk()
            ->assertSee('Loza Hotel Gondar')
            ->assertSee('Ato Abnet Kebede')
            ->assertSee('0084920194')
            ->assertSee('TRD-GDR-2024-8891')
            ->assertSee('Regulatory Status (Bureau)')
            ->assertSee('Platform Status (Admin)')
            ->assertSee('Enterprise Luxury');
    }

    public function test_admin_can_assign_commercial_subscription_plan_to_provider(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Goha Hilltop Lodge',
            'provider_type' => 'hotel',
            'status' => 'pending',
        ]);
        $plan = SubscriptionPlan::create([
            'plan' => 'Pro Partner',
            'price' => 2500,
            'commission_rate' => 8,
            'duration' => 30,
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.providers.subscription', $provider), [
            'plan_id' => $plan->plan_id,
        ]);

        $response->assertRedirect(route('admin.providers.show', $provider));
        $this->assertDatabaseHas('provider_subscriptions', [
            'provider_id' => $provider->provider_id,
            'plan_id' => $plan->plan_id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'provider_subscription_assigned',
            'actor_user_id' => $admin->user_id,
        ]);
    }

    public function test_admin_cannot_activate_provider_before_bureau_verification(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Unverified Resort',
            'provider_type' => 'hotel',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.providers.status', $provider), [
            'status' => 'approved',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame('pending', $provider->fresh()->status);
        $this->assertFalse($provider->fresh()->isOperational());
    }

    public function test_admin_can_activate_provider_after_bureau_verification(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Verified Resort',
            'provider_type' => 'hotel',
            'status' => 'pending',
        ]);
        $provider->forceFill(['verification_status' => 'verified'])->save();

        $response = $this->actingAs($admin)->patch(route('admin.providers.status', $provider), [
            'status' => 'approved',
        ]);

        $response->assertRedirect(route('admin.providers.show', $provider));
        $this->assertSame('approved', $provider->fresh()->status);
        $this->assertTrue($provider->fresh()->isOperational());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'provider_status_changed',
            'actor_user_id' => $admin->user_id,
        ]);
    }

    public function test_admin_suspension_removes_provider_from_public_discovery_while_bureau_verification_remains_intact(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Active Hotel',
            'provider_type' => 'hotel',
            'status' => 'approved',
        ]);
        $provider->forceFill(['verification_status' => 'verified'])->save();

        $officer = TourismBureauOfficer::create(['user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar City', 'location' => 'Gondar', 'description' => 'Test']);
        $category = Category::create(['category_name' => 'Lodging']);
        $service = TourismService::create([
            'provider_id' => $provider->provider_id,
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Royal Suite Room',
            'price' => 1500,
            'description' => 'Luxury suite',
        ]);

        // Verify publicly live
        $this->get(route('tourism-services.show', $service))->assertOk();

        // Admin suspends for policy breach
        $this->actingAs($admin)->patch(route('admin.providers.status', $provider), [
            'status' => 'suspended',
        ])->assertRedirect(route('admin.providers.show', $provider));

        $provider->refresh();
        $this->assertSame('suspended', $provider->status);
        $this->assertSame('verified', $provider->verification_status); // Regulatory status preserved
        $this->assertFalse($provider->isOperational());

        // Verify no longer visible publicly
        $this->get(route('tourism-services.show', $service))->assertNotFound();
    }

    public function test_bureau_officer_cannot_modify_platform_status_or_assign_subscriptions(): void
    {
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Target Provider',
            'provider_type' => 'hotel',
            'status' => 'pending',
        ]);
        $plan = SubscriptionPlan::create(['plan' => 'Standard', 'price' => 500, 'commission_rate' => 5, 'duration' => 30, 'active' => true]);

        // Bureau cannot access admin routes
        $this->actingAs($officerUser)->patch(route('admin.providers.status', $provider), ['status' => 'approved'])->assertForbidden();
        $this->actingAs($officerUser)->post(route('admin.providers.subscription', $provider), ['plan_id' => $plan->plan_id])->assertForbidden();
    }
}
