<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTouristNavigationCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_plan_your_trip_links_only_to_real_public_tourist_discovery_routes(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Plan with real public services')
            ->assertSee(route('tourism-services.index', ['provider_type' => 'hotel']), false)
            ->assertSee(route('tourism-services.index', ['provider_type' => 'restaurant']), false)
            ->assertSee(route('tour-guides.index'), false)
            ->assertSee(route('transportation.index'), false)
            ->assertSee(route('events.index'), false)
            ->assertSee(route('museums.index'), false)
            ->assertSee(route('smart-trip.index'), false)
            ->assertDontSee('Trip Planner (coming soon)')
            ->assertDontSee('Explore on Map (coming soon)')
            ->assertDontSee('Hotel Dashboard')
            ->assertDontSee('Bureau Dashboard')
            ->assertDontSee('Admin Dashboard');
    }

    public function test_hotel_and_restaurant_public_filters_only_return_the_requested_operational_provider_type(): void
    {
        $this->get(route('tourism-services.index', ['provider_type' => 'hotel']))
            ->assertOk()
            ->assertSee('Hotels')
            ->assertSee('UAT Standard Room')
            ->assertDontSee('UAT Gondar Dining Reservation');

        $this->get(route('tourism-services.index', ['provider_type' => 'restaurant']))
            ->assertOk()
            ->assertSee('Restaurants')
            ->assertSee('UAT Gondar Dining Reservation')
            ->assertDontSee('UAT Standard Room');
    }

    public function test_authenticated_tourist_keeps_consumer_navigation_without_provider_or_governance_links(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();

        $this->actingAs($tourist)->get(route('home'))
            ->assertOk()
            ->assertSee('My Bookings')
            ->assertDontSee('Hotel Dashboard')
            ->assertDontSee('Restaurant Dashboard')
            ->assertDontSee('Transportation Dashboard')
            ->assertDontSee('Event Dashboard')
            ->assertDontSee('Tour Guide Portal')
            ->assertDontSee('Bureau Dashboard')
            ->assertDontSee('Admin Dashboard');
    }
}
