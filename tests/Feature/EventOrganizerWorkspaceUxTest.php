<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventOrganizerWorkspaceUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_event_organizer_workspace_has_event_attention_and_role_navigation(): void
    {
        $organizer = User::where('email', 'event@test.com')->firstOrFail();

        $this->actingAs($organizer)->get(route('event-organizer.dashboard'))
            ->assertOk()->assertSee('Needs attention')->assertSee('Operational insights')->assertSee('Published events')->assertSee('Notifications')->assertDontSee('View Public Site')->assertDontSee('Explore Ethiopia');
    }

    public function test_event_management_ticket_and_booking_workflows_render(): void
    {
        $organizer = User::where('email', 'event@test.com')->firstOrFail();

        $this->actingAs($organizer)->get(route('event-organizer.events.index'))
            ->assertOk()->assertSee('My events')->assertSee('Timkat Gondar Epiphany & Cultural Festival');
        $event = $organizer->serviceProvider->events()->firstOrFail();
        $this->actingAs($organizer)->get(route('event-organizer.events.show', $event))
            ->assertOk()->assertSee('Manage tickets')->assertSee('Edit');
        $this->actingAs($organizer)->get(route('event-organizer.events.tickets', $event))
            ->assertOk()->assertSee('Ticket management');
        $this->actingAs($organizer)->get(route('event-organizer.events.bookings'))
            ->assertOk()->assertSee('Event bookings');
    }

    public function test_non_event_roles_are_denied_from_event_organizer_workspace(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $hotel = User::where('email', 'hotel@test.com')->firstOrFail();

        $this->actingAs($tourist)->get(route('event-organizer.dashboard'))->assertForbidden();
        $this->actingAs($hotel)->get(route('event-organizer.dashboard'))->assertForbidden();
    }
}
