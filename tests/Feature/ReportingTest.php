<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_view_derived_platform_report(): void
    {
        $admin = User::factory()->create(['role' => 'administrator']);
        User::factory()->count(2)->create(['role' => 'tourist']);
        ServiceProvider::create(['user_id' => User::factory()->create(['role' => 'service_provider'])->user_id, 'business_name' => 'Report Hotel', 'provider_type' => 'hotel', 'status' => 'approved']);

        $this->actingAs($admin)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Reports and analytics');
    }

    public function test_bureau_can_view_verification_report_but_not_admin_report(): void
    {
        $bureau = User::factory()->create(['role' => 'tourism_bureau_officer']);

        $this->actingAs($bureau)->get(route('bureau.reports.index'))->assertOk()->assertSee('Bureau Reports');
        $this->actingAs($bureau)->get(route('admin.reports.index'))->assertForbidden();
    }

    public function test_provider_report_is_available_only_to_authenticated_service_providers(): void
    {
        $tourist = User::factory()->create(['role' => 'tourist']);
        $this->actingAs($tourist)->get(route('provider.reports'))->assertForbidden();

        $providerUser = User::factory()->create(['role' => 'service_provider']);
        ServiceProvider::create(['user_id' => $providerUser->user_id, 'business_name' => 'Provider Report', 'provider_type' => 'hotel', 'status' => 'approved']);

        $this->actingAs($providerUser)->get(route('provider.reports'))->assertOk()->assertSee('Provider Reports');
    }

    public function test_report_routes_require_authentication(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
        $this->get(route('bureau.reports.index'))->assertRedirect(route('login'));
        $this->get(route('provider.reports'))->assertRedirect(route('login'));
    }
}
