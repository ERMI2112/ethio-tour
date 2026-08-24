<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Review;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TourGuideProfessionalFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_can_upload_profile_image_and_update_professional_fields(): void
    {
        Storage::fake('public');

        /** @var User $user */
        $user = User::factory()->create(['role' => 'tour_guide']);
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);
        $destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Gondar, Amhara',
            'description' => 'Imperial Capital',
        ]);

        $guide = TourGuide::create([
            'user_id' => $user->user_id,
            'license_number' => 'TG-TEST-001',
            'expertise' => 'Historical tours',
            'availability_status' => 'available',
            'daily_rate' => 2000,
        ]);

        $image = UploadedFile::fake()->create('guide_avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->put(route('tour-guide.profile.update'), [
            'full_name' => 'Yared Tadesse',
            'expertise' => 'Expert in Gondarine history, castles, and monasteries',
            'bio' => 'Professional certified tour guide with passion for storytelling.',
            'phone_number' => '+251 91 184 2901',
            'languages' => ['Amharic', 'English', 'French'],
            'years_of_experience' => 9,
            'primary_destination_id' => $destination->destination_id,
            'specialties' => ['UNESCO Heritage', 'Highland Trekking'],
            'daily_rate' => 2500,
            'availability_status' => 'available',
            'profile_image' => $image,
        ]);

        $response->assertRedirect(route('tour-guide.profile'));
        $response->assertSessionHas('success');

        $guide->refresh();
        $this->assertSame('Yared Tadesse', $guide->full_name);
        $this->assertSame(9, $guide->years_of_experience);
        $this->assertSame('+251 91 184 2901', $guide->phone_number);
        $this->assertSame('2500.00', (string) $guide->daily_rate);
        $this->assertSame($destination->destination_id, $guide->primary_destination_id);
        $this->assertContains('French', $guide->languagesList());
        $this->assertContains('UNESCO Heritage', $guide->specialtiesList());
        $this->assertNotNull($guide->profile_image);
        Storage::disk('public')->assertExists($guide->profile_image);
    }

    public function test_guide_can_view_reviews_workspace_with_aggregated_metrics(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $user->user_id,
            'license_number' => 'TG-REV-001',
            'expertise' => 'Historical tours',
            'availability_status' => 'available',
        ]);

        /** @var User $touristUser */
        $touristUser = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $touristUser->user_id, 'full_name' => 'Sara Jenkins', 'nationality' => 'British']);

        $booking = Booking::create([
            'tourist_id' => $tourist->tourist_id,
            'guide_id' => $guide->guide_id,
            'status' => 'completed',
            'booking_date' => today(),
            'total_amount' => 4000,
        ]);

        Review::create([
            'booking_id' => $booking->booking_id,
            'tourist_id' => $tourist->tourist_id,
            'rating' => 5,
            'comment' => 'Incredible guide! Best tour in Gondar.',
            'review_date' => today(),
        ]);

        $response = $this->actingAs($user)->get(route('tour-guide.reviews'));
        $response->assertOk()
            ->assertSee('Ratings & Reviews')
            ->assertSee('Overall Rating')
            ->assertSee('5.0')
            ->assertSee('Incredible guide! Best tour in Gondar.')
            ->assertSee('Sara Jenkins');
    }

    public function test_guide_can_view_earnings_workspace_with_financial_ledger(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $user->user_id,
            'license_number' => 'TG-EARN-001',
            'expertise' => 'Historical tours',
            'availability_status' => 'available',
            'daily_rate' => 2000,
        ]);

        /** @var User $touristUser */
        $touristUser = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $touristUser->user_id, 'full_name' => 'Michael Brown', 'nationality' => 'American']);

        Booking::create([
            'tourist_id' => $tourist->tourist_id,
            'guide_id' => $guide->guide_id,
            'status' => 'completed',
            'booking_date' => today()->subDays(2),
            'total_amount' => 6000,
        ]);

        Booking::create([
            'tourist_id' => $tourist->tourist_id,
            'guide_id' => $guide->guide_id,
            'status' => 'confirmed',
            'booking_date' => today()->addDays(3),
            'total_amount' => 4000,
        ]);

        $response = $this->actingAs($user)->get(route('tour-guide.earnings'));
        $response->assertOk()
            ->assertSee('Earnings & Payouts')
            ->assertSee('6,000.00') // Lifetime completed
            ->assertSee('4,000.00') // Pending/upcoming
            ->assertSee('Completed Tours Ledger')
            ->assertSee('Michael Brown');
    }

    public function test_guide_can_view_and_update_settings(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $user->user_id,
            'license_number' => 'TG-SET-001',
            'expertise' => 'Historical tours',
            'availability_status' => 'available',
            'daily_rate' => 2000,
        ]);

        $this->actingAs($user)->get(route('tour-guide.settings'))
            ->assertOk()
            ->assertSee('Operational Settings')
            ->assertSee('2000');

        $this->actingAs($user)->put(route('tour-guide.settings.update'), [
            'daily_rate' => 2800,
            'phone_number' => '+251 92 333 4444',
            'availability_status' => 'unavailable',
        ])->assertRedirect(route('tour-guide.settings'));

        $guide->refresh();
        $this->assertSame('2800.00', (string) $guide->daily_rate);
        $this->assertSame('+251 92 333 4444', $guide->phone_number);
        $this->assertSame('unavailable', $guide->availability_status);
    }
}
