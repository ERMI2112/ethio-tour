<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicInformationArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_traveler_centered_public_navigation_groups(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Home')
            ->assertSee('Destinations')
            ->assertSee('Things to Do')
            ->assertSee('Events')
            ->assertSee('Plan Your Trip')
            ->assertSee('Smart Trip')
            ->assertSee('Search')
            ->assertSee('Culture &amp; Heritage', false)
            ->assertSee('Food &amp; Dining', false)
            ->assertSee('Tour Guides')
            ->assertSee('Hotels')
            ->assertSee('Restaurants')
            ->assertSee('Transportation &amp; Car Rental', false)
            ->assertSee('Map')
            ->assertSee('Museums')
            ->assertSee('Cultural Events')
            ->assertSee('Festivals / Upcoming Events')
            ->assertSee('Plan a Trip')
            ->assertDontSee('aria-expanded="false">Stay &amp; Eat', false)
            ->assertDontSee('aria-expanded="false">Travel &amp; Transport', false)
            ->assertDontSee('aria-expanded="false">Map', false)
            ->assertSee('data-bs-toggle="dropdown"', false)
            ->assertSee('data-bs-target="#primary-navigation"', false)
            ->assertSee('Highlands &amp; Nature', false);
    }

    public function test_public_urls_remain_available_under_the_new_navigation(): void
    {
        foreach (['/destinations', '/heritage-sites', '/tourism-services', '/categories', '/tour-guides', '/museums', '/transportation', '/events'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_public_breadcrumbs_use_the_new_information_architecture(): void
    {
        $this->get('/destinations')
            ->assertOk()
            ->assertSee('Home')
            ->assertSee('Destinations');

        $this->get('/tour-guides')
            ->assertOk()
            ->assertSee('Things to Do')
            ->assertSee('Tour Guides');

        $this->get('/tourism-services?provider_type=hotel')
            ->assertOk()
            ->assertSee('Plan Your Trip')
            ->assertSee('Hotels')
            ->assertDontSee('Stay &amp; Eat', false);

        $this->get('/tourism-services?provider_type=restaurant')
            ->assertOk()
            ->assertSee('Plan Your Trip')
            ->assertSee('Restaurants');

        $this->get('/transportation')
            ->assertOk()
            ->assertSee('Plan Your Trip')
            ->assertSee('Transportation &amp; Car Rental', false)
            ->assertDontSee('Travel &amp; Transport', false);

        $this->get('/museums')
            ->assertOk()
            ->assertSee('Plan Your Trip')
            ->assertSee('Museums');

        $this->get('/events')
            ->assertOk()
            ->assertSee('Events')
            ->assertSee('Cultural Events')
            ->assertDontSee('Events &amp; Festivals', false);

        $this->get('/map')
            ->assertOk()
            ->assertSee('Plan Your Trip')
            ->assertSee('Map');

        $this->get('/search')
            ->assertOk()
            ->assertSee('Home')
            ->assertSee('Search');

        $this->get('/smart-trip')
            ->assertOk()
            ->assertSee('Home')
            ->assertSee('Smart Trip');
    }
}
