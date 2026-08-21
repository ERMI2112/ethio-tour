<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Destination;
use App\Models\ServiceProvider;
use App\Models\TourismService;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (! str_contains($this->name(), 'empty_states')) {
            $this->seed(UatDemoSeeder::class);
        }
    }

    public function test_landing_page_has_traveler_search_and_real_discovery_entry_points(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Find your next story in Ethiopia.')
            ->assertSee('name="q"', false)
            ->assertSee('action="'.route('search').'"', false)
            ->assertSee('Gondar')
            ->assertSee('Timkat Gondar Epiphany &amp; Cultural Festival', false)
            ->assertSee(route('map'))
            ->assertSee(route('smart-trip.index'))
            ->assertSee('See what is around you.')
            ->assertDontSee('Nine portals')
            ->assertDontSee(route('portals.index'))
            ->assertDontSee('Admin Dashboard')
            ->assertDontSee('Provider Workspace')
            ->assertSee('navbar-toggler', false);
    }

    public function test_unapproved_provider_services_are_not_promoted_on_landing_page(): void
    {
        $provider = ServiceProvider::where('business_name', 'Ras Dashen Lodge (Pending)')->firstOrFail();
        $destination = Destination::where('name', 'Gondar')->firstOrFail();
        $category = Category::where('category_name', 'Accommodation')->firstOrFail();
        TourismService::create([
            'provider_id' => $provider->provider_id,
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Hidden Unapproved Service',
            'description' => 'This service must remain private until governance is complete.',
            'price' => 100,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Hidden Unapproved Service');
    }

    public function test_landing_page_uses_honest_empty_states_without_fake_social_proof(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('No destinations are available yet.')
            ->assertSee('No public experiences are available yet.')
            ->assertSee('No reviews are available yet.')
            ->assertDontSee('4.9 out of 5')
            ->assertDontSee('Trusted by thousands');
    }

    public function test_landing_calls_to_action_use_specific_public_discovery_routes(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee(route('tourism-services.index', ['provider_type' => 'hotel']), false)
            ->assertSee(route('tourism-services.index', ['provider_type' => 'restaurant']), false)
            ->assertSee(route('transportation.index'), false)
            ->assertSee(route('events.index'), false)
            ->assertSee(route('tour-guides.index'), false)
            ->assertSee(route('museums.index'), false)
            ->assertSee(route('map'), false)
            ->assertSee(route('smart-trip.index'), false)
            ->assertDontSee('Ethio Tour foundation is ready.')
            ->assertDontSee('Upcoming cultural events · Plan My Trip');
    }
}
