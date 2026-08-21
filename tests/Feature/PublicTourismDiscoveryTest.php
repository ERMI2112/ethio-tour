<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Destination;
use App\Models\HeritageSite;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTourismDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_visitor_can_view_destination_listing_and_detail_with_related_information(): void
    {
        [$destination, $heritageSite, $service] = $this->createPublicTourismData();

        $this->get('/destinations')->assertOk()->assertSee($destination->name)->assertSee($destination->location);
        $this->get(route('destinations.show', $destination))->assertOk()
            ->assertSee($destination->description)
            ->assertSee($heritageSite->heritage_type)
            ->assertSee($service->service_name);
    }

    public function test_heritage_site_listing_and_detail_preserve_the_destination_relationship(): void
    {
        [$destination, $heritageSite] = $this->createPublicTourismData();

        $this->get('/heritage-sites')->assertOk()->assertSee($heritageSite->heritage_type)->assertSee($destination->name);
        $this->get(route('heritage-sites.show', $heritageSite))->assertOk()
            ->assertSee($heritageSite->opening_hours)
            ->assertSee(number_format($heritageSite->entrance_fee, 2))
            ->assertSee($destination->name);
    }

    public function test_categories_and_tourism_services_can_be_filtered_publicly(): void
    {
        [$destination, , $service, $category] = $this->createPublicTourismData();
        $otherCategory = Category::create(['category_name' => 'Vehicle']);
        $otherService = TourismService::create([
            'provider_id' => $service->provider_id,
            'category_id' => $otherCategory->category_id,
            'destination_id' => $destination->destination_id,
            'service_name' => 'City Vehicle',
            'price' => 500,
            'description' => 'Transport service',
        ]);

        $this->get('/categories')->assertOk()->assertSee($category->category_name)->assertSee($otherCategory->category_name);
        $this->get('/tourism-services?category='.$category->category_id)->assertOk()->assertSee($service->service_name)->assertDontSee($otherService->service_name);
        $this->get('/tourism-services?destination='.$destination->destination_id)->assertOk()->assertSee($service->service_name)->assertSee($otherService->service_name);
    }

    public function test_public_search_empty_results_and_missing_records_are_handled_safely(): void
    {
        $this->get('/destinations?q=not-a-real-destination')->assertOk()->assertSee('No destinations or spaces found');
        $this->get('/heritage-sites?q=not-a-real-site')->assertOk()->assertSee('No heritage sites found');
        $this->get('/tourism-services?q=not-a-real-service')->assertOk()->assertSee('No tourism services found');
        $this->get('/destinations/999999')->assertNotFound();
    }

    public function test_public_discovery_does_not_expose_management_or_booking_routes(): void
    {
        $this->get('/provider/services')->assertNotFound();
        $this->get('/admin/destinations')->assertNotFound();
        $this->get('/bureau/destinations')->assertNotFound();
        $this->get('/bookings')->assertNotFound();
    }

    private function createPublicTourismData(): array
    {
        $officer = TourismBureauOfficer::create(['user_id' => User::factory()->create(['role' => 'tourism_bureau_officer'])->user_id]);
        $provider = ServiceProvider::create([
            'user_id' => User::factory()->create(['role' => 'service_provider'])->user_id,
            'business_name' => 'Gondar Guest House',
            'provider_type' => 'hotel',
            'status' => 'approved',
        ]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Fasil Ghebbi', 'location' => 'Gondar', 'description' => 'Historic royal enclosure.']);
        $heritageSite = HeritageSite::create(['destination_id' => $destination->destination_id, 'heritage_type' => 'Royal Castle', 'opening_hours' => '08:00-17:00', 'entrance_fee' => 200]);
        $category = Category::create(['category_name' => 'Room']);
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Historic Stay', 'price' => 1200, 'description' => 'Accommodation near the destination.']);

        return [$destination, $heritageSite, $service, $category];
    }
}
