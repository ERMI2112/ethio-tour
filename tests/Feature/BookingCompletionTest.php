<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\CulturalEvent;
use App\Models\EventReservation;
use App\Models\EventTicketType;
use App\Models\HotelRoomReservation;
use App\Models\RestaurantReservation;
use App\Models\TourGuideReservation;
use App\Models\TourismService;
use App\Models\TransportationReservation;
use App\Models\User;
use App\Services\BookingCompletionService;
use App\Services\ReviewEligibilityService;
use Carbon\Carbon;
use Database\Seeders\UatDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UatDemoSeeder::class);
    }

    public function test_hotel_completion_waits_until_checkout_and_then_completes(): void
    {
        $before = $this->hotelBooking(today()->addDay());
        $after = $this->hotelBooking(today()->subDay());
        $service = app(BookingCompletionService::class);

        $this->assertFalse($service->complete($before));
        $this->assertSame('confirmed', $before->fresh()->status);
        $this->assertTrue($service->complete($after));
        $this->assertSame('completed', $after->fresh()->status);
    }

    public function test_restaurant_completion_uses_reservation_end_time(): void
    {
        $before = $this->restaurantBooking(today(), '23:59:00');
        $after = $this->restaurantBooking(today()->subDay(), '23:59:00');
        $service = app(BookingCompletionService::class);

        $this->assertFalse($service->complete($before));
        $this->assertTrue($service->complete($after));
    }

    public function test_transportation_completion_waits_until_dropoff(): void
    {
        $before = $this->transportBooking(now()->addHour());
        $after = $this->transportBooking(now()->subHour());
        $service = app(BookingCompletionService::class);

        $this->assertFalse($service->complete($before));
        $this->assertTrue($service->complete($after));
    }

    public function test_guide_completion_waits_until_requested_tour_end(): void
    {
        $before = $this->guideBooking(today()->addDay());
        $after = $this->guideBooking(today()->subDay());
        $service = app(BookingCompletionService::class);

        $this->assertFalse($service->complete($before));
        $this->assertTrue($service->complete($after));
    }

    public function test_event_completion_uses_event_end_time(): void
    {
        $before = $this->eventBooking(today()->addDay(), '20:00:00');
        $after = $this->eventBooking(today()->subDay(), '20:00:00');
        $service = app(BookingCompletionService::class);

        $this->assertFalse($service->complete($before));
        $this->assertTrue($service->complete($after));
    }

    public function test_only_confirmed_paid_or_free_bookings_can_complete(): void
    {
        $pending = $this->hotelBooking(today()->subDay(), 'pending');
        $paymentPending = $this->hotelBooking(today()->subDay(), 'payment_pending');
        $unpaid = $this->hotelBooking(today()->subDay(), 'confirmed', false);
        $unpaid->payment()->create(['amount' => 1500, 'status' => 'pending', 'payment_method' => 'chapa']);
        $rejected = $this->hotelBooking(today()->subDay(), 'rejected');
        $cancelled = $this->hotelBooking(today()->subDay(), 'cancelled');
        $service = app(BookingCompletionService::class);

        foreach ([$pending, $paymentPending, $unpaid, $rejected, $cancelled] as $booking) {
            $this->assertFalse($service->complete($booking));
            $this->assertNotSame('completed', $booking->fresh()->status);
        }

        $paid = $this->hotelBooking(today()->subDay());
        $this->assertTrue($service->complete($paid));
    }

    public function test_completion_is_idempotent_and_review_eligibility_follows_status(): void
    {
        $booking = $this->hotelBooking(today()->subDay());
        $service = app(BookingCompletionService::class);

        $this->assertTrue($service->complete($booking));
        $this->assertFalse($service->complete($booking));
        $this->assertSame('completed', $booking->fresh()->status);
        $this->assertTrue(app(ReviewEligibilityService::class)->isEligible($booking->fresh()));
    }

    public function test_completion_command_is_repeatable(): void
    {
        $booking = $this->hotelBooking(today()->subDay());

        $this->artisan('bookings:complete')->expectsOutput('Completed 1 booking(s).')->assertSuccessful();
        $this->artisan('bookings:complete')->expectsOutput('Completed 0 booking(s).')->assertSuccessful();
        $this->assertSame('completed', $booking->fresh()->status);
    }

    private function hotelBooking(Carbon $checkout, string $status = 'confirmed', bool $paid = true): Booking
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail()->tourist;
        $service = TourismService::where('service_name', 'UAT Standard Room')->firstOrFail();
        $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $service->service_id, 'status' => $status, 'total_amount' => 1500, 'currency' => 'ETB']);
        HotelRoomReservation::create(['booking_id' => $booking->booking_id, 'check_in_date' => $checkout->copy()->subDays(2), 'check_out_date' => $checkout, 'guest_count' => 1]);
        if ($paid) {
            $booking->payment()->create(['amount' => 1500, 'status' => 'success', 'payment_method' => 'chapa']);
        }

        return $booking;
    }

    private function restaurantBooking(Carbon $date, string $endTime): Booking
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail()->tourist;
        $service = TourismService::where('service_name', 'UAT Gondar Dining Reservation')->firstOrFail();
        $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $service->service_id, 'status' => 'confirmed', 'total_amount' => 350, 'currency' => 'ETB']);
        RestaurantReservation::create(['booking_id' => $booking->booking_id, 'reservation_date' => $date, 'start_time' => '18:00:00', 'end_time' => $endTime, 'guest_count' => 1]);
        $booking->payment()->create(['amount' => 350, 'status' => 'success', 'payment_method' => 'chapa']);

        return $booking;
    }

    private function transportBooking(Carbon $dropoff): Booking
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail()->tourist;
        $service = TourismService::where('service_name', 'UAT Gondar Car Rental')->firstOrFail();
        $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $service->service_id, 'status' => 'confirmed', 'total_amount' => 1800, 'currency' => 'ETB']);
        TransportationReservation::create(['booking_id' => $booking->booking_id, 'pickup_location' => 'Gondar', 'dropoff_location' => 'Gondar', 'pickup_at' => $dropoff->copy()->subDay(), 'dropoff_at' => $dropoff, 'passenger_count' => 1]);
        $booking->payment()->create(['amount' => 1800, 'status' => 'success', 'payment_method' => 'chapa']);

        return $booking;
    }

    private function guideBooking(Carbon $endDate): Booking
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail()->tourist;
        $guide = User::where('email', 'guide@test.com')->firstOrFail()->tourGuide;
        $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'guide_id' => $guide->guide_id, 'status' => 'confirmed', 'total_amount' => 4000, 'currency' => 'ETB']);
        TourGuideReservation::create(['booking_id' => $booking->booking_id, 'start_date' => $endDate->copy()->subDays(2), 'end_date' => $endDate, 'number_of_tourists' => 1]);
        $booking->payment()->create(['amount' => 4000, 'status' => 'success', 'payment_method' => 'chapa']);

        return $booking;
    }

    private function eventBooking(Carbon $date, string $endTime): Booking
    {
        $tourist = User::where('email', 'tourist@test.com')->firstOrFail()->tourist;
        $baseEvent = CulturalEvent::where('event_name', 'UAT Gondar Cultural Festival')->firstOrFail();
        $baseService = TourismService::findOrFail($baseEvent->service_id);
        $service = $baseService->replicate();
        $service->service_name = 'Completion Test Offering '.uniqid();
        $service->save();
        $event = $baseEvent->replicate();
        $event->event_name = 'Completion Test Event '.uniqid();
        $event->service_id = $service->service_id;
        $event->save();
        $event->update(['event_date' => $date, 'start_time' => '18:00:00', 'end_time' => $endTime]);
        $baseTicket = EventTicketType::where('event_id', $baseEvent->event_id)->firstOrFail();
        $ticket = EventTicketType::create(['event_id' => $event->event_id, 'name' => 'Completion Admission', 'price' => $baseTicket->price, 'quantity' => 10, 'status' => 'active']);
        $booking = Booking::create(['tourist_id' => $tourist->tourist_id, 'service_id' => $service->service_id, 'status' => 'confirmed', 'total_amount' => $ticket->price, 'currency' => 'ETB']);
        EventReservation::create(['booking_id' => $booking->booking_id, 'ticket_type_id' => $ticket->ticket_type_id, 'quantity' => 1]);
        $booking->payment()->create(['amount' => $ticket->price, 'status' => 'success', 'payment_method' => 'chapa']);

        return $booking;
    }
}
