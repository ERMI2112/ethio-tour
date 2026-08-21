<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NinePortalsSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_workspace_directory_is_not_exposed_to_public_visitors(): void
    {
        $this->get(route('portals.index'))
            ->assertRedirect(route('home'))
            ->assertDontSee('nine portals');
    }

    public function test_signed_in_operator_is_sent_to_their_workspace_instead(): void
    {
        $this->seed(UatDemoSeeder::class);
        $hotel = User::where('email', 'hotel@test.com')->firstOrFail();

        $this->actingAs($hotel)->get(route('portals.index'))
            ->assertRedirect(route('hotel.dashboard'));
    }
}
