<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourGuideWorkspaceUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_guide_workspace_has_attention_state_and_role_navigation(): void
    {
        $guide = User::where('email', 'guide@test.com')->firstOrFail();

        $this->actingAs($guide)->get(route('tour-guide.dashboard'))
            ->assertOk()->assertSee('Needs attention')->assertSee('Profile summary')->assertSee('Availability')->assertSee('Reports')->assertSee('Notifications')->assertDontSee('View Public Site')->assertDontSee('Explore Ethiopia');
    }

    public function test_guide_requests_availability_and_profile_workflows_render(): void
    {
        $guide = User::where('email', 'guide@test.com')->firstOrFail();

        $this->actingAs($guide)->get(route('tour-guide.requests.index', ['status' => 'completed']))
            ->assertOk()->assertSee('Booking Requests')->assertSee('Apply filter');
        $this->actingAs($guide)->get(route('tour-guide.availability'))
            ->assertOk()->assertSee('Availability');
        $this->actingAs($guide)->get(route('tour-guide.profile'))
            ->assertOk()->assertSee('Verification status')->assertSee('Daily Guide Rate');
    }

    public function test_non_guide_roles_are_denied_from_guide_workspace(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $hotel = User::where('email', 'hotel@test.com')->firstOrFail();

        $this->actingAs($tourist)->get(route('tour-guide.dashboard'))->assertForbidden();
        $this->actingAs($hotel)->get(route('tour-guide.dashboard'))->assertForbidden();
    }
}
