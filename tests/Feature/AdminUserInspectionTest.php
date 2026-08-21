<?php

namespace Tests\Feature;

use App\Models\Administrator;
use App\Models\Destination;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\Tourist;
use App\Models\TourGuide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserInspectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_directory_and_filter(): void
    {
        $adminUser = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        Administrator::create(['user_id' => $adminUser->user_id]);

        $touristUser = User::factory()->create(['role' => 'tourist', 'email' => 'traveler@example.com']);
        Tourist::create(['user_id' => $touristUser->user_id, 'full_name' => 'John Traveler', 'nationality' => 'Ethiopian']);

        $response = $this->actingAs($adminUser)->get(route('admin.users.index', ['q' => 'traveler']));

        $response->assertOk();
        $response->assertSee('traveler@example.com');
        $response->assertSee('John Traveler');
    }

    public function test_admin_can_inspect_360_degree_profile_for_all_roles(): void
    {
        $adminUser = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        Administrator::create(['user_id' => $adminUser->user_id]);

        $officerUser = User::factory()->create(['role' => 'tourism_bureau_officer', 'email' => 'officer@example.com']);
        $officer = TourismBureauOfficer::create(['user_id' => $officerUser->user_id]);

        $dest = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara Region',
            'description' => 'Historic capital',
        ]);

        // 1. Tour Guide
        $guideUser = User::factory()->create(['role' => 'tour_guide', 'email' => 'guide@example.com']);
        $guide = TourGuide::create([
            'user_id' => $guideUser->user_id,
            'full_name' => 'Abebe Bikila',
            'license_number' => 'ET-GND-999',
            'expertise' => 'Highland trekking and royal history',
            'primary_destination_id' => $dest->destination_id,
            'daily_rate' => 1500.00,
            'availability_status' => 'available',
            'years_of_experience' => 7,
            'languages' => ['Amharic', 'English', 'French'],
            'specialties' => ['Simien Trekking', 'Castles'],
        ]);
        $guide->forceFill(['verification_status' => 'verified'])->save();

        $resGuide = $this->actingAs($adminUser)->get(route('admin.users.show', $guideUser));
        $resGuide->assertOk();
        $resGuide->assertSee('Abebe Bikila');
        $resGuide->assertSee('ET-GND-999');
        $resGuide->assertSee('Simien Trekking');

        // 2. Service Provider
        $providerUser = User::factory()->create(['role' => 'service_provider', 'email' => 'hotel@example.com']);
        ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Goha Grand Hotel',
            'provider_type' => 'hotel',
            'status' => 'approved',
            'verification_notes' => '5-star certified',
        ]);

        $resProvider = $this->actingAs($adminUser)->get(route('admin.users.show', $providerUser));
        $resProvider->assertOk();
        $resProvider->assertSee('Goha Grand Hotel');
        $resProvider->assertSee('5-star certified');

        // 3. Tourism Bureau Officer
        $resOfficer = $this->actingAs($adminUser)->get(route('admin.users.show', $officerUser));
        $resOfficer->assertOk();
        $resOfficer->assertSee('Tourism Bureau Officer #'.$officer->officer_id);

        // 4. Tourist
        $touristUser = User::factory()->create(['role' => 'tourist', 'email' => 'tourist@example.com']);
        Tourist::create(['user_id' => $touristUser->user_id, 'full_name' => 'Sara Connor', 'nationality' => 'Canada']);

        $resTourist = $this->actingAs($adminUser)->get(route('admin.users.show', $touristUser));
        $resTourist->assertOk();
        $resTourist->assertSee('Sara Connor');
        $resTourist->assertSee('Canada');
    }

    public function test_non_admin_cannot_access_user_inspection(): void
    {
        $touristUser = User::factory()->create(['role' => 'tourist']);
        Tourist::create(['user_id' => $touristUser->user_id, 'full_name' => 'Test Tourist', 'nationality' => 'Ethiopian']);

        $targetUser = User::factory()->create(['role' => 'tour_guide']);

        $this->actingAs($touristUser)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($touristUser)->get(route('admin.users.show', $targetUser))->assertForbidden();
    }
}
