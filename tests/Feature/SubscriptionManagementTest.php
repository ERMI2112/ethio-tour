<?php

namespace Tests\Feature;

use App\Models\ProviderSubscription;
use App\Models\ServiceProvider;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_toggle_plan_without_removing_historical_subscriptions(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        $provider = ServiceProvider::create(['user_id' => User::factory()->create(['role' => 'service_provider'])->user_id, 'business_name' => 'Subscribed Provider', 'provider_type' => 'hotel', 'status' => 'approved']);
        $plan = SubscriptionPlan::create(['plan' => 'Pilot', 'price' => 100, 'commission_rate' => 5, 'duration' => 30]);
        $subscription = ProviderSubscription::create(['provider_id' => $provider->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today(), 'end_date' => today()->addDays(30), 'status' => 'active']);

        $this->actingAs($admin)->patch(route('admin.subscriptions.status', $plan), ['active' => 0])->assertRedirect();

        $this->assertDatabaseHas('subscription_plans', ['plan_id' => $plan->plan_id, 'active' => false]);
        $this->assertDatabaseHas('provider_subscriptions', ['provider_subscription_id' => $subscription->provider_subscription_id, 'plan_id' => $plan->plan_id, 'status' => 'active']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'subscription_plan_deactivated', 'subject_id' => $plan->plan_id]);

        $this->actingAs($admin)->patch(route('admin.subscriptions.status', $plan), ['active' => 1])->assertRedirect();
        $this->assertDatabaseHas('subscription_plans', ['plan_id' => $plan->plan_id, 'active' => true]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'subscription_plan_activated', 'subject_id' => $plan->plan_id]);
    }

    public function test_invalid_plan_values_and_duplicate_names_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        SubscriptionPlan::create(['plan' => 'Existing', 'price' => 10, 'commission_rate' => 5, 'duration' => 30]);

        $this->actingAs($admin)->post(route('admin.subscriptions.store'), ['plan' => 'Existing', 'price' => -1, 'commission_rate' => 101, 'duration' => 0])->assertSessionHasErrors(['plan', 'price', 'commission_rate', 'duration']);
    }

    public function test_provider_can_view_only_its_own_subscription_configuration(): void
    {
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $providerUser->user_id, 'business_name' => 'Own Provider', 'provider_type' => 'hotel', 'status' => 'approved']);
        $other = ServiceProvider::create(['user_id' => User::factory()->create(['role' => 'service_provider'])->user_id, 'business_name' => 'Other Provider', 'provider_type' => 'restaurant', 'status' => 'approved']);
        $plan = SubscriptionPlan::create(['plan' => 'Standard', 'price' => 100, 'commission_rate' => 5, 'duration' => 30]);
        ProviderSubscription::create(['provider_id' => $provider->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today(), 'end_date' => today()->addDays(30), 'status' => 'active']);
        ProviderSubscription::create(['provider_id' => $other->provider_id, 'plan_id' => $plan->plan_id, 'start_date' => today(), 'end_date' => today()->addDays(30), 'status' => 'active']);

        $this->actingAs($providerUser)->get(route('provider.status'))->assertOk()->assertSee('Own Provider')->assertSee('Standard')->assertDontSee('Other Provider');
        $this->actingAs($providerUser)->patch(route('admin.subscriptions.status', $plan), ['active' => 0])->assertForbidden();
    }

    public function test_approved_provider_remains_operational_when_subscription_is_unconfigured(): void
    {
        $user = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Operational Provider', 'provider_type' => 'hotel', 'status' => 'approved']);
        $provider->forceFill(['verification_status' => 'verified'])->save();

        $this->assertTrue($provider->fresh()->isOperational());
        $this->actingAs($user)->get(route('provider.status'))->assertOk()->assertSee('Billing will be available after payment integration');
    }
}
