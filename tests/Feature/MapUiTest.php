<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_interactive_map_page_is_accessible_and_uses_map_data_endpoint(): void
    {
        $this->get(route('map'))
            ->assertOk()
            ->assertSee('Discover places on the map')
            ->assertSee('tourism-map', false)
            ->assertSee(route('map.data'), false)
            ->assertSee('Near me')
            ->assertSee('No mapped places are available yet.');
    }

    public function test_map_page_exposes_no_private_account_data(): void
    {
        $this->get(route('map'))->assertDontSee('password')->assertDontSee('provider_id');
    }
}
