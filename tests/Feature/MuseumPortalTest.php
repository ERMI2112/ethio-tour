<?php

namespace Tests\Feature;

use App\Models\MuseumInformation;
use App\Models\TourismBureauOfficer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MuseumPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_museums_are_publicly_listed_searched_and_viewable(): void
    {
        $museum = $this->museum('Royal Heritage Museum', 'Gondar');
        $other = $this->museum('City Culture Centre', 'Addis Ababa');

        $this->get(route('museums.index'))->assertOk()
            ->assertSee($museum->museum_name)
            ->assertSee($other->museum_name);
        $this->get(route('museums.index', ['q' => 'Gondar']))->assertOk()
            ->assertSee($museum->museum_name)
            ->assertDontSee($other->museum_name);
        $this->get(route('museums.show', $museum))->assertOk()
            ->assertSee($museum->description)
            ->assertSee($museum->opening_hours)
            ->assertSee('Fee applies — paid at the site');
    }

    public function test_bureau_officer_can_create_and_edit_owned_museum_information(): void
    {
        $context = $this->officerContext();

        $this->actingAs($context['user'])->get(route('bureau.museums.index'))->assertOk();
        $this->actingAs($context['user'])->post(route('bureau.museums.store'), [
            'museum_name' => 'Fasil Museum',
            'description' => 'A public cultural collection.',
            'location' => 'Gondar',
            'opening_hours' => '08:00-17:00',
            'entrance_fee' => '100',
            'contact_information' => 'info@example.test',
        ])->assertRedirect(route('bureau.museums.index'));

        $museum = $context['officer']->museumInformation()->firstOrFail();
        $this->assertSame($context['officer']->officer_id, $museum->officer_id);

        $this->actingAs($context['user'])->put(route('bureau.museums.update', $museum), [
            'museum_name' => 'Updated Fasil Museum',
            'description' => 'Updated description.',
            'location' => 'Gondar',
            'opening_hours' => '09:00-18:00',
            'entrance_fee' => '125.50',
        ])->assertRedirect(route('bureau.museums.index'));

        $this->assertDatabaseHas('museum_information', ['museum_id' => $museum->museum_id, 'museum_name' => 'Updated Fasil Museum']);
    }

    public function test_only_bureau_officers_can_manage_museums(): void
    {
        $tourist = User::factory()->create(['role' => 'tourist']);
        $this->actingAs($tourist)->get(route('bureau.museums.index'))->assertForbidden();
        $this->get(route('bureau.museums.index'))->assertForbidden();
    }

    public function test_officer_cannot_access_another_officers_museum(): void
    {
        $owner = $this->officerContext();
        $other = $this->officerContext();
        $museum = $this->museum('Owner Museum', 'Gondar', $owner['officer']);

        $this->actingAs($other['user'])->get(route('bureau.museums.edit', $museum))->assertForbidden();
        $this->actingAs($other['user'])->put(route('bureau.museums.update', $museum), [
            'museum_name' => 'Tampered Museum',
            'description' => 'No.',
            'location' => 'Gondar',
            'opening_hours' => '08:00-17:00',
        ])->assertForbidden();
        $this->actingAs($other['user'])->delete(route('bureau.museums.destroy', $museum))->assertForbidden();
        $this->assertDatabaseHas('museum_information', ['museum_id' => $museum->museum_id, 'museum_name' => 'Owner Museum']);
    }

    public function test_museum_validation_rejects_missing_fields_and_negative_fee(): void
    {
        $context = $this->officerContext();

        $this->actingAs($context['user'])->post(route('bureau.museums.store'), [
            'museum_name' => '',
            'description' => '',
            'location' => '',
            'opening_hours' => '',
            'entrance_fee' => '-1',
        ])->assertSessionHasErrors(['museum_name', 'description', 'location', 'opening_hours', 'entrance_fee']);
    }

    public function test_submitted_officer_id_cannot_change_ownership(): void
    {
        $owner = $this->officerContext();
        $other = $this->officerContext();

        $this->actingAs($owner['user'])->post(route('bureau.museums.store'), [
            'museum_name' => 'Owned Museum',
            'description' => 'Description',
            'location' => 'Gondar',
            'opening_hours' => '08:00-17:00',
            'officer_id' => $other['officer']->officer_id,
        ])->assertRedirect();

        $museum = $owner['officer']->museumInformation()->firstOrFail();
        $this->assertSame($owner['officer']->officer_id, $museum->officer_id);
    }

    public function test_navigation_exposes_public_museums_and_bureau_management_only_to_bureau_officers(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Museums')->assertDontSee('Museum Information');
        $bureau = $this->officerContext();
        $this->actingAs($bureau['user'])->get(route('home'))->assertOk()->assertSee('Museum Information');
        $tourist = User::factory()->create(['role' => 'tourist']);
        $this->actingAs($tourist)->get(route('home'))->assertOk()->assertDontSee('Museum Information');
    }

    private function officerContext(): array
    {
        $user = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $user->user_id]);

        return compact('user', 'officer');
    }

    private function museum(string $name, string $location, ?TourismBureauOfficer $officer = null): MuseumInformation
    {
        $officer ??= $this->officerContext()['officer'];

        return MuseumInformation::create([
            'officer_id' => $officer->officer_id,
            'museum_name' => $name,
            'description' => $name.' description',
            'location' => $location,
            'opening_hours' => '08:00-17:00',
            'entrance_fee' => 150,
            'contact_information' => 'contact@example.test',
        ]);
    }
}
