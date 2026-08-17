<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BureauWorkspaceUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_bureau_workspace_prioritizes_regulatory_queues_and_uses_real_counts(): void
    {
        $bureau = User::where('email', 'bureau@test.com')->firstOrFail();

        $this->actingAs($bureau)->get(route('bureau.dashboard'))
            ->assertOk()
            ->assertSee('Needs attention')
            ->assertSee('Verification summary')
            ->assertSee('Recent decisions')
            ->assertSee('Museum / tourism operations')
            ->assertSee('1 guide awaiting verification')
            ->assertSee('1 provider awaiting verification')
            ->assertSee('Notifications')
            ->assertSee('View Public Site')
            ->assertDontSee('Explore Ethiopia')
            ->assertDontSee('Things to Do');
    }

    public function test_guide_and_provider_queues_support_real_search_and_status_filters(): void
    {
        $bureau = User::where('email', 'bureau@test.com')->firstOrFail();

        $this->actingAs($bureau)->get(route('bureau.guides.index', ['status' => 'pending', 'q' => 'UAT-GUIDE-PENDING']))
            ->assertOk()->assertSee('UAT-GUIDE-PENDING')->assertSee('Review');
        $this->actingAs($bureau)->get(route('bureau.providers.index', ['status' => 'pending', 'q' => 'UAT Pending Provider']))
            ->assertOk()->assertSee('UAT Pending Provider')->assertSee('Review');
    }

    public function test_bureau_review_pages_keep_governance_boundaries_visible(): void
    {
        $bureau = User::where('email', 'bureau@test.com')->firstOrFail();
        $guide = TourGuide::where('verification_status', 'pending')->firstOrFail();
        $provider = ServiceProvider::where('verification_status', 'pending')->firstOrFail();

        $this->actingAs($bureau)->get(route('bureau.guides.show', $guide))
            ->assertOk()->assertSee('Guide identity')->assertSee('Decision notes');
        $this->actingAs($bureau)->get(route('bureau.providers.show', $provider))
            ->assertOk()->assertSee('Bureau verification')->assertSee('Platform activation');
    }

    public function test_bureau_cannot_access_operational_admin_or_consumer_roles(): void
    {
        $this->actingAs(User::where('email', 'tourist@test.com')->firstOrFail())->get(route('bureau.dashboard'))->assertForbidden();
        $this->actingAs(User::where('email', 'admin@test.com')->firstOrFail())->get(route('bureau.dashboard'))->assertForbidden();
    }
}
