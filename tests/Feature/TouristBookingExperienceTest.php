<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\HotelRoomReservation;
use App\Models\TourGuide;
use App\Models\TourGuideReservation;
use App\Models\TourismService;
use App\Models\User;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TouristBookingExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_hotel_detail_uses_frozen_total_and_explains_awaiting_payment(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $service = TourismService::where('service_name', 'Standard Heritage View Room')->firstOrFail();
        $booking = Booking::create([
            'tourist_id' => $tourist->tourist->tourist_id,
            'service_id' => $service->service_id,
            'status' => 'payment_pending',
            'booking_date' => now(),
            'total_amount' => 3210,
            'currency' => 'ETB',
        ]);
        HotelRoomReservation::create([
            'booking_id' => $booking->booking_id,
            'check_in_date' => today()->addDays(10),
            'check_out_date' => today()->addDays(12),
            'guest_count' => 1,
        ]);

        $this->actingAs($tourist)->get(route('tourist.reservations.show', $booking))
            ->assertOk()
            ->assertSee('3,210.00 ETB')
            ->assertSee('Awaiting Payment')
            ->assertSee('Continue Payment')
            ->assertDontSee('Paid');

        $this->actingAs($tourist)->get(route('tourist.reservations.index'))
            ->assertOk()
            ->assertSee('3,210.00 ETB');
    }

    public function test_guide_history_shows_frozen_amount_and_review_action_for_completed_booking(): void
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail();
        $guide = TourGuide::where('license_number', 'TG-GDR-001')->firstOrFail();
        $booking = Booking::create([
            'tourist_id' => $tourist->tourist->tourist_id,
            'guide_id' => $guide->guide_id,
            'status' => 'completed',
            'booking_date' => now()->subDays(5),
            'total_amount' => 4000,
            'currency' => 'ETB',
        ]);
        TourGuideReservation::create([
            'booking_id' => $booking->booking_id,
            'start_date' => today()->subDays(5),
            'end_date' => today()->subDays(3),
            'number_of_tourists' => 2,
        ]);

        $this->actingAs($tourist)->get(route('tourist.reservations.index'))
            ->assertOk()
            ->assertSee('4,000.00 ETB');

        $this->actingAs($tourist)->get(route('tourist.reservations.show', $booking))
            ->assertOk()
            ->assertSee('Write a review')
            ->assertSee('4,000.00 ETB');
    }
}
