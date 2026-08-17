<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\EventReservation;
use App\Models\EventTicketType;
use App\Models\RestaurantReservation;
use App\Models\TourismService;
use App\Models\TransportationReservation;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalStabilizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_hotel_create_and_edit_pages_render_forms_and_service_creation_persists_room_type(): void
    {
        $hotel = User::where('email', 'hotel@test.com')->firstOrFail();
        $category = Category::where('category_name', 'Accommodation')->firstOrFail();
        $destination = Destination::where('name', 'Gondar')->firstOrFail();

        $this->actingAs($hotel)->get(route('hotel.services.create'))
            ->assertOk()
            ->assertSee('name="service_name"', false)
            ->assertSee('Select a category')
            ->assertDontSee("@include('hotel.services._form");

        $this->actingAs($hotel)->post(route('hotel.services.store'), [
            'service_name' => 'UAT Hotel Stabilization Room',
            'price' => 1750,
            'description' => 'A room created by the stabilization regression test.',
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
            'capacity' => 2,
            'amenities' => ['Wi-Fi', 'TV'],
        ])->assertRedirect(route('hotel.services.index'));

        $service = TourismService::where('service_name', 'UAT Hotel Stabilization Room')->firstOrFail();
        $this->assertDatabaseHas('hotel_room_types', ['service_id' => $service->service_id, 'capacity' => 2]);

        $this->actingAs($hotel)->get(route('hotel.services.edit', $service))
            ->assertOk()
            ->assertSee('name="service_name"', false)
            ->assertDontSee("@include('hotel.services._form");
    }

    public function test_restaurant_service_form_and_reservation_portal_render_and_reservation_can_be_accepted(): void
    {
        $restaurant = User::where('email', 'restaurant@test.com')->firstOrFail();
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $category = Category::where('category_name', 'Dining & Reservations')->firstOrFail();
        $destination = Destination::where('name', 'Gondar')->firstOrFail();

        $this->actingAs($restaurant)->get(route('restaurant.services.create'))
            ->assertOk()
            ->assertSee('name="service_name"', false)
            ->assertSee('Select a category')
            ->assertDontSee("@include('restaurant.services._form");

        $this->actingAs($restaurant)->post(route('restaurant.services.store'), [
            'service_name' => 'UAT Restaurant Stabilization Service',
            'price' => 250,
            'description' => 'A restaurant service created by the stabilization regression test.',
            'category_id' => $category->category_id,
            'destination_id' => $destination->destination_id,
        ])->assertRedirect(route('restaurant.services.index'));

        $service = TourismService::where('service_name', 'UAT Restaurant Stabilization Service')->firstOrFail();
        $this->actingAs($restaurant)->get(route('restaurant.services.edit', $service))
            ->assertOk()
            ->assertSee('name="service_name"', false)
            ->assertDontSee("@include('restaurant.services._form");

        $date = Carbon::tomorrow()->toDateString();
        $this->actingAs($tourist)->post(route('tourist.restaurant-reservations.store', $service), [
            'reservation_date' => $date,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'guest_count' => 2,
        ])->assertRedirect();

        $booking = Booking::latest('booking_id')->firstOrFail();
        $this->actingAs($restaurant)->get(route('restaurant.reservations.index'))
            ->assertOk()
            ->assertDontSee('@endif@endif', false);

        $this->actingAs($restaurant)->patch(route('restaurant.reservations.accept', $booking))
            ->assertRedirect();

        $this->assertSame('accepted', $booking->fresh()->status);
        $this->assertNotNull(RestaurantReservation::where('booking_id', $booking->booking_id)->value('table_id'));
    }

    public function test_transportation_service_selector_and_vehicle_creation_work(): void
    {
        $transport = User::where('email', 'transport@test.com')->firstOrFail();
        $service = TourismService::where('service_name', 'UAT Gondar Car Rental')->firstOrFail();

        $this->actingAs($transport)->get(route('transportation.vehicles.create'))
            ->assertOk()
            ->assertSee($service->service_name)
            ->assertSee('name="service_id"', false);

        $this->actingAs($transport)->post(route('transportation.vehicles.store'), [
            'service_id' => $service->service_id,
            'vehicle_identifier' => 'UAT-STABILIZATION-01',
            'vehicle_type' => 'SUV',
            'make' => 'Toyota',
            'model' => 'RAV4',
            'year' => 2024,
            'capacity' => 4,
            'status' => 'active',
        ])->assertRedirect(route('transportation.vehicles.index'));

        $this->assertDatabaseHas('transportation_vehicles', [
            'provider_id' => $transport->serviceProvider->provider_id,
            'service_id' => $service->service_id,
            'vehicle_identifier' => 'UAT-STABILIZATION-01',
        ]);
    }

    public function test_event_organizer_can_reach_ticket_management_and_create_a_ticket_type(): void
    {
        $organizer = User::where('email', 'event@test.com')->firstOrFail();
        $event = CulturalEvent::where('event_name', 'UAT Gondar Cultural Festival')->firstOrFail();

        $this->actingAs($organizer)->get(route('event-organizer.events.show', $event))
            ->assertOk()
            ->assertSee(route('event-organizer.events.tickets', $event));

        $this->actingAs($organizer)->get(route('event-organizer.events.tickets', $event))
            ->assertOk()
            ->assertSee('Add ticket type');

        $this->actingAs($organizer)->post(route('event-organizer.events.tickets.store', $event), [
            'name' => 'Stabilization Test Ticket',
            'price' => 300,
            'quantity' => 10,
            'status' => 'active',
        ])->assertRedirect();

        $ticket = EventTicketType::where('event_id', $event->event_id)->where('name', 'Stabilization Test Ticket')->firstOrFail();
        $this->assertDatabaseHas('event_ticket_types', ['event_id' => $event->event_id, 'name' => 'Stabilization Test Ticket']);

        $this->actingAs($organizer)->put(route('event-organizer.events.tickets.update', [$event, $ticket]), [
            'name' => 'Updated Stabilization Ticket',
            'price' => 350,
            'quantity' => 12,
            'status' => 'inactive',
        ])->assertRedirect();

        $this->assertDatabaseHas('event_ticket_types', ['ticket_type_id' => $ticket->ticket_type_id, 'name' => 'Updated Stabilization Ticket', 'status' => 'inactive']);
    }

    public function test_tour_guide_global_navigation_has_one_portal_entry_and_internal_links_remain_available(): void
    {
        $guide = User::where('email', 'guide@test.com')->firstOrFail();

        $this->actingAs($guide)->get('/')
            ->assertOk()
            ->assertSee('Tour Guide Portal')
            ->assertDontSee('href="'.route('tour-guide.availability').'"', false)
            ->assertDontSee('href="'.route('tour-guide.requests.index').'"', false);

        $this->actingAs($guide)->get(route('tour-guide.dashboard'))
            ->assertOk()
            ->assertSee('Availability')
            ->assertSee('Booking Requests');
    }

    public function test_tourist_reservation_detail_renders_event_and_transportation_branches(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $touristId = $tourist->tourist->tourist_id;
        $event = CulturalEvent::where('event_name', 'UAT Gondar Cultural Festival')->firstOrFail();
        $ticket = $event->ticketTypes()->firstOrFail();

        $eventBooking = Booking::create([
            'tourist_id' => $touristId,
            'service_id' => $event->service_id,
            'guide_id' => null,
            'status' => 'accepted',
            'booking_date' => now(),
            'total_amount' => $ticket->price,
            'currency' => 'ETB',
        ]);
        EventReservation::create([
            'booking_id' => $eventBooking->booking_id,
            'ticket_type_id' => $ticket->ticket_type_id,
            'quantity' => 1,
        ]);

        $this->actingAs($tourist)->get(route('tourist.reservations.show', $eventBooking))
            ->assertOk()
            ->assertSee('Event ticket reservation')
            ->assertDontSee('@elseif', false);

        $transportService = TourismService::where('service_name', 'UAT Gondar Car Rental')->firstOrFail();
        $transportBooking = Booking::create([
            'tourist_id' => $touristId,
            'service_id' => $transportService->service_id,
            'guide_id' => null,
            'status' => 'pending',
            'booking_date' => now(),
            'total_amount' => 0,
            'currency' => 'ETB',
        ]);
        TransportationReservation::create([
            'booking_id' => $transportBooking->booking_id,
            'vehicle_id' => null,
            'pickup_location' => 'Gondar Airport',
            'dropoff_location' => 'Gondar Hotel',
            'pickup_at' => Carbon::tomorrow()->setTime(9, 0),
            'dropoff_at' => Carbon::tomorrow()->setTime(10, 0),
            'passenger_count' => 2,
        ]);

        $this->actingAs($tourist)->get(route('tourist.reservations.show', $transportBooking))
            ->assertOk()
            ->assertSee('Transportation Reservation Details')
            ->assertDontSee('@elseif', false);
    }
}
