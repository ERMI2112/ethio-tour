<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\EventTicketType;
use App\Models\HeritageSite;
use App\Models\HotelRoomType;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationDiscoveryHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_destination_listing_renders_with_rich_badges_and_search(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $gondar = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara Region',
            'description' => 'Historic imperial city of castles and royal enclosures.',
            'latitude' => 12.6030000,
            'longitude' => 37.4520000,
        ]);

        $lalibela = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Lalibela',
            'location' => 'Amhara Region',
            'description' => 'Famed for rock-hewn monolithic churches.',
            'latitude' => 12.0320000,
            'longitude' => 39.0430000,
        ]);

        HeritageSite::create([
            'destination_id' => $gondar->destination_id,
            'heritage_type' => 'Fasil Ghebbi Royal Enclosure',
            'opening_hours' => '08:30 - 17:30',
            'entrance_fee' => 200,
        ]);

        $response = $this->get(route('destinations.index'));
        $response->assertOk()
            ->assertSee('Discover Ethiopia')
            ->assertSee('Gondar')
            ->assertSee('Lalibela')
            ->assertSee('heritage site');

        // Test search
        $searchResponse = $this->get(route('destinations.index', ['q' => 'Gondar']));
        $searchResponse->assertOk()
            ->assertSee('Gondar')
            ->assertDontSee('Lalibela');
    }

    public function test_destination_detail_renders_editorial_hero_and_quick_nav(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar City',
            'location' => 'North Gondar, Amhara',
            'description' => 'The Camelot of Africa featuring 17th-century castle architecture.',
            'latitude' => 12.6000000,
            'longitude' => 37.4660000,
        ]);

        $response = $this->get(route('destinations.show', $destination));
        $response->assertOk()
            ->assertSee('Gondar City')
            ->assertSee('The Camelot of Africa')
            ->assertSee('Plan a Trip to Gondar City')
            ->assertSee('Explore on Map')
            ->assertSee('Quick Navigation')
            ->assertSee('#heritage-sites')
            ->assertSee('#accommodations');
    }

    public function test_heritage_and_museum_sections_are_properly_integrated(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $gondar = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara',
            'description' => 'Castle city.',
        ]);

        $other = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Axum',
            'location' => 'Tigray',
            'description' => 'Ancient obelisks.',
        ]);

        $gondarSite = HeritageSite::create([
            'destination_id' => $gondar->destination_id,
            'heritage_type' => 'Fasilides Bath',
            'opening_hours' => '09:00 - 17:00',
            'entrance_fee' => 150.00,
        ]);

        $axumSite = HeritageSite::create([
            'destination_id' => $other->destination_id,
            'heritage_type' => 'Great Stele of Axum',
            'opening_hours' => '08:00 - 18:00',
            'entrance_fee' => 200.00,
        ]);

        $response = $this->get(route('destinations.show', $gondar));
        $response->assertOk()
            ->assertSee('Fasilides Bath')
            ->assertSee('150.00 ETB')
            ->assertDontSee('Great Stele of Axum')
            ->assertSee('Museums & Cultural Centers')
            ->assertSee(route('museums.index', ['q' => 'Gondar']));
    }

    public function test_verified_hotels_restaurants_and_transport_are_partitioned(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara',
            'description' => 'Historic city.',
        ]);

        $hotelUser = User::factory()->create(['role' => 'service_provider', 'is_active' => true]);
        $hotelProvider = ServiceProvider::create([
            'user_id' => $hotelUser->user_id,
            'business_name' => 'Goha Hotel Gondar',
            'provider_type' => 'hotel',
            'status' => 'approved',
            'verification_status' => 'verified',
        ]);
        $hotelCat = Category::create(['category_name' => 'Accommodation']);
        $hotelService = TourismService::create([
            'provider_id' => $hotelProvider->provider_id,
            'category_id' => $hotelCat->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Panoramic Hillside Suite',
            'price' => 3200.00,
            'description' => 'Luxury suite overlooking the royal castles.',
        ]);
        HotelRoomType::create([
            'service_id' => $hotelService->service_id,
            'capacity' => 2,
            'amenities' => ['Balcony', 'Wi-Fi', 'Breakfast'],
        ]);

        $restaurantUser = User::factory()->create(['role' => 'service_provider', 'is_active' => true]);
        $restaurantProvider = ServiceProvider::create([
            'user_id' => $restaurantUser->user_id,
            'business_name' => 'Four Sisters Restaurant',
            'provider_type' => 'restaurant',
            'status' => 'approved',
            'verification_status' => 'verified',
        ]);
        $diningCat = Category::create(['category_name' => 'Dining']);
        TourismService::create([
            'provider_id' => $restaurantProvider->provider_id,
            'category_id' => $diningCat->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Traditional Buffet Dinner',
            'price' => 450.00,
            'description' => 'Authentic Ethiopian dining with cultural coffee ceremony.',
        ]);

        $transportUser = User::factory()->create(['role' => 'service_provider', 'is_active' => true]);
        $transportProvider = ServiceProvider::create([
            'user_id' => $transportUser->user_id,
            'business_name' => 'Simien 4x4 Rentals',
            'provider_type' => 'transportation_car_rental',
            'status' => 'approved',
            'verification_status' => 'verified',
        ]);
        $transCat = Category::create(['category_name' => 'Transport']);
        TourismService::create([
            'provider_id' => $transportProvider->provider_id,
            'category_id' => $transCat->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Land Cruiser Safari 4WD',
            'price' => 2800.00,
            'description' => 'Heavy duty 4WD with experienced driver for mountain roads.',
        ]);

        $response = $this->get(route('destinations.show', $destination));
        $response->assertOk()
            ->assertSee('Goha Hotel Gondar')
            ->assertSee('Panoramic Hillside Suite')
            ->assertSee('3,200.00 ETB')
            ->assertSee('Balcony')
            ->assertSee('Four Sisters Restaurant')
            ->assertSee('Traditional Buffet Dinner')
            ->assertSee('450.00 ETB')
            ->assertSee('Simien 4x4 Rentals')
            ->assertSee('Land Cruiser Safari 4WD')
            ->assertSee('2,800.00 ETB');
    }

    public function test_unapproved_and_inactive_services_are_strictly_excluded(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara',
            'description' => 'Historic city.',
        ]);

        $category = Category::create(['category_name' => 'Accommodation']);

        // 1. Pending verification provider
        $pendingUser = User::factory()->create(['role' => 'service_provider', 'is_active' => true]);
        $pendingProvider = ServiceProvider::create([
            'user_id' => $pendingUser->user_id,
            'business_name' => 'Pending Hotel',
            'provider_type' => 'hotel',
            'status' => 'pending',
            'verification_status' => 'pending',
        ]);
        TourismService::create([
            'provider_id' => $pendingProvider->provider_id,
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Hidden Pending Room',
            'price' => 1000,
            'description' => 'Unapproved.',
        ]);

        // 2. Suspended provider
        $suspendedUser = User::factory()->create(['role' => 'service_provider', 'is_active' => true]);
        $suspendedProvider = ServiceProvider::create([
            'user_id' => $suspendedUser->user_id,
            'business_name' => 'Suspended Hotel',
            'provider_type' => 'hotel',
            'status' => 'suspended',
            'verification_status' => 'verified',
        ]);
        TourismService::create([
            'provider_id' => $suspendedProvider->provider_id,
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Hidden Suspended Room',
            'price' => 1000,
            'description' => 'Suspended.',
        ]);

        // 3. Inactive User provider
        $inactiveUser = User::factory()->create(['role' => 'service_provider', 'is_active' => false]);
        $inactiveProvider = ServiceProvider::create([
            'user_id' => $inactiveUser->user_id,
            'business_name' => 'Inactive Hotel',
            'provider_type' => 'hotel',
            'status' => 'approved',
            'verification_status' => 'verified',
        ]);
        TourismService::create([
            'provider_id' => $inactiveProvider->provider_id,
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Hidden Inactive Room',
            'price' => 1000,
            'description' => 'Inactive user.',
        ]);

        $response = $this->get(route('destinations.show', $destination));
        $response->assertOk()
            ->assertDontSee('Hidden Pending Room')
            ->assertDontSee('Hidden Suspended Room')
            ->assertDontSee('Hidden Inactive Room');
    }

    public function test_cultural_events_show_upcoming_dates_and_ticket_starting_prices(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara',
            'description' => 'Historic city.',
        ]);

        $eventUser = User::factory()->create(['role' => 'service_provider', 'is_active' => true]);
        $eventProvider = ServiceProvider::create([
            'user_id' => $eventUser->user_id,
            'business_name' => 'Gondar Cultural Events',
            'provider_type' => 'event_organizer',
            'status' => 'approved',
            'verification_status' => 'verified',
        ]);
        $eventCat = Category::create(['category_name' => 'Events']);
        $eventService = TourismService::create([
            'provider_id' => $eventProvider->provider_id,
            'category_id' => $eventCat->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Timkat Festival Celebration',
            'price' => 0,
            'description' => 'Epiphany festival celebration.',
        ]);

        $event = CulturalEvent::create([
            'destination_id' => $destination->destination_id,
            'provider_id' => $eventProvider->provider_id,
            'service_id' => $eventService->service_id,
            'event_name' => 'Timkat Gondar Grand Procession',
            'description' => 'Annual baptism and tabot ceremony.',
            'event_date' => now()->addDays(20),
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'venue' => 'Fasilides Bath Complex',
            'status' => 'published',
        ]);

        EventTicketType::create([
            'event_id' => $event->event_id,
            'name' => 'General Visitor',
            'price' => 300.00,
            'quantity' => 200,
            'status' => 'active',
        ]);

        $response = $this->get(route('destinations.show', $destination));
        $response->assertOk()
            ->assertSee('Timkat Gondar Grand Procession')
            ->assertSee('Fasilides Bath Complex')
            ->assertSee('From')
            ->assertSee('300.00 ETB');
    }

    public function test_service_ratings_derive_honestly_from_real_reviews(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara',
            'description' => 'Historic city.',
        ]);

        $hotelUser = User::factory()->create(['role' => 'service_provider', 'is_active' => true]);
        $hotelProvider = ServiceProvider::create([
            'user_id' => $hotelUser->user_id,
            'business_name' => 'Tayitu Hotel',
            'provider_type' => 'hotel',
            'status' => 'approved',
            'verification_status' => 'verified',
        ]);
        $hotelCat = Category::create(['category_name' => 'Accommodation']);
        $service = TourismService::create([
            'provider_id' => $hotelProvider->provider_id,
            'category_id' => $hotelCat->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'Classic Room',
            'price' => 1800.00,
            'description' => 'Comfortable stay.',
        ]);

        $touristUser = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $touristUser->user_id, 'full_name' => 'Eleni Tadesse', 'nationality' => 'Ethiopian']);

        $booking = Booking::create([
            'tourist_id' => $tourist->tourist_id,
            'service_id' => $service->service_id,
            'status' => 'completed',
            'total_amount' => 1800.00,
            'currency' => 'ETB',
        ]);

        Review::create([
            'booking_id' => $booking->booking_id,
            'tourist_id' => $tourist->tourist_id,
            'rating' => 5,
            'comment' => 'Outstanding stay and warm hospitality.',
            'review_date' => now(),
        ]);

        $response = $this->get(route('destinations.show', $destination));
        $response->assertOk()
            ->assertSee('Tayitu Hotel')
            ->assertSee('★ 5');
    }

    public function test_tour_guide_advisory_section_links_to_guide_directory(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara',
            'description' => 'Historic city.',
        ]);

        $response = $this->get(route('destinations.show', $destination));
        $response->assertOk()
            ->assertSee('Bureau-Verified Tour Guides')
            ->assertSee('Browse Verified Tour Guides')
            ->assertSee(route('tour-guides.index'));
    }

    public function test_smart_trip_cta_links_to_itinerary_builder(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara',
            'description' => 'Historic city.',
        ]);

        $response = $this->get(route('destinations.show', $destination));
        $response->assertOk()
            ->assertSee('Plan a Trip to Gondar')
            ->assertSee('Build a Custom Itinerary')
            ->assertSee(route('smart-trip.index'));
    }

    public function test_empty_states_for_all_verticals_are_honest(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $emptyDestination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Semien Mountains',
            'location' => 'Amhara',
            'description' => 'Mountain national park with endemic wildlife.',
        ]);

        $response = $this->get(route('destinations.show', $emptyDestination));
        $response->assertOk()
            ->assertSee('No heritage sites listed yet')
            ->assertSee('No hotels listed yet')
            ->assertSee('No restaurants listed yet')
            ->assertSee('Browse Transport Directory')
            ->assertSee('No upcoming events scheduled');
    }

    public function test_breadcrumbs_follow_canonical_destinations_pattern(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'location' => 'Amhara',
            'description' => 'Historic city.',
        ]);

        $response = $this->get(route('destinations.show', $destination));
        $response->assertOk()
            ->assertSee('Home')
            ->assertSee('Destinations')
            ->assertSee(route('destinations.index'))
            ->assertDontSee('Explore Ethiopia /');
    }
}
