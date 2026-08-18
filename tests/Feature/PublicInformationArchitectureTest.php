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
            ->assertSee('data-nav-placeholder="true"', false);
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
            ->assertSee('Explore Ethiopia')
            ->assertSee('Destinations');

        $this->get('/tour-guides')
            ->assertOk()
            ->assertSee('Things to Do')
            ->assertSee('Tour Guides');
    }
}
