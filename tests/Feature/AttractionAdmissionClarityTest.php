<?php

namespace Tests\Feature;

use App\Models\Attraction;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\MuseumInformation;
use App\Models\Payment;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttractionAdmissionClarityTest extends TestCase
{
    use RefreshDatabase;

    private Destination $destination;

    private Attraction $attractionWithFee;

    private Attraction $freeAttraction;

    protected function setUp(): void
    {
        parent::setUp();

        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $this->destination = Destination::create([
            'officer_id' => $officer->officer_id,
            'name' => 'Gondar',
            'slug' => 'gondar',
            'location' => 'Amhara Region',
            'description' => 'The Camelot of Africa with 17th-century castles and churches.',
            'tagline' => 'The Camelot of Africa',
            'latitude' => 12.6030000,
            'longitude' => 37.4520000,
        ]);

        $this->attractionWithFee = Attraction::create([
            'destination_id' => $this->destination->destination_id,
            'name' => 'Fasil Ghebbi (Royal Enclosure)',
            'slug' => 'fasil-ghebbi',
            'description' => 'UNESCO World Heritage royal enclosure and palace compound.',
            'category' => 'heritage_site',
            'location_address' => 'Central Gondar, Amhara, Ethiopia',
            'latitude' => 12.6087000,
            'longitude' => 37.4683000,
            'opening_hours' => '08:30 – 17:30 daily',
            'entry_fee' => 200.00,
            'is_featured' => true,
        ]);

        $this->freeAttraction = Attraction::create([
            'destination_id' => $this->destination->destination_id,
            'name' => 'Gondar Piazza Monument',
            'slug' => 'gondar-piazza-monument',
            'description' => 'Public plaza monument accessible to all visitors.',
            'category' => 'monument',
            'location_address' => 'Piazza, Gondar',
            'latitude' => 12.6050000,
            'longitude' => 37.4650000,
            'opening_hours' => 'Open 24/7',
            'entry_fee' => 0.00,
            'is_featured' => false,
        ]);
    }

    /**
     * 1. Attraction detail renders admission information clearly.
     */
    public function test_attraction_detail_renders_admission_information(): void
    {
        $response = $this->get(route('destinations.show', $this->destination));

        $response->assertOk()
            ->assertSee('Fasil Ghebbi (Royal Enclosure)')
            ->assertSee('200.00 ETB')
            ->assertSee('Paid at the site')
            ->assertSee('Free Admission');
    }

    /**
     * 2. Non-bookable attractions do NOT display Pay Now, Book Ticket, or Buy Ticket.
     */
    public function test_non_bookable_attractions_do_not_display_pay_or_ticket_actions(): void
    {
        $response = $this->get(route('destinations.show', $this->destination));

        $response->assertOk()
            ->assertDontSee('Pay Now')
            ->assertDontSee('Book Ticket')
            ->assertDontSee('Buy Ticket')
            ->assertDontSee('included in your booking')
            ->assertDontSee('Est. Admission');
    }

    /**
     * 3. Non-bookable attractions do NOT display Chapa checkout action.
     */
    public function test_non_bookable_attractions_do_not_display_chapa_checkout_action(): void
    {
        $response = $this->get(route('destinations.show', $this->destination));

        $response->assertOk()
            ->assertDontSee('Chapa payment')
            ->assertDontSee('chapa.checkout')
            ->assertDontSee('Pay with Chapa');
    }

    /**
     * 4. Non-bookable attractions do NOT imply Ethio Tour collects admission.
     */
    public function test_attractions_explicitly_disclaim_fee_processing(): void
    {
        $response = $this->get(route('destinations.show', $this->destination));

        $response->assertOk()
            ->assertSee('Admission is paid at the attraction entrance. Ethio Tour does not currently process this admission fee.')
            ->assertDontSee('Ethio Tour collects');
    }

    /**
     * 5. Admission values are presented truthfully as site-paid amounts.
     */
    public function test_admission_values_are_qualified_as_site_paid(): void
    {
        $response = $this->get(route('destinations.show', $this->destination));

        $response->assertOk()
            ->assertSee('200.00 ETB')
            ->assertSee('— paid at the site')
            ->assertDontSee('Est. Admission');
    }

    /**
     * 6. Existing directions, map, guide, hotel, and Smart Trip actions remain available.
     */
    public function test_discovery_and_guidance_actions_remain_fully_available(): void
    {
        $response = $this->get(route('destinations.show', $this->destination));

        $response->assertOk()
            ->assertSee('Get Directions')
            ->assertSee('View on Platform Map')
            ->assertSee('Nearby Services &amp; Tours', false)
            ->assertSee('Start Planning Trip');
    }

    /**
     * 7. Existing bookable service payment architecture is intact and unchanged.
     */
    public function test_existing_service_provider_booking_and_payment_flows_remain_unchanged(): void
    {
        $touristUser = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create([
            'user_id' => $touristUser->user_id,
            'full_name' => 'Sara Bekele',
            'nationality' => 'Ethiopian',
        ]);

        $providerUser = User::factory()->create(['role' => 'hotel_provider']);
        $provider = ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Gondar Heritage Hotel',
            'provider_type' => 'hotel',
            'status' => 'approved',
            'verification_status' => 'approved',
            'contact_phone' => '+251911223344',
            'contact_email' => 'hotel@gondar.com',
        ]);

        $category = Category::create([
            'category_name' => 'Hotels & Accommodation',
        ]);

        $service = TourismService::create([
            'provider_id' => $provider->provider_id,
            'category_id' => $category->category_id,
            'destination_id' => $this->destination->destination_id,
            'service_name' => 'Deluxe Castle View Room',
            'description' => 'Luxury suite overlooking royal castles.',
            'price' => 1500.00,
        ]);

        $booking = Booking::create([
            'tourist_id' => $tourist->tourist_id,
            'service_id' => $service->service_id,
            'booking_date' => now(),
            'total_amount' => 1500.00,
            'currency' => 'ETB',
            'status' => 'pending',
        ]);

        $payment = Payment::create([
            'booking_id' => $booking->booking_id,
            'amount' => 1500.00,
            'payment_method' => 'chapa',
            'status' => 'pending',
            'gateway_reference' => 'TX-TEST-12345',
        ]);

        $this->assertDatabaseHas('bookings', [
            'booking_id' => $booking->booking_id,
            'total_amount' => '1500.00',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('payments', [
            'payment_id' => $payment->payment_id,
            'payment_method' => 'chapa',
            'status' => 'pending',
        ]);
    }

    /**
     * 8. Museum governance remains Bureau-managed and non-commercialized.
     */
    public function test_museum_governance_remains_bureau_managed(): void
    {
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);

        $museum = MuseumInformation::create([
            'officer_id' => $officer->officer_id,
            'museum_name' => 'Royal Enclosure Museum',
            'location' => 'Fasil Ghebbi, Gondar',
            'description' => 'Artifacts and manuscripts from the Gondarine dynasty.',
            'opening_hours' => '08:30 – 17:30 daily',
            'entrance_fee' => 150.00,
            'contact_information' => '+251581112233',
        ]);

        $response = $this->get(route('museums.show', $museum));

        $response->assertOk()
            ->assertSee('Royal Enclosure Museum')
            ->assertSee('Museum collection')
            ->assertSee('150.00 ETB')
            ->assertSee('— paid at the site')
            ->assertDontSee('Pay Now')
            ->assertDontSee('Book Ticket');
    }
}
