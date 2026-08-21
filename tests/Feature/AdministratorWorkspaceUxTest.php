<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministratorWorkspaceUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_dashboard_explains_attention_and_platform_overview_with_real_queue_count(): void
    {
        $admin = User::where('email', 'admin@test.com')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Attention')
            ->assertSee('Platform overview')
            ->assertSee('Recent activity')
            ->assertSee('Operational insights')
            ->assertSee('Review activation queue')
            ->assertSee('1 provider waiting for activation');
    }

    public function test_admin_workspace_keeps_consumer_navigation_out_of_sidebar_and_omits_fake_settings(): void
    {
        $admin = User::where('email', 'admin@test.com')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertSee('Provider governance')
            ->assertSee('Notifications')
            ->assertDontSee('View Public Site')
            ->assertDontSee('Explore Ethiopia')
            ->assertDontSee('Things to Do')
            ->assertDontSee('Stay &amp; Eat', false)
            ->assertDontSee('href="'.url('/admin/settings').'"', false);
    }

    public function test_provider_governance_exposes_real_activation_queue_and_state_filters(): void
    {
        $admin = User::where('email', 'admin@test.com')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.providers.index', ['verification' => 'verified', 'status' => 'pending']))
            ->assertOk()
            ->assertSee('Activation queue')
            ->assertSee('Habesha Cultural Dining')
            ->assertSee('Bureau verification')
            ->assertSee('Platform status');
    }

    public function test_dashboard_reports_clear_attention_state_when_activation_queue_is_empty(): void
    {
        ServiceProvider::where('verification_status', 'verified')->where('status', 'pending')->update(['status' => 'approved']);
        $admin = User::where('email', 'admin@test.com')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('No provider activation actions are waiting');
    }
}
