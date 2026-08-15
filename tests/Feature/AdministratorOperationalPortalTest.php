<?php

namespace Tests\Feature;

use App\Models\Administrator;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministratorOperationalPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_active_administrator_can_access_admin_portal(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        foreach (['tourist', 'tour_guide', 'service_provider', 'tourism_bureau_officer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get(route('admin.dashboard'))->assertForbidden();
        }
        $inactive = $this->admin(false);
        $this->actingAs($inactive)->get(route('admin.dashboard'))->assertRedirect();
    }

    public function test_admin_can_approve_only_verified_provider_and_cannot_change_bureau_state(): void
    {
        $admin = $this->admin();
        $eligible = $this->provider('verified', 'pending');
        $unverified = $this->provider('pending', 'pending');

        $this->actingAs($admin)->get(route('admin.providers.index'))->assertOk()->assertSee($eligible->business_name);
        $this->actingAs($admin)->patch(route('admin.providers.status', $eligible), ['status' => 'approved'])->assertRedirect();
        $this->assertDatabaseHas('service_providers', ['provider_id' => $eligible->provider_id, 'verification_status' => 'verified', 'status' => 'approved']);
        $this->actingAs($admin)->patch(route('admin.providers.status', $unverified), ['status' => 'approved'])->assertSessionHasErrors('status');
        $this->assertDatabaseHas('service_providers', ['provider_id' => $unverified->provider_id, 'verification_status' => 'pending', 'status' => 'pending']);
    }

    public function test_admin_can_suspend_and_reinstate_provider_and_actions_are_audited(): void
    {
        $admin = $this->admin();
        $provider = $this->provider('verified', 'approved');

        $this->actingAs($admin)->patch(route('admin.providers.status', $provider), ['status' => 'suspended'])->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.providers.status', $provider), ['status' => 'approved'])->assertRedirect();
        $this->assertDatabaseHas('service_providers', ['provider_id' => $provider->provider_id, 'status' => 'approved']);
        $this->assertDatabaseCount('audit_logs', 2);
    }

    public function test_admin_cannot_deactivate_self_but_can_manage_other_users(): void
    {
        $admin = $this->admin();
        $other = User::factory()->create(['role' => 'tourist']);
        $this->actingAs($admin)->patch(route('admin.users.toggle', $admin))->assertStatus(422);
        $this->actingAs($admin)->patch(route('admin.users.toggle', $other))->assertRedirect();
        $this->assertFalse($other->fresh()->is_active);
    }

    public function test_admin_can_create_subscription_plan_with_valid_commission_configuration(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.subscriptions.store'), ['plan' => 'Professional', 'price' => 500, 'commission_rate' => 7.5, 'duration' => 30])->assertRedirect();
        $this->assertDatabaseHas('subscription_plans', ['plan' => 'Professional', 'commission_rate' => 7.50]);
        $this->actingAs(User::factory()->create(['role' => 'tourist']))->post(route('admin.subscriptions.store'), ['plan' => 'Nope', 'commission_rate' => 5, 'duration' => 30])->assertForbidden();
    }

    private function admin(bool $active = true): User
    {
        $user = User::factory()->create(['role' => 'administrator', 'is_active' => $active]);
        Administrator::create(['user_id' => $user->user_id]);

        return $user;
    }

    private function provider(string $verification, string $status): ServiceProvider
    {
        $provider = ServiceProvider::create(['user_id' => User::factory()->create(['role' => 'service_provider'])->user_id, 'business_name' => uniqid('Provider '), 'provider_type' => 'hotel', 'status' => $status]);
        $provider->forceFill(['verification_status' => $verification])->save();

        return $provider->fresh();
    }
}
