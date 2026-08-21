<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\HeritageSite;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\Trip;
use App\Models\User;
use App\Services\SmartTripRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartTripTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_smart_trip_landing_page(): void
    {
        $this->get(route('smart-trip.index'))->assertOk()->assertSee('Build a trip that feels like yours.');
    }

    public function test_tourist_can_create_trip_with_real_suggestions(): void
    {
        [$destination, $category] = $this->catalog();
        $provider = $this->provider('hotel');
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Castle Hotel', 'price' => 120, 'description' => 'A comfortable historic stay', 'latitude' => 12.6, 'longitude' => 37.46]);
        HeritageSite::create(['destination_id' => $destination->destination_id, 'heritage_type' => 'Royal Castle', 'opening_hours' => '08:00-17:00', 'entrance_fee' => 10, 'latitude' => 12.61, 'longitude' => 37.47]);
        $user = $this->tourist();

        $response = $this->actingAs($user)->post(route('smart-trip.store'), [
            'title' => 'Gondar Weekend',
            'start_date' => today()->addDays(5)->toDateString(),
            'end_date' => today()->addDays(6)->toDateString(),
            'destination_ids' => [$destination->destination_id],
            'preferences' => ['history'],
        ]);

        $trip = Trip::firstOrFail();
        $response->assertRedirect(route('smart-trip.show', $trip));
        $this->assertSame((int) $user->user_id, (int) $trip->user_id);
        $this->assertTrue($trip->destinations()->whereKey($destination->destination_id)->exists());
        $this->assertSame('planned', $trip->fresh()->status);
        $this->assertTrue($trip->items()->where('item_type', 'service')->where('item_id', $service->service_id)->exists());
    }

    public function test_non_tourist_cannot_create_persisted_trip(): void
    {
        [$destination] = $this->catalog();
        $providerUser = User::factory()->create(['role' => 'service_provider']);

        $this->actingAs($providerUser)->post(route('smart-trip.store'), [
            'title' => 'Not allowed',
            'start_date' => today()->addDay()->toDateString(),
            'end_date' => today()->addDays(2)->toDateString(),
            'destination_ids' => [$destination->destination_id],
        ])->assertForbidden();
        $this->assertDatabaseCount('trips', 0);
    }

    public function test_tourist_ownership_protects_trip_and_items(): void
    {
        [$destination] = $this->catalog();
        $owner = $this->tourist();
        $other = $this->tourist();
        $trip = $this->makeTrip($owner, $destination);
        $item = $trip->items()->create(['item_type' => 'destination', 'item_id' => $destination->destination_id, 'planned_date' => $trip->start_date, 'sequence' => 1, 'source' => 'manual']);

        $this->actingAs($other)->get(route('smart-trip.show', $trip))->assertForbidden();
        $this->actingAs($other)->delete(route('smart-trip.items.destroy', [$trip, $item]))->assertForbidden();
        $this->actingAs($owner)->get(route('smart-trip.show', $trip))->assertOk()->assertSee($destination->name);
    }

    public function test_tourist_can_add_move_reorder_note_and_remove_real_item(): void
    {
        [$destination] = $this->catalog();
        $owner = $this->tourist();
        $trip = $this->makeTrip($owner, $destination);
        $heritage = HeritageSite::create(['destination_id' => $destination->destination_id, 'heritage_type' => 'Fasil Ghebbi', 'opening_hours' => '08:00-17:00', 'entrance_fee' => 10]);

        $this->actingAs($owner)->post(route('smart-trip.items.store', $trip), ['item_type' => 'heritage_site', 'item_id' => $heritage->heritage_id, 'planned_date' => $trip->start_date->toDateString(), 'notes' => 'Visit early'])->assertRedirect();
        $item = $trip->items()->where('item_type', 'heritage_site')->firstOrFail();
        $newDate = $trip->end_date->toDateString();
        $this->actingAs($owner)->patch(route('smart-trip.items.move', [$trip, $item]), ['planned_date' => $newDate])->assertRedirect();
        $this->actingAs($owner)->patch(route('smart-trip.items.notes', [$trip, $item]), ['notes' => 'Allow time for photos'])->assertRedirect();
        $this->actingAs($owner)->patch(route('smart-trip.items.position', [$trip, $item]), ['direction' => 'up'])->assertRedirect();
        $this->assertSame('Allow time for photos', $item->fresh()->notes);
        $this->assertSame($newDate, $item->fresh()->planned_date->toDateString());
        $this->actingAs($owner)->delete(route('smart-trip.items.destroy', [$trip, $item]))->assertRedirect();
        $this->assertDatabaseMissing('trip_items', ['trip_item_id' => $item->trip_item_id]);
    }

    public function test_invalid_or_private_resources_cannot_be_added(): void
    {
        [$destination] = $this->catalog();
        $owner = $this->tourist();
        $trip = $this->makeTrip($owner, $destination);
        $unapproved = $this->provider('hotel', 'pending');
        $category = Category::first();
        $hidden = TourismService::create(['provider_id' => $unapproved->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Hidden Stay', 'price' => 100, 'description' => 'Not public']);

        $this->actingAs($owner)->post(route('smart-trip.items.store', $trip), ['item_type' => 'service', 'item_id' => $hidden->service_id, 'planned_date' => $trip->start_date->toDateString()])->assertStatus(422);
        $this->actingAs($owner)->post(route('smart-trip.items.store', $trip), ['item_type' => 'destination', 'item_id' => $destination->destination_id, 'planned_date' => today()->addYears(2)->toDateString()])->assertStatus(422);
        $this->assertDatabaseCount('trip_items', 0);
    }

    public function test_event_suggestions_respect_trip_dates(): void
    {
        [$destination] = $this->catalog();
        $provider = $this->provider('event_organizer');
        $inside = CulturalEvent::create(['destination_id' => $destination->destination_id, 'provider_id' => $provider->provider_id, 'event_name' => 'Timket Festival', 'description' => 'Cultural festival', 'event_date' => today()->addDays(5), 'venue' => 'Square', 'status' => 'published']);
        CulturalEvent::create(['destination_id' => $destination->destination_id, 'provider_id' => $provider->provider_id, 'event_name' => 'Outside Festival', 'description' => 'Cultural festival', 'event_date' => today()->addDays(20), 'venue' => 'Square', 'status' => 'published']);
        $user = $this->tourist();
        $trip = $this->makeTrip($user, $destination, 5, 6);
        $recommendations = app(SmartTripRecommendationService::class)->recommendations($trip);

        $this->assertTrue($recommendations->contains(fn (array $item): bool => $item['item_type'] === 'event' && (int) $item['item_id'] === (int) $inside->event_id));
        $this->assertFalse($recommendations->contains(fn (array $item): bool => $item['title'] === 'Outside Festival'));
    }

    public function test_recommendations_are_deterministic_and_missing_coordinates_are_safe(): void
    {
        [$destination] = $this->catalog();
        $heritage = HeritageSite::create(['destination_id' => $destination->destination_id, 'heritage_type' => 'Castle', 'opening_hours' => '08:00-17:00', 'entrance_fee' => 10]);
        $user = $this->tourist();
        $trip = $this->makeTrip($user, $destination);
        $service = app(SmartTripRecommendationService::class);
        $first = $service->recommendations($trip)->map(fn (array $item): array => [$item['item_type'], $item['item_id'], $item['planned_date'], $item['score']])->all();
        $second = $service->recommendations($trip)->map(fn (array $item): array => [$item['item_type'], $item['item_id'], $item['planned_date'], $item['score']])->all();

        $this->assertSame($first, $second);
        $this->assertTrue(collect($first)->contains(fn (array $item): bool => $item[0] === 'heritage_site' && (int) $item[1] === (int) $heritage->heritage_id));
    }

    public function test_search_picker_and_private_trip_map_are_integrated(): void
    {
        [$destination] = $this->catalog();
        $owner = $this->tourist();
        $trip = $this->makeTrip($owner, $destination);
        $this->actingAs($owner)->get(route('smart-trip.items.create', $trip))->assertOk()->assertSee('Search the same public catalog');
        $this->actingAs($owner)->get(route('smart-trip.map', $trip))->assertOk()->assertSee(route('smart-trip.map.data', $trip), false);
        $this->actingAs($owner)->getJson(route('smart-trip.map.data', $trip))->assertOk()->assertJsonStructure(['data', 'count']);
    }

    public function test_existing_booking_flows_are_not_created_by_itinerary_items(): void
    {
        [$destination] = $this->catalog();
        $owner = $this->tourist();
        $trip = $this->makeTrip($owner, $destination);
        $trip->items()->create(['item_type' => 'destination', 'item_id' => $destination->destination_id, 'planned_date' => $trip->start_date, 'sequence' => 1, 'source' => 'manual']);

        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('trip_items', 1);
    }

    public function test_tourist_can_view_printable_itinerary_sheet(): void
    {
        [$destination] = $this->catalog();
        $owner = $this->tourist();
        $other = $this->tourist();
        $trip = $this->makeTrip($owner, $destination);
        $trip->items()->create(['item_type' => 'destination', 'item_id' => $destination->destination_id, 'planned_date' => $trip->start_date, 'sequence' => 1, 'source' => 'manual']);

        $this->actingAs($other)->get(route('smart-trip.print', $trip))->assertForbidden();
        $this->actingAs($owner)->get(route('smart-trip.print', $trip))
            ->assertOk()
            ->assertSee($trip->title)
            ->assertSee($destination->name)
            ->assertSee('Daily Travel Schedule');
    }

    private function tourist(): User
    {
        $user = User::factory()->create(['role' => 'tourist']);
        Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Trip Tourist', 'nationality' => 'Ethiopian']);

        return $user;
    }

    private function provider(string $type, string $status = 'approved'): ServiceProvider
    {
        $user = User::factory()->create(['role' => 'service_provider']);

        return ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => ucfirst($type).' Provider', 'provider_type' => $type, 'status' => $status]);
    }

    private function catalog(): array
    {
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar', 'location' => 'Amhara', 'description' => 'Historic city', 'latitude' => 12.6, 'longitude' => 37.46]);
        $category = Category::create(['category_name' => 'Accommodation']);

        return [$destination, $category, $officer];
    }

    private function makeTrip(User $user, Destination $destination, int $startOffset = 5, int $endOffset = 6): Trip
    {
        $trip = Trip::create(['user_id' => $user->user_id, 'title' => 'Saved Trip', 'start_date' => today()->addDays($startOffset), 'end_date' => today()->addDays($endOffset), 'preferences' => ['history']]);
        $trip->destinations()->attach($destination->destination_id);

        return $trip;
    }
}
