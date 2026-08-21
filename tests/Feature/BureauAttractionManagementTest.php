<?php

namespace Tests\Feature;

use App\Models\Attraction;
use App\Models\Destination;
use App\Models\TourismBureauOfficer;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BureauAttractionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_bureau_officer_can_view_attractions_in_jurisdiction(): void
    {
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer', 'is_active' => true]);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);

        $dest = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara Region',
            'description' => 'Historic city',
        ]);

        $attraction = Attraction::create([
            'destination_id' => $dest->destination_id,
            'name' => 'Fasil Ghebbi Royal Enclosure',
            'slug' => 'fasil-ghebbi-royal-enclosure',
            'category' => 'heritage_site',
            'description' => 'UNESCO World Heritage castle complex.',
            'opening_hours' => '08:30 – 17:30 daily',
            'entry_fee' => 200.00,
            'latitude' => 12.6087000,
            'longitude' => 37.4683000,
            'is_featured' => true,
        ]);

        $response = $this->actingAs($officerUser)->get(route('bureau.attractions.index'));
        $response->assertOk();
        $response->assertSee('Fasil Ghebbi Royal Enclosure');
        $response->assertSee('200.00 ETB');
        $response->assertSee('Heritage Site');
    }

    public function test_bureau_officer_can_create_new_attraction(): void
    {
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer', 'is_active' => true]);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);

        $dest = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara Region',
            'description' => 'Historic city',
        ]);

        $this->actingAs($officerUser)->get(route('bureau.attractions.create'))->assertOk();

        $response = $this->actingAs($officerUser)->post(route('bureau.attractions.store'), [
            'destination_id' => $dest->destination_id,
            'name' => 'Debre Berhan Selassie Church',
            'category' => 'church',
            'description' => 'Famous for its ceiling covered in painted angel faces.',
            'location_address' => 'Northeast Gondar, Amhara Region',
            'latitude' => 12.6130000,
            'longitude' => 37.4700000,
            'opening_hours' => '08:00 – 17:00 daily',
            'entry_fee' => 200.00,
            'is_featured' => 1,
            'image_url' => '/images/attractions/debre-berhan-selassie.jpg',
        ]);

        $response->assertRedirect(route('bureau.attractions.index'));

        $this->assertDatabaseHas('attractions', [
            'destination_id' => $dest->destination_id,
            'name' => 'Debre Berhan Selassie Church',
            'category' => 'church',
            'entry_fee' => 200.00,
            'is_featured' => true,
        ]);
    }

    public function test_bureau_officer_can_edit_and_delete_attraction(): void
    {
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer', 'is_active' => true]);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);

        $dest = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara Region',
            'description' => 'Historic city',
        ]);

        $attraction = Attraction::create([
            'destination_id' => $dest->destination_id,
            'name' => 'Fasilides Bath',
            'slug' => 'fasilides-bath',
            'category' => 'monument',
            'description' => 'Sunken pavilion and pool for Timkat celebration.',
            'entry_fee' => 150.00,
        ]);

        // Edit view
        $this->actingAs($officerUser)->get(route('bureau.attractions.edit', $attraction))->assertOk();

        // Update
        $this->actingAs($officerUser)->put(route('bureau.attractions.update', $attraction), [
            'destination_id' => $dest->destination_id,
            'name' => 'Fasilides Imperial Bath',
            'category' => 'monument',
            'description' => 'Updated historical overview.',
            'entry_fee' => 250.00,
        ])->assertRedirect(route('bureau.attractions.index'));

        $this->assertEquals('Fasilides Imperial Bath', $attraction->fresh()->name);
        $this->assertEquals('250.00', $attraction->fresh()->entry_fee);

        // Destroy
        $this->actingAs($officerUser)->delete(route('bureau.attractions.destroy', $attraction))->assertRedirect(route('bureau.attractions.index'));
        $this->assertDatabaseMissing('attractions', ['attraction_id' => $attraction->attraction_id]);
    }

    public function test_officer_cannot_manage_attractions_in_other_jurisdictions(): void
    {
        $officerUser1 = User::factory()->create(['role' => 'tourism_bureau_officer', 'is_active' => true]);
        $officer1 = TourismBureauOfficer::create(['user_id' => $officerUser1->user_id]);
        $dest1 = Destination::create(['officer_id' => $officer1->officer_id, 'name' => 'Dest 1', 'location' => 'Region 1', 'description' => 'Desc 1']);

        $officerUser2 = User::factory()->create(['role' => 'tourism_bureau_officer', 'is_active' => true]);
        $officer2 = TourismBureauOfficer::create(['user_id' => $officerUser2->user_id]);
        $dest2 = Destination::create(['officer_id' => $officer2->officer_id, 'name' => 'Dest 2', 'location' => 'Region 2', 'description' => 'Desc 2']);

        $attraction = Attraction::create([
            'destination_id' => $dest1->destination_id,
            'name' => 'Attraction in Dest 1',
            'slug' => 'attraction-dest-1',
            'category' => 'heritage_site',
            'description' => 'Description',
        ]);

        // Officer 2 attempts to edit officer 1's attraction
        $this->actingAs($officerUser2)->get(route('bureau.attractions.edit', $attraction))->assertForbidden();

        // Officer 2 attempts to store attraction under officer 1's destination
        $this->actingAs($officerUser2)->post(route('bureau.attractions.store'), [
            'destination_id' => $dest1->destination_id,
            'name' => 'Unauthorized Attraction',
            'category' => 'museum',
            'description' => 'Description',
        ])->assertSessionHasErrors('destination_id');
    }

    public function test_non_bureau_user_cannot_access_attractions_management(): void
    {
        $touristUser = User::factory()->create(['role' => 'tourist']);
        Tourist::create(['user_id' => $touristUser->user_id, 'full_name' => 'Sam Traveler', 'nationality' => 'Ethiopian']);

        $this->actingAs($touristUser)->get(route('bureau.attractions.index'))->assertForbidden();
        $this->actingAs($touristUser)->get(route('bureau.attractions.create'))->assertForbidden();
    }
}
