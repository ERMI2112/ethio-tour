<?php

namespace Tests\Feature;

use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\HeritageSite;
use App\Models\MuseumInformation;
use App\Models\TourismService;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapDiscoveryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_public_detail_pages_link_to_map_only_when_coordinates_exist(): void
    {
        $destination = Destination::where('name', 'Gondar')->firstOrFail();
        $service = TourismService::where('service_name', 'Standard Heritage View Room')->firstOrFail();
        $event = CulturalEvent::where('event_name', 'Timkat Gondar Epiphany & Cultural Festival')->firstOrFail();
        $museum = MuseumInformation::firstOrFail();
        $heritage = HeritageSite::create([
            'destination_id' => $destination->destination_id,
            'heritage_type' => 'Gondar Royal Heritage Site',
            'opening_hours' => '08:00-17:00',
            'entrance_fee' => 50,
            'latitude' => 12.61,
            'longitude' => 37.47,
        ]);

        $this->get(route('destinations.show', $destination))->assertOk()->assertDontSee('View on Map');

        $destination->update(['latitude' => 12.6, 'longitude' => 37.46]);
        $service->update(['latitude' => 12.61, 'longitude' => 37.47]);
        $event->update(['latitude' => 12.62, 'longitude' => 37.48]);
        $museum->update(['latitude' => 12.63, 'longitude' => 37.49]);

        $this->get(route('destinations.show', $destination))->assertSee(e(route('map', ['category' => 'destinations', 'q' => 'Gondar'])), false);
        $this->get(route('heritage-sites.show', $heritage))->assertSee(e(route('map', ['category' => 'heritage_sites', 'q' => $heritage->heritage_type])), false);
        $this->get(route('tourism-services.show', $service))->assertSee(e(route('map', ['category' => 'hotels', 'q' => $service->service_name])), false);
        $this->get(route('events.show', $event))->assertSee(e(route('map', ['category' => 'events', 'q' => $event->event_name])), false);
        $this->get(route('museums.show', $museum))->assertSee(e(route('map', ['category' => 'museums', 'q' => $museum->museum_name])), false);
    }

    public function test_map_entry_points_remain_public_and_smart_trip_uses_existing_map(): void
    {
        $this->get(route('home'))->assertOk()->assertSee(route('map'), false);
        $this->get(route('map'))->assertOk()->assertSee('Near me');
        $this->get(route('map.data'))->assertOk()->assertJsonStructure(['data', 'count']);
        $this->get(route('smart-trip.index'))->assertOk();
    }
}
