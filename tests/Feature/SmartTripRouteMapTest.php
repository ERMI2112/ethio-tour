<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\TourismBureauOfficer;
use App\Models\Tourist;
use App\Models\Trip;
use App\Models\TripItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartTripRouteMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_tourist_can_view_multi_stop_route_map_and_metrics(): void
    {
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);

        $gondar = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara Region',
            'description' => 'Camelot of Africa',
            'latitude' => 12.6087000,
            'longitude' => 37.4683000,
        ]);

        $bahirDar = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Bahir Dar',
            'location' => 'Amhara Region',
            'description' => 'Lake Tana',
            'latitude' => 11.5936000,
            'longitude' => 37.3908000,
        ]);

        $touristUser = User::factory()->create(['role' => 'tourist', 'is_active' => true]);
        $tourist = Tourist::create(['user_id' => $touristUser->user_id, 'full_name' => 'Sara Connor', 'nationality' => 'Canada']);

        $trip = Trip::create([
            'user_id' => $touristUser->user_id,
            'title' => 'Northern Ethiopia Grand Circuit',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(9)->toDateString(),
            'status' => 'planned',
        ]);

        $trip->destinations()->attach([$gondar->destination_id, $bahirDar->destination_id]);

        // Add 2 sequenced stops with coordinates
        TripItem::create([
            'trip_id' => $trip->trip_id,
            'item_type' => 'destination',
            'item_id' => $gondar->destination_id,
            'planned_date' => now()->addDays(5)->toDateString(),
            'sequence' => 1,
        ]);

        TripItem::create([
            'trip_id' => $trip->trip_id,
            'item_type' => 'destination',
            'item_id' => $bahirDar->destination_id,
            'planned_date' => now()->addDays(7)->toDateString(),
            'sequence' => 2,
        ]);

        // Map View Page
        $response = $this->actingAs($touristUser)->get(route('smart-trip.map', $trip));
        $response->assertOk();
        $response->assertSee('Northern Ethiopia Grand Circuit');
        $response->assertSee('Total Route Distance');
        $response->assertSee('Estimated Travel Time');
        $response->assertSee('Gondar');
        $response->assertSee('Bahir Dar');

        // Map Data JSON Endpoint
        $jsonResponse = $this->actingAs($touristUser)->get(route('smart-trip.map.data', $trip));
        $jsonResponse->assertOk();
        $jsonResponse->assertJsonStructure([
            'data' => [
                '*' => ['sequence_number', 'day_number', 'type', 'title', 'summary', 'latitude', 'longitude', 'url'],
            ],
            'count',
            'route_segments' => [
                '*' => ['leg_number', 'from_title', 'to_title', 'distance_km', 'formatted_distance', 'duration_minutes', 'formatted_duration', 'polyline'],
            ],
            'total_distance_km',
            'formatted_total_distance',
            'total_duration_minutes',
            'formatted_total_duration',
        ]);

        $payload = $jsonResponse->json();
        $this->assertEquals(2, $payload['count']);
        $this->assertCount(1, $payload['route_segments']);
        $this->assertGreaterThan(100, $payload['total_distance_km']); // Gondar to Bahir Dar is ~140-180 km
        $this->assertStringContainsString('km', $payload['formatted_total_distance']);
    }

    public function test_non_owner_cannot_access_private_trip_map(): void
    {
        $ownerUser = User::factory()->create(['role' => 'tourist', 'is_active' => true]);
        Tourist::create(['user_id' => $ownerUser->user_id, 'full_name' => 'Owner', 'nationality' => 'Ethiopian']);

        $otherUser = User::factory()->create(['role' => 'tourist', 'is_active' => true]);
        Tourist::create(['user_id' => $otherUser->user_id, 'full_name' => 'Other', 'nationality' => 'Ethiopian']);

        $trip = Trip::create([
            'user_id' => $ownerUser->user_id,
            'title' => 'Secret Private Trip',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'status' => 'planned',
        ]);

        $this->actingAs($otherUser)->get(route('smart-trip.map', $trip))->assertForbidden();
        $this->actingAs($otherUser)->get(route('smart-trip.map.data', $trip))->assertForbidden();
    }
}
