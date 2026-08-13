<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_navigation_shows_public_auth_actions_only(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Primary navigation', false)
            ->assertSee('Tourism Information')
            ->assertSee('Map')
            ->assertSee('Log in')
            ->assertSee('Register')
            ->assertDontSee('Log out')
            ->assertDontSee('Bookings');
    }

    public function test_authenticated_navigation_shows_shared_and_role_placeholder_actions(): void
    {
        $tourist = User::factory()->create(['role' => 'tourist']);

        $response = $this->actingAs($tourist)->get('/account');

        $response->assertOk()
            ->assertSee('Account')
            ->assertSee('Log out')
            ->assertSee('Bookings')
            ->assertSee('Reports')
            ->assertSee('Tourist Portal')
            ->assertSee('data-nav-placeholder="true"', false)
            ->assertDontSee('Administrator Portal');
    }

    public function test_role_specific_navigation_placeholder_matches_the_authenticated_role(): void
    {
        $roles = [
            'tour_guide' => 'Tour Guide Portal',
            'service_provider' => 'Service Provider Portal',
            'tourism_bureau_officer' => 'Tourism Bureau Portal',
            'administrator' => 'Administrator Portal',
        ];

        foreach ($roles as $role => $label) {
            $response = $this->actingAs(User::factory()->create(['role' => $role]))->get('/account');
            $response->assertOk()->assertSee($label)->assertDontSee('Tourist Portal');
        }
    }

    public function test_shared_flash_message_region_renders_success_messages(): void
    {
        $response = $this->withSession(['success' => 'Saved for a later phase.'])->get('/');

        $response->assertOk()->assertSee('Saved for a later phase.')->assertSee('alert-success', false);
    }
}
