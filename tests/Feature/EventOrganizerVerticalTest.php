<?php

namespace Tests\Feature;

use App\Exceptions\EventInventoryException;
use App\Models\Booking;
use App\Models\Category;
use App\Models\CulturalEvent;
use App\Models\Destination;
use App\Models\EventTicketType;
use App\Models\ServiceProvider;
use App\Models\TourismBureauOfficer;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\User;
use App\Services\EventInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventOrganizerVerticalTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_event_discovery_and_organizer_role_access(): void
    {
        $context = $this->context();
        $this->get(route('events.index'))->assertOk()->assertSee($context['event']->event_name);
        $this->get(route('events.show', $context['event']))->assertOk()->assertSee($context['ticket']->name);
        $this->actingAs($context['user'])->get(route('event-organizer.dashboard'))->assertOk();
        $tourist = $this->tourist();
        $this->actingAs($tourist['user'])->get(route('event-organizer.dashboard'))->assertForbidden();
        $hotelUser = User::factory()->create(['role' => 'service_provider']);
        ServiceProvider::create(['user_id' => $hotelUser->user_id, 'business_name' => 'Hotel', 'provider_type' => 'hotel', 'status' => 'approved']);
        $this->actingAs($hotelUser)->get(route('event-organizer.dashboard'))->assertForbidden();
    }

    public function test_event_organizer_can_create_event_and_ticket_types(): void
    {
        $context = $this->context();
        $this->actingAs($context['user'])->post(route('event-organizer.events.store'), [
            'event_name' => 'New Cultural Night', 'description' => 'Music and heritage.', 'event_date' => '2026-11-15', 'start_time' => '18:00', 'end_time' => '21:00', 'venue' => 'Gondar Square', 'destination_id' => $context['destination']->destination_id, 'category_id' => $context['category']->category_id, 'status' => 'published',
        ])->assertRedirect();
        $event = $context['provider']->events()->latest('event_id')->firstOrFail();
        $this->actingAs($context['user'])->post(route('event-organizer.events.tickets.store', $event), ['name' => 'VIP', 'price' => 250, 'quantity' => 10, 'status' => 'active'])->assertRedirect();
        $this->assertDatabaseHas('event_ticket_types', ['event_id' => $event->event_id, 'name' => 'VIP']);
    }

    public function test_event_and_ticket_ownership_blocks_cross_organizer_access(): void
    {
        $owner = $this->context();
        $other = $this->context('other-event@example.test');
        $this->actingAs($other['user'])->get(route('event-organizer.events.edit', $owner['event']))->assertForbidden();
        $this->actingAs($other['user'])->get(route('event-organizer.events.tickets', $owner['event']))->assertForbidden();
        $this->assertDatabaseHas('cultural_events', ['event_id' => $owner['event']->event_id, 'provider_id' => $owner['provider']->provider_id]);
    }

    public function test_ticket_inventory_is_calculated_and_booking_is_centralized(): void
    {
        $context = $this->context();
        $inventory = app(EventInventoryService::class);
        $this->assertSame(5, $inventory->availableQuantity($context['ticket']));
        $tourist = $this->tourist();
        $this->actingAs($tourist['user'])->post(route('tourist.event-reservations.store', $context['event']), ['ticket_type_id' => $context['ticket']->ticket_type_id, 'quantity' => 2])->assertRedirect();
        $booking = Booking::latest('booking_id')->firstOrFail();
        $this->assertSame('accepted', $booking->status);
        $this->assertSame($context['event']->service_id, $booking->service_id);
        $this->assertSame(2, $booking->eventReservation->quantity);
        $this->assertSame(300.0, (float) $booking->total_amount);
        $this->assertSame(3, $inventory->availableQuantity($context['ticket']->fresh()));
    }

    public function test_sold_out_and_past_events_cannot_be_booked(): void
    {
        $context = $this->context();
        $tourist = $this->tourist();
        $this->actingAs($tourist['user'])->post(route('tourist.event-reservations.store', $context['event']), ['ticket_type_id' => $context['ticket']->ticket_type_id, 'quantity' => 6])->assertRedirect()->assertSessionHas('error');
        $context['event']->update(['event_date' => '2020-01-01']);
        $this->actingAs($tourist['user'])->post(route('tourist.event-reservations.store', $context['event']), ['ticket_type_id' => $context['ticket']->ticket_type_id, 'quantity' => 1])->assertRedirect()->assertSessionHas('error');
    }

    public function test_competing_ticket_claims_cannot_oversell_inventory(): void
    {
        $context = $this->context();
        $first = $this->tourist();
        $second = $this->tourist();
        $service = app(EventInventoryService::class);
        $booking = $service->reserve($first['tourist'], $context['event'], $context['ticket']->ticket_type_id, 5);
        $this->assertSame('accepted', $booking->status);
        $this->expectException(EventInventoryException::class);
        $service->reserve($second['tourist'], $context['event'], $context['ticket']->ticket_type_id, 1);
    }

    public function test_event_reservation_history_is_visible_only_to_own_organizer(): void
    {
        $context = $this->context();
        $tourist = $this->tourist();
        app(EventInventoryService::class)->reserve($tourist['tourist'], $context['event'], $context['ticket']->ticket_type_id, 1);
        $this->actingAs($context['user'])->get(route('event-organizer.events.bookings'))->assertOk()->assertSee($context['event']->event_name);
        $other = $this->context('second-organizer@example.test');
        $this->actingAs($other['user'])->get(route('event-organizer.events.bookings'))->assertOk()->assertDontSee($context['event']->event_name);
    }

    private function context(string $email = 'event-organizer@example.test'): array
    {
        $user = User::factory()->create(['email' => $email, 'role' => 'service_provider']);
        $provider = ServiceProvider::create(['user_id' => $user->user_id, 'business_name' => 'Cultural Organizer', 'provider_type' => 'event_organizer', 'status' => 'approved']);
        $bureauUser = User::factory()->create(['role' => 'tourism_bureau_officer']);
        $officer = TourismBureauOfficer::create(['user_id' => $bureauUser->user_id]);
        $category = Category::create(['category_name' => 'Culture '.uniqid()]);
        $destination = Destination::create(['officer_id' => $officer->officer_id, 'name' => 'Gondar', 'location' => 'Gondar', 'description' => 'Historic city']);
        $service = TourismService::create(['provider_id' => $provider->provider_id, 'category_id' => $category->category_id, 'destination_id' => $destination->destination_id, 'service_name' => 'Timket Festival', 'price' => 0, 'description' => 'Festival service']);
        $event = CulturalEvent::create(['destination_id' => $destination->destination_id, 'provider_id' => $provider->provider_id, 'service_id' => $service->service_id, 'event_name' => 'Timket Festival', 'description' => 'Cultural celebration', 'event_date' => '2026-11-01', 'start_time' => '09:00', 'end_time' => '17:00', 'venue' => 'Gondar Square', 'status' => 'published']);
        $ticket = EventTicketType::create(['event_id' => $event->event_id, 'name' => 'General', 'price' => 150, 'quantity' => 5, 'status' => 'active']);

        return compact('user', 'provider', 'category', 'destination', 'service', 'event', 'ticket');
    }

    private function tourist(): array
    {
        $user = User::factory()->create(['role' => 'tourist']);
        $tourist = Tourist::create(['user_id' => $user->user_id, 'full_name' => 'Event Tourist', 'nationality' => 'Ethiopian']);

        return compact('user', 'tourist');
    }
}
