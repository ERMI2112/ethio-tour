<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelWorkspaceUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_hotel_workspace_has_operational_navigation_and_real_attention_state(): void
    {
        $hotel = User::where('email', 'hotel@test.com')->firstOrFail();

        $this->actingAs($hotel)->get(route('hotel.dashboard'))
            ->assertOk()
            ->assertSee('Needs attention')
            ->assertSee('Room-type services')
            ->assertSee('Physical rooms')
            ->assertSee('Reservations')
            ->assertSee('Guest feedback')
            ->assertSee('Notifications')
            ->assertDontSee('View Public Site')
            ->assertDontSee('Explore Ethiopia')
            ->assertDontSee('Things to Do');
    }

    public function test_hotel_reservation_filters_and_detail_workflow_are_visible(): void
    {
        $hotel = User::where('email', 'hotel@test.com')->firstOrFail();

        $this->actingAs($hotel)->get(route('hotel.reservations.index', ['status' => 'completed']))
            ->assertOk()->assertSee('Completed')->assertSee('Reservation status guide');
        $this->actingAs($hotel)->get(route('hotel.services.index'))
            ->assertOk()->assertSee('Room-type services')->assertSee('Standard Heritage View Room');
        $this->actingAs($hotel)->get(route('hotel.rooms.index'))
            ->assertOk()->assertSee('Physical rooms')->assertSee('Reservation history');
    }

    public function test_hotel_profile_and_non_hotel_roles_remain_protected(): void
    {
        $hotel = User::where('email', 'hotel@test.com')->firstOrFail();
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();

        $this->actingAs($hotel)->get(route('hotel.profile'))->assertOk()->assertSee('Verification status')->assertSee('Account state');
        $this->actingAs($tourist)->get(route('hotel.dashboard'))->assertForbidden();
    }
}
