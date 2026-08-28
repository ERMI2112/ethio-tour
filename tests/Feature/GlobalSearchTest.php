<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\HeritageSite;
use App\Models\MuseumInformation;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_search_page_and_normalized_results_are_available(): void
    {
        [$destination, $category] = $this->catalog();
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $providerUser->user_id, 'business_name' => 'Gondar Hotel', 'provider_type' => 'hotel', 'status' => 'approved']);
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Gondar Castle Hotel', 'price' => 100, 'description' => 'Stay near the castle', 'latitude' => 12.6, 'longitude' => 37.46]);

        $this->get(route('search', ['q' => 'castle']))->assertOk()->assertSee('Gondar Castle Hotel')->assertSee(route('tourism-services.show', $service), false)->assertSee('View on map');
        $this->get(route('search', ['q' => 'Gondar', 'type' => 'destinations']))->assertOk()->assertSee('Gondar');
    }

    public function test_search_covers_public_heritage_museum_guide_and_event_results(): void
    {
        [$destination, $category, $officer] = $this->catalog();
        HeritageSite::create(['destination_id' => $destination->destination_id, 'heritage_type' => 'Royal Castle', 'opening_hours' => '08:00-17:00', 'entrance_fee' => 10]);
        MuseumInformation::create(['officer_id' => $officer->officer_id, 'museum_name' => 'Gondar Museum', 'description' => 'Castle history collection', 'location' => 'Gondar', 'opening_hours' => '09:00-17:00']);
        $guideUser = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create(['user_id' => $guideUser->user_id, 'license_number' => 'LIC-SEARCH', 'expertise' => 'Castle history', 'availability_status' => 'available', 'daily_rate' => 100]);
        $guide->forceFill(['verification_status' => 'verified', 'admin_approval_status' => 'approved'])->save();
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $providerUser->user_id, 'business_name' => 'Culture Events', 'provider_type' => 'event_organizer', 'status' => 'approved']);
        CulturalEvent::create(['destination_id' => $destination->destination_id, 'provider_id' => $provider->provider_id, 'event_name' => 'Castle Festival', 'description' => 'Cultural celebration', 'event_date' => today()->addDays(10), 'venue' => 'Royal Castle', 'status' => 'published']);

        $this->get(route('search', ['q' => 'Castle', 'type' => 'heritage']))->assertOk()->assertSee('Royal Castle');
        $this->get(route('search', ['q' => 'Museum', 'type' => 'museums']))->assertOk()->assertSee('Gondar Museum');
        $this->get(route('search', ['q' => 'history', 'type' => 'guides']))->assertOk()->assertSee('Tour Guide Profile');
        $this->get(route('search', ['q' => 'Festival', 'type' => 'events']))->assertOk()->assertSee('Castle Festival');
    }

    public function test_search_excludes_unapproved_provider_content_and_private_fields(): void
    {
        [$destination, $category] = $this->catalog();
        $user = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Pending Hotel', 'provider_type' => 'hotel', 'status' => 'pending']);
        TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Hidden Hotel', 'price' => 100, 'description' => 'Should not be searchable']);

        $this->get(route('search', ['q' => 'Hidden Hotel']))->assertOk()->assertDontSee('Should not be searchable')->assertDontSee($user->email)->assertDontSee('verification_status')->assertDontSee('provider_id');
    }

    public function test_destination_category_and_event_date_filters_are_applied(): void
    {
        [$destination, $category] = $this->catalog();
        $otherDestination = $this->destination('Other City');
        $user = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Event Organizer', 'provider_type' => 'event_organizer', 'status' => 'approved']);
        CulturalEvent::create(['destination_id' => $destination->destination_id, 'provider_id' => $provider->provider_id, 'event_name' => 'Filtered Festival', 'description' => 'Festival', 'event_date' => '2026-09-10', 'venue' => 'Square', 'status' => 'published']);
        CulturalEvent::create(['destination_id' => $otherDestination->destination_id, 'provider_id' => $provider->provider_id, 'event_name' => 'Other Festival', 'description' => 'Festival', 'event_date' => '2026-09-11', 'venue' => 'Square', 'status' => 'published']);

        $this->get(route('search', ['type' => 'events', 'destination' => $destination->destination_id, 'date' => '2026-09-10']))->assertOk()->assertSee('Filtered Festival')->assertDontSee('Other Festival');
        $this->get(route('search', ['type' => 'services', 'category' => $category->category_id]))->assertOk();
    }

    public function test_search_paginates_results_and_handles_empty_queries(): void
    {
        for ($index = 1; $index <= 25; $index++) {
            $this->destination('Place '.$index);
        }

        $this->get(route('search', ['q' => 'Place']))->assertOk()->assertSee('25 results')->assertSee('page=2');
        $this->get(route('search', ['q' => 'Nothing matches this']))->assertOk()->assertSee('No public results found');
    }

    public function test_rating_filter_uses_derived_public_reviews_and_invalid_dates_are_safe(): void
    {
        [$destination, $category] = $this->catalog();
        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $providerUser->user_id, 'business_name' => 'Rated Hotel', 'provider_type' => 'hotel', 'status' => 'approved']);
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Rated Hotel', 'price' => 100, 'description' => 'Highly rated stay']);
        $tourist = Tourist::create(['user_id' => User::factory()->create()->user_id, 'full_name' => 'Search Tourist', 'nationality' => 'Ethiopian']);
        $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $service->service_id, 'status' => 'completed', 'booking_date' => now()]);
        Review::create(['booking_id' => $booking->booking_id, 'tourist_id' => $tourist->tourist_id, 'rating' => 5, 'comment' => 'Excellent stay and service.', 'review_date' => today()]);

        $this->get(route('search', ['type' => 'hotels', 'rating' => 5]))->assertOk()->assertSee('Rated Hotel')->assertSee('5.0 / 5 rating');
        $this->get(route('search', ['type' => 'hotels', 'rating' => 5.1]))->assertOk()->assertSee('Rated Hotel');
        $this->get(route('search', ['type' => 'events', 'date' => 'not-a-date']))->assertOk();
    }

    private function catalog(): array
    {
        $officer = TourismBureauOfficer::create(['user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar', 'location' => 'Amhara', 'description' => 'Historic city']);
        $category = Category::create(['category_name' => 'Accommodation']);

        return [$destination, $category, $officer];
    }

    private function destination(string $name): Destination
    {
        $officer = TourismBureauOfficer::first() ?: TourismBureauOfficer::create(['user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id]);

        return Destination::create(['officer_id' => $officer->officer_id, 'name' => $name, 'location' => 'Amhara', 'description' => 'A public destination']);
    }
}
