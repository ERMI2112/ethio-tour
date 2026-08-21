<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantWorkspaceUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_restaurant_workspace_has_operational_navigation_and_attention_state(): void
    {
        $restaurant = User::where('email', 'restaurant@test.com')->firstOrFail();

        $this->actingAs($restaurant)->get(route('restaurant.dashboard'))
            ->assertOk()
            ->assertSee('Needs attention')
            ->assertSee('Menu and service offerings')
            ->assertSee('Table inventory')
            ->assertSee('Guest feedback')
            ->assertSee('Notifications')
            ->assertDontSee('View Public Site')
            ->assertDontSee('Explore Ethiopia')
            ->assertDontSee('Things to Do');
    }

    public function test_restaurant_reservation_filters_inventory_and_menu_pages_render(): void
    {
        $restaurant = User::where('email', 'restaurant@test.com')->firstOrFail();

        $this->actingAs($restaurant)->get(route('restaurant.reservations.index', ['status' => 'completed']))
            ->assertOk()->assertSee('Completed');
        $this->actingAs($restaurant)->get(route('restaurant.services.index'))
            ->assertOk()->assertSee('Menu and service offerings');
        $this->actingAs($restaurant)->get(route('restaurant.tables.index'))
            ->assertOk()->assertSee('Table inventory')->assertSee('Reservation history');
    }

    public function test_non_restaurant_roles_cannot_access_restaurant_workspace(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $hotel = User::where('email', 'hotel@test.com')->firstOrFail();

        $this->actingAs($tourist)->get(route('restaurant.dashboard'))->assertForbidden();
        $this->actingAs($hotel)->get(route('restaurant.dashboard'))->assertForbidden();
    }
}
