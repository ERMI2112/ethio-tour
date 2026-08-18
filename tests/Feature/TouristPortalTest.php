<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Notification;
use App\Models\TourismService;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TouristPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_tourist_dashboard_requires_an_active_tourist_account(): void
    {
        $this->get(route('tourist.dashboard'))->assertRedirect(route('login'));

        $admin = User::where('email', 'admin@test.com')->firstOrFail();
        $this->actingAs($admin)->get(route('tourist.dashboard'))->assertForbidden();

        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $tourist->update(['is_active' => false]);
        $this->actingAs($tourist)->get(route('tourist.dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_aggregates_real_bookings_trips_notifications_and_review_links(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $service = TourismService::where('service_name', 'UAT Standard Room')->firstOrFail();
        $destination = Destination::where('name', 'Gondar')->firstOrFail();
        $booking = Booking::create([
            'tourist_id' => $tourist->tourist->tourist_id,
            'service_id' => $service->service_id,
            'status' => 'payment_pending',
            'booking_date' => now(),
            'total_amount' => 1500,
            'currency' => 'ETB',
        ]);
        Notification::create([
            'user_id' => $tourist->user_id,
            'type' => 'booking_accepted',
            'title' => 'Booking accepted',
            'message' => 'Your reservation is ready for payment.',
            'channel' => 'in_app',
            'sent_date' => now(),
            'read_status' => false,
        ]);
        $trip = Trip::create([
            'user_id' => $tourist->user_id,
            'title' => 'Gondar Weekend',
            'start_date' => today()->addDays(3),
            'end_date' => today()->addDays(5),
            'status' => 'planned',
        ]);
        $trip->destinations()->attach($destination->destination_id);

        $this->actingAs($tourist)->get(route('tourist.dashboard'))
            ->assertOk()
            ->assertSee('Welcome, UAT Tourist')
            ->assertSee('Needs attention')
            ->assertSee('Upcoming bookings')
            ->assertSee('UAT Standard Room')
            ->assertSee('Continue Payment')
            ->assertSee('Gondar Weekend')
            ->assertSee('Booking accepted')
            ->assertSee('My Reviews')
            ->assertSee('#BK-'.sprintf('%05d', $booking->booking_id));
    }

    public function test_tourist_profile_and_review_pages_are_available_and_profile_updates_are_scoped(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();

        $this->actingAs($tourist)->get(route('tourist.profile'))->assertOk()->assertSee('UAT Tourist');
        $this->actingAs($tourist)->get(route('tourist.profile.edit'))->assertOk()->assertSee('Edit Profile');
        $this->actingAs($tourist)->get(route('tourist.reviews.index'))->assertOk()->assertSee('My Reviews');

        $this->actingAs($tourist)->put(route('tourist.profile.update'), [
            'full_name' => 'Updated Tourist',
            'nationality' => 'Ethiopian',
            'role' => 'administrator',
            'is_active' => false,
            'tourist_id' => 999999,
        ])->assertRedirect(route('tourist.profile'));

        $this->assertDatabaseHas('tourists', [
            'tourist_id' => $tourist->tourist->tourist_id,
            'full_name' => 'Updated Tourist',
            'nationality' => 'Ethiopian',
        ]);
        $this->assertDatabaseHas('users', [
            'user_id' => $tourist->user_id,
            'role' => 'tourist',
            'is_active' => 1,
        ]);
    }

    public function test_tourist_dashboard_has_useful_empty_states_without_fabricated_records(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();

        $this->actingAs($tourist)->get(route('tourist.dashboard'))
            ->assertOk()
            ->assertSee("You don't have any upcoming bookings yet.")
            ->assertSee('No saved trips yet')
            ->assertSee('all caught up')
            ->assertSee('submitted a review yet')
            ->assertSee('Completed bookings will appear here when they');
    }
}
