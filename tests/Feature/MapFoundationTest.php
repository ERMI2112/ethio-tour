<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Destination;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MapFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_coordinates_are_nullable_and_valid_ranges_are_persisted(): void
    {
        $destination = $this->destination(['latitude' => -12.3456789, 'longitude' => 44.9876543]);
        $this->assertSame('-12.3456789', $destination->fresh()->latitude);
        $this->assertSame('44.9876543', $destination->fresh()->longitude);

        $withoutCoordinates = $this->destination();
        $this->assertNull($withoutCoordinates->latitude);
        $this->assertNull($withoutCoordinates->longitude);
        $this->assertNotSame('0.0000000', (string) $withoutCoordinates->latitude);
    }

    public function test_invalid_coordinate_ranges_and_unpaired_values_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->destination(['latitude' => 90.000001, 'longitude' => 35]);
    }

    public function test_unpaired_coordinates_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->destination(['latitude' => 12.3]);
    }

    public function test_public_map_data_contains_real_coordinates_and_filters_provider_governance(): void
    {
        $destination = $this->destination(['latitude' => 12.6, 'longitude' => 37.46]);
        $category = Category::create(['category_name' => 'Lodging']);
        $eligibleUser = User::factory()->create(['role' => 'service_provider']);
        $eligibleProvider = ServiceProvider::create(['user_id' => $eligibleUser->user_id, 'business_name' => 'Eligible Hotel', 'provider_type' => 'hotel', 'status' => 'approved']);
        $eligible = TourismService::create(['provider_id' => $eligibleProvider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Lake Hotel', 'price' => 100, 'description' => 'Public hotel', 'latitude' => 12.61, 'longitude' => 37.47]);
        $hiddenUser = User::factory()->create(['role' => 'service_provider']);
        $hiddenProvider = ServiceProvider::create(['user_id' => $hiddenUser->user_id, 'business_name' => 'Pending Hotel', 'provider_type' => 'hotel', 'status' => 'pending']);
        TourismService::create(['provider_id' => $hiddenProvider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Hidden Hotel', 'price' => 100, 'description' => 'Private hotel', 'latitude' => 12.62, 'longitude' => 37.48]);

        $response = $this->getJson(route('map.data', ['category' => 'services']));
        $response->assertOk()->assertJsonFragment(['title' => 'Lake Hotel'])->assertJsonMissing(['title' => 'Hidden Hotel'])->assertJsonMissing(['email' => $eligibleUser->email]);
        $this->assertSame(1, $response->json('count'));
        $this->assertSame((float) $eligible->latitude, $response->json('data.0.latitude'));
    }

    public function test_public_map_data_supports_category_and_search_filters(): void
    {
        $destination = $this->destination(['latitude' => 12.6, 'longitude' => 37.46]);

        $this->getJson(route('map.data', ['category' => 'destinations', 'q' => 'Gondar']))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Gondar']);
    }

    public function test_map_data_is_publicly_accessible_without_authentication(): void
    {
        $this->getJson(route('map.data'))->assertOk()->assertJsonStructure(['data', 'count']);
    }

    private function destination(array $attributes = []): Destination
    {
        $officer = TourismBureauOfficer::create(['user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id]);

        return Destination::create(array_merge(['officer_id' => $officer->officer_id, 'name' => 'Gondar', 'location' => 'Amhara', 'description' => 'Historic destination'], $attributes));
    }
}
