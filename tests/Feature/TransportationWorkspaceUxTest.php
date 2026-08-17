<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransportationWorkspaceUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_transportation_workspace_has_fleet_attention_and_role_navigation(): void
    {
        $transport = User::where('email', 'transport@test.com')->firstOrFail();

        $this->actingAs($transport)->get(route('transportation.dashboard'))
            ->assertOk()->assertSee('Needs attention')->assertSee('Fleet overview')->assertSee('Vehicles')->assertSee('Notifications')->assertSee('View Public Site')->assertDontSee('Explore Ethiopia');
    }

    public function test_transportation_reservation_filters_and_inventory_pages_render(): void
    {
        $transport = User::where('email', 'transport@test.com')->firstOrFail();

        $this->actingAs($transport)->get(route('transportation.reservations.index', ['status' => 'completed']))
            ->assertOk()->assertSee('Completed')->assertSee('Rental reservations');
        $this->actingAs($transport)->get(route('transportation.services.index'))
            ->assertOk()->assertSee('Transportation services');
        $this->actingAs($transport)->get(route('transportation.vehicles.index'))
            ->assertOk()->assertSee('Vehicle inventory');
    }

    public function test_non_transport_roles_are_denied_from_transportation_workspace(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $hotel = User::where('email', 'hotel@test.com')->firstOrFail();

        $this->actingAs($tourist)->get(route('transportation.dashboard'))->assertForbidden();
        $this->actingAs($hotel)->get(route('transportation.dashboard'))->assertForbidden();
    }
}
