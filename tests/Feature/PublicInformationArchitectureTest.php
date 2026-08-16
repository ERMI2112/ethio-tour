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
            ->assertSee('Explore Ethiopia')
            ->assertSee('Things to Do')
            ->assertSee('Stay &amp; Eat', false)
            ->assertSee('Travel &amp; Transport', false)
            ->assertSee('Events')
            ->assertSee('Plan Your Trip')
            ->assertSee('Smart Trip')
            ->assertSee('Search')
            ->assertSee('Destinations')
            ->assertSee('Tour Guides')
            ->assertSee('Transportation')
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
