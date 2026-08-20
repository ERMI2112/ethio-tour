<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalWorkspaceLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_operational_roles_receive_workspace_shells_without_public_primary_navigation(): void
    {
        $cases = [
            ['email' => 'admin@test.com', 'route' => 'admin.dashboard', 'label' => 'Admin Dashboard', 'link' => 'Provider governance'],
            ['email' => 'bureau@test.com', 'route' => 'bureau.dashboard', 'label' => 'Bureau Dashboard', 'link' => 'Guide verification'],
            ['email' => 'hotel@test.com', 'route' => 'hotel.dashboard', 'label' => 'Provider Workspace', 'link' => 'Room types'],
            ['email' => 'guide@test.com', 'route' => 'tour-guide.dashboard', 'label' => 'Tour Guide Portal', 'link' => 'Availability'],
            ['email' => 'tourist@test.com', 'route' => 'tourist.reservations.index', 'label' => 'Traveler Workspace', 'link' => 'My Bookings'],
        ];

        foreach ($cases as $case) {
            $user = User::where('email', $case['email'])->firstOrFail();
            $response = $this->actingAs($user)->get(route($case['route']));

            $response->assertOk()
                ->assertSee($case['label'])
                ->assertSee($case['link'])
                ->assertDontSee('Primary navigation', false)
                ->assertDontSee('Explore Ethiopia');

            if ($case['email'] === 'tourist@test.com') {
                $response->assertSee('View Public Site');
            } else {
                $response->assertDontSee('View Public Site');
            }

            if ($case['email'] === 'guide@test.com') {
                $response->assertSee('Tour Guide Portal');
            } else {
                $response->assertSee('workspace-sidebar', false)
                    ->assertSee('offcanvas-lg', false);
            }
        }
    }

    public function test_pending_provider_workspace_exposes_onboarding_actions_only(): void
    {
        $provider = User::where('email', 'uat-provider-pending@test.com')->firstOrFail();

        $this->actingAs($provider)->get(route('provider.status'))
            ->assertOk()
            ->assertSee('Application Status')
            ->assertSee('Business profile')
            ->assertDontSee('Hotel Dashboard')
            ->assertDontSee('Reservations');
    }

    public function test_guest_still_receives_public_navigation(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Primary navigation', false)
            ->assertSee('Explore Ethiopia')
            ->assertSee('Things to Do');
    }

    public function test_every_authenticated_workspace_exposes_the_shared_theme_control(): void
    {
        $workspaces = [
            ['email' => 'admin@test.com', 'route' => 'admin.dashboard'],
            ['email' => 'bureau@test.com', 'route' => 'bureau.dashboard'],
            ['email' => 'guide@test.com', 'route' => 'tour-guide.dashboard'],
            ['email' => 'hotel@test.com', 'route' => 'hotel.dashboard'],
            ['email' => 'restaurant@test.com', 'route' => 'restaurant.dashboard'],
            ['email' => 'transport@test.com', 'route' => 'transportation.dashboard'],
            ['email' => 'event@test.com', 'route' => 'event-organizer.dashboard'],
            ['email' => 'tourist@test.com', 'route' => 'tourist.dashboard'],
        ];

        foreach ($workspaces as $workspace) {
            $user = User::where('email', $workspace['email'])->firstOrFail();

            $this->actingAs($user)->get(route($workspace['route']))
                ->assertOk()
                ->assertSee('data-theme-toggle', false)
                ->assertSee('Switch to dark mode', false)
                ->assertSee('ethio_tour_theme', false);
        }
    }
}
