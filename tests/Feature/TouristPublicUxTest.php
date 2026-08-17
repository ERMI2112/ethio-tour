<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TouristPublicUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_homepage_guides_guest_through_real_public_discovery(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Discover Ethiopia')->assertSee('Explore Ethiopia')->assertSee('Things to Do')->assertSee('Stay &amp; Eat', false)->assertSee('Upcoming cultural events')->assertSee('Plan My Trip')->assertSee('Explore on Map');
    }

    public function test_public_journey_routes_and_smart_trip_entry_are_reachable(): void
    {
        foreach ([route('destinations.index'), route('tour-guides.index'), route('tourism-services.index'), route('transportation.index'), route('events.index'), route('search'), route('map'), route('smart-trip.index')] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_tourist_private_bookings_and_notifications_remain_protected(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $other = User::factory()->create(['role' => 'tourist']);

        $this->actingAs($tourist)->get(route('tourist.reservations.index'))->assertOk()->assertSee('My Bookings');
        $this->actingAs($other)->get(route('notifications.index'))->assertOk();
        $this->actingAs($tourist)->get(route('account'))->assertOk();
    }
}
