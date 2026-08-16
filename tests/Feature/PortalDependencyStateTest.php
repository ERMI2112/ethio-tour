<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalDependencyStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_forms_explain_missing_reference_data_instead_of_showing_empty_required_selectors(): void
    {
        $eventOrganizer = $this->provider('event_organizer');
        $hotel = $this->provider('hotel');
        $restaurant = $this->provider('restaurant');
        $transportation = $this->provider('transportation_car_rental');

        $this->actingAs($eventOrganizer)->get(route('event-organizer.events.create'))
            ->assertOk()
            ->assertSee('Reference data is not available yet.')
            ->assertSee('At least one destination and category must be published');

        $this->actingAs($hotel)->get(route('hotel.services.create'))
            ->assertOk()
            ->assertSee('Reference data is not available yet.')
            ->assertSee('before a room type can be created');

        $this->actingAs($restaurant)->get(route('restaurant.services.create'))
            ->assertOk()
            ->assertSee('Reference data is not available yet.')
            ->assertSee('before a restaurant service can be created');

        $this->actingAs($transportation)->get(route('transportation.services.create'))
            ->assertOk()
            ->assertSee('Reference data is not available yet.')
            ->assertSee('before a transportation service can be created');

        $this->actingAs($transportation)->get(route('transportation.vehicles.create'))
            ->assertOk()
            ->assertSee('No transportation services available.')
            ->assertSee('Create a transportation service first');
    }

    private function provider(string $type): User
    {
        $user = User::factory()->create(['role' => 'service_provider']);

        ServiceProvider::create([
            'user_id' => $user->user_id,
            'business_name' => ucfirst(str_replace('_', ' ', $type)),
            'provider_type' => $type,
            'status' => 'approved',
        ]);

        return $user;
    }
}
