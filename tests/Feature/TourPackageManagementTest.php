<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourPackageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_can_view_tours_catalog_and_create_package(): void
    {
        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);

        $dest = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Simien Mountains',
            'location' => 'Amhara Region',
            'description' => 'National park highlands',
        ]);

        $guideUser = User::factory()->create(['role' => 'tour_guide', 'is_active' => true]);
        $guide = TourGuide::create([
            'user_id' => $guideUser->user_id,
            'full_name' => 'Kassahun Melaku',
            'license_number' => 'ET-SIM-001',
            'expertise' => 'Mountain trekking',
            'availability_status' => 'available',
            'daily_rate' => 2000.00,
        ]);
        $guide->forceFill(['verification_status' => 'verified', 'admin_approval_status' => 'approved'])->save();

        // Access catalog
        $this->actingAs($guideUser)->get(route('tour-guide.tours'))->assertOk();

        // Access create form
        $this->actingAs($guideUser)->get(route('tour-guide.packages.create'))->assertOk();

        // Store new package
        $response = $this->actingAs($guideUser)->post(route('tour-guide.packages.store'), [
            'title' => '3-Day Simien Mountains Trekking Expedition',
            'destination_id' => $dest->destination_id,
            'duration_days' => 3,
            'price' => 7500.00,
            'max_group_size' => 8,
            'difficulty_level' => 'challenging',
            'description' => 'A breathtaking journey along dramatic escarpments to spot Gelada baboons and Jinbar Waterfall.',
            'itinerary_days' => [
                ['title' => 'Sankaber Camp Trek', 'description' => 'Departure from Debark and 4-hour hike to Sankaber.'],
                ['title' => 'Jinbar Waterfall Escarpment', 'description' => 'Witness 500m waterfall drop and wildlife.'],
                ['title' => 'Chennek Ridge Summit', 'description' => 'Ascend to high peaks and return to Gondar.'],
            ],
            'included' => "Licensed Scout & Guide\nPark Entry Fees\nCamping Equipment\nCook & 3 Meals Daily",
            'excluded' => "Personal Travel Insurance\nAlcoholic Drinks\nTips for Scouts",
        ]);

        $response->assertRedirect(route('tour-guide.tours'));

        $this->assertDatabaseHas('tour_packages', [
            'guide_id' => $guide->guide_id,
            'title' => '3-Day Simien Mountains Trekking Expedition',
            'duration_days' => 3,
            'difficulty_level' => 'challenging',
            'is_active' => true,
        ]);

        $pkg = TourPackage::where('guide_id', $guide->guide_id)->first();
        $this->assertCount(3, $pkg->itineraryList());
        $this->assertCount(4, $pkg->includedList());
        $this->assertCount(3, $pkg->excludedList());
    }

    public function test_guide_can_edit_toggle_and_delete_own_package(): void
    {
        $guideUser = User::factory()->create(['role' => 'tour_guide', 'is_active' => true]);
        $guide = TourGuide::create([
            'user_id' => $guideUser->user_id,
            'full_name' => 'Kassahun Melaku',
            'license_number' => 'ET-SIM-001',
            'expertise' => 'Highland hikes',
            'availability_status' => 'available',
        ]);
        $guide->forceFill(['verification_status' => 'verified', 'admin_approval_status' => 'approved'])->save();

        $pkg = TourPackage::create([
            'guide_id' => $guide->guide_id,
            'title' => 'Initial Title',
            'slug' => 'initial-title',
            'duration_days' => 2,
            'price' => 4000.00,
            'max_group_size' => 6,
            'difficulty_level' => 'moderate',
            'description' => 'Initial description',
            'is_active' => true,
        ]);

        // Edit form
        $this->actingAs($guideUser)->get(route('tour-guide.packages.edit', $pkg))->assertOk();

        // Update
        $this->actingAs($guideUser)->put(route('tour-guide.packages.update', $pkg), [
            'title' => 'Updated Simien Circuit',
            'duration_days' => 2,
            'price' => 4500.00,
            'max_group_size' => 6,
            'difficulty_level' => 'moderate',
            'description' => 'Updated excursion details',
        ])->assertRedirect(route('tour-guide.tours'));

        $this->assertEquals('Updated Simien Circuit', $pkg->fresh()->title);

        // Toggle active status
        $this->actingAs($guideUser)->patch(route('tour-guide.packages.toggle', $pkg))->assertRedirect();
        $this->assertFalse($pkg->fresh()->is_active);

        // Delete
        $this->actingAs($guideUser)->delete(route('tour-guide.packages.destroy', $pkg))->assertRedirect(route('tour-guide.tours'));
        $this->assertDatabaseMissing('tour_packages', ['package_id' => $pkg->package_id]);
    }

    public function test_guide_cannot_modify_another_guides_package(): void
    {
        $guideUser1 = User::factory()->create(['role' => 'tour_guide', 'is_active' => true]);
        $guide1 = TourGuide::create(['user_id' => $guideUser1->user_id, 'full_name' => 'Guide 1', 'license_number' => 'ET-001', 'expertise' => 'Tours', 'availability_status' => 'available']);

        $guideUser2 = User::factory()->create(['role' => 'tour_guide', 'is_active' => true]);
        $guide2 = TourGuide::create(['user_id' => $guideUser2->user_id, 'full_name' => 'Guide 2', 'license_number' => 'ET-002', 'expertise' => 'Tours', 'availability_status' => 'available']);

        $pkg = TourPackage::create([
            'guide_id' => $guide1->guide_id,
            'title' => 'Guide 1 Tour',
            'slug' => 'guide-1-tour',
            'duration_days' => 1,
            'price' => 1000.00,
            'max_group_size' => 4,
            'difficulty_level' => 'easy',
            'description' => 'Tour description',
            'is_active' => true,
        ]);

        $this->actingAs($guideUser2)->get(route('tour-guide.packages.edit', $pkg))->assertForbidden();
        $this->actingAs($guideUser2)->put(route('tour-guide.packages.update', $pkg), [
            'title' => 'Hacked Title',
            'duration_days' => 1,
            'price' => 1000.00,
            'max_group_size' => 4,
            'difficulty_level' => 'easy',
            'description' => 'Tour description',
        ])->assertForbidden();
        $this->actingAs($guideUser2)->delete(route('tour-guide.packages.destroy', $pkg))->assertForbidden();
    }

    public function test_public_can_view_packages_on_verified_guide_profile(): void
    {
        $guideUser = User::factory()->create(['role' => 'tour_guide', 'is_active' => true]);
        $guide = TourGuide::create([
            'user_id' => $guideUser->user_id,
            'full_name' => 'Amanuel Petros',
            'license_number' => 'ET-LAL-777',
            'expertise' => 'Church history & chanting',
            'availability_status' => 'available',
            'daily_rate' => 1800.00,
        ]);
        $guide->forceFill(['verification_status' => 'verified', 'admin_approval_status' => 'approved'])->save();

        $pkg = TourPackage::create([
            'guide_id' => $guide->guide_id,
            'title' => 'Lalibela 2-Day Pilgrimage Circuit',
            'slug' => 'lalibela-2-day-pilgrimage',
            'duration_days' => 2,
            'price' => 3800.00,
            'max_group_size' => 10,
            'difficulty_level' => 'easy',
            'description' => 'Touring the 11 rock-hewn monolithic churches with candlelight chanting ceremony.',
            'itinerary' => [
                ['day' => 1, 'title' => 'Northern Group Churches', 'description' => 'Visit Bet Medhane Alem and Bet Maryam.'],
                ['day' => 2, 'title' => 'Bet Giyorgis & Southern Group', 'description' => 'Cross-shaped church and afternoon blessing.'],
            ],
            'included' => ['English Tour Guide', 'Site Entry Passports'],
            'excluded' => ['Shoe-keeper gratuities'],
            'is_active' => true,
        ]);

        $response = $this->get(route('tour-guides.show', $guide));
        $response->assertOk();
        $response->assertSee('Lalibela 2-Day Pilgrimage Circuit');
        $response->assertSee('Northern Group Churches');
        $response->assertSee('3,800.00 ETB');
    }
}
