<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\CulturalEvent;
use App\Models\EventReservation;
use App\Models\EventTicketType;
use App\Models\HotelRoom;
use App\Models\HotelRoomReservation;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\RestaurantReservation;
use App\Models\RestaurantTable;
use App\Models\Review;
use App\Models\TourGuide;
use App\Models\TourGuideReservation;
use App\Models\TourismService;
use App\Models\Tourist;
use App\Models\TransportationReservation;
use App\Models\TransportationVehicle;
use App\Services\CommissionService;
use App\Services\LedgerService;
use Illuminate\Database\Seeder;

/**
 * Seeds a realistic end-to-end demo journey for the UAT tourist account:
 * completed bookings across every vertical, safe demo payment records,
 * eligible reviews, and in-app notifications.
 *
 * SAFETY GUARANTEES
 * - Never contacts the Chapa API: payment rows are seeded directly with
 *   deterministic DEMO-SEED-* gateway references in a terminal demo state.
 * - Never sends external SMS/email: notifications are in_app rows only.
 * - Idempotent: every booking is located through its deterministic
 *   DEMO-SEED-* payment reference (or natural reservation keys), so a
 *   second run updates instead of duplicating.
 */
class DemoJourneySeeder extends Seeder
{
    private const REFERENCE_PREFIX = 'DEMO-SEED-';

    private ?Tourist $tourist = null;

    public function run(): void
    {
        $this->tourist = Tourist::query()
            ->whereHas('user', fn ($query) => $query->where('email', 'tourist@test.com'))
            ->first();

        if (! $this->tourist) {
            // UatDemoSeeder has not run yet; nothing safe to attach to.
            return;
        }

        $this->seedCompletedHotelBooking();
        $this->seedCompletedRestaurantBooking();
        $this->seedCompletedTransportationBooking();
        $this->seedCompletedGuideBooking();
        $this->seedUpcomingEventBooking();
        $this->seedPendingHotelBooking();
        $this->seedNotifications();
    }

    private function seedCompletedHotelBooking(): void
    {
        $service = $this->service('Standard Heritage View Room');
        $room = $service ? HotelRoom::query()
            ->whereHas('hotelRoomType', fn ($query) => $query->where('service_id', $service->service_id))
            ->where('status', 'active')
            ->first() : null;

        if (! $service || ! $room) {
            return;
        }

        $booking = $this->bookingWithPayment(
            reference: 'HOTEL-001',
            attributes: ['tourist_id' => $this->tourist->tourist_id, 'service_id' => $service->service_id, 'status' => 'completed', 'total_amount' => 3000.00, 'currency' => 'ETB'],
            amount: 3000.00,
        );

        HotelRoomReservation::firstOrCreate(
            ['booking_id' => $booking->booking_id],
            [
                'room_id' => $room->room_id,
                'check_in_date' => now()->subDays(20)->toDateString(),
                'check_out_date' => now()->subDays(18)->toDateString(),
                'guest_count' => 2,
            ],
        );

        $this->review($booking, 5, 'Goha Hotel exceeded every expectation. Spotless heritage-view room, staff arranged our Simien permits, and the breakfast injera was the best of our trip. Falling asleep with the castle skyline in view is unforgettable.');
    }

    private function seedCompletedRestaurantBooking(): void
    {
        $service = $this->service('Traditional Feast & Coffee Ceremony');
        $table = $service ? RestaurantTable::query()
            ->where('provider_id', $service->provider_id)
            ->where('status', 'active')
            ->first() : null;

        if (! $service || ! $table) {
            return;
        }

        $booking = $this->bookingWithPayment(
            reference: 'RESTAURANT-001',
            attributes: ['tourist_id' => $this->tourist->tourist_id, 'service_id' => $service->service_id, 'status' => 'completed', 'total_amount' => 350.00, 'currency' => 'ETB'],
            amount: 350.00,
        );

        RestaurantReservation::firstOrCreate(
            ['booking_id' => $booking->booking_id],
            [
                'table_id' => $table->table_id,
                'reservation_date' => now()->subDays(15)->toDateString(),
                'start_time' => '18:30',
                'end_time' => '20:30',
                'guest_count' => 2,
            ],
        );

        $this->review($booking, 5, 'Four Sisters is a Gondar treasure. The doro wat was rich and perfectly spiced, and the traditional coffee ceremony — roasting, grinding, and brewing at our table — was a highlight of the whole trip.');
    }

    private function seedCompletedTransportationBooking(): void
    {
        $service = $this->service('Simien 4x4 Safari Car Rental');
        $vehicle = $service ? TransportationVehicle::query()
            ->where('provider_id', $service->provider_id)
            ->where('status', 'active')
            ->first() : null;

        if (! $service || ! $vehicle) {
            return;
        }

        $booking = $this->bookingWithPayment(
            reference: 'TRANSPORT-001',
            attributes: ['tourist_id' => $this->tourist->tourist_id, 'service_id' => $service->service_id, 'status' => 'completed', 'total_amount' => 3600.00, 'currency' => 'ETB'],
            amount: 3600.00,
        );

        TransportationReservation::firstOrCreate(
            ['booking_id' => $booking->booking_id],
            [
                'vehicle_id' => $vehicle->vehicle_id,
                'pickup_location' => 'Goha Hotel, Gondar',
                'dropoff_location' => 'Debark, Simien Mountains National Park Gate',
                'pickup_at' => now()->subDays(12)->setTime(6, 0),
                'dropoff_at' => now()->subDays(12)->setTime(11, 30),
                'passenger_count' => 2,
            ],
        );

        $this->review($booking, 4, 'Reliable 4x4 and a careful, punctual driver who handled the Debark mountain road expertly. Vehicle was clean and the early pickup for our trek happened exactly on time. One star off only because the AC struggled on the climb.');
    }

    private function seedCompletedGuideBooking(): void
    {
        $guide = TourGuide::query()
            ->where('license_number', 'TG-GDR-001')
            ->where('verification_status', 'verified')
            ->first();

        if (! $guide) {
            return;
        }

        $booking = $this->bookingWithPayment(
            reference: 'GUIDE-001',
            attributes: ['tourist_id' => $this->tourist->tourist_id, 'guide_id' => $guide->guide_id, 'status' => 'completed', 'total_amount' => 4000.00, 'currency' => 'ETB'],
            amount: 4000.00,
        );

        TourGuideReservation::firstOrCreate(
            ['booking_id' => $booking->booking_id],
            [
                'start_date' => now()->subDays(19)->toDateString(),
                'end_date' => now()->subDays(18)->toDateString(),
                'number_of_tourists' => 2,
            ],
        );

        $this->review($booking, 5, 'Yared turned Fasil Ghebbi from old stones into living history. His stories about Emperor Fasilides and the angels of Debre Berhan Selassie gave us goosebumps. Worth every birr — book him before someone else does.');
    }

    private function seedUpcomingEventBooking(): void
    {
        $event = CulturalEvent::query()
            ->where('event_name', 'Timkat Gondar Epiphany & Cultural Festival')
            ->where('status', 'published')
            ->first();
        $ticketType = $event ? EventTicketType::query()
            ->where('event_id', $event->event_id)
            ->where('name', 'General Admission')
            ->first() : null;

        if (! $event || ! $ticketType) {
            return;
        }

        $quantity = 2;
        $amount = (float) $ticketType->price * $quantity;

        $booking = $this->bookingWithPayment(
            reference: 'EVENT-001',
            attributes: ['tourist_id' => $this->tourist->tourist_id, 'service_id' => $event->service_id, 'status' => 'confirmed', 'total_amount' => $amount, 'currency' => 'ETB'],
            amount: $amount,
        );

        EventReservation::firstOrCreate(
            ['booking_id' => $booking->booking_id],
            ['ticket_type_id' => $ticketType->ticket_type_id, 'quantity' => $quantity],
        );

        // Deliberately no review: the event is in the future, so the booking
        // is not yet review-eligible under ReviewEligibilityService rules.
    }

    private function seedPendingHotelBooking(): void
    {
        $service = $this->service('Deluxe Imperial Suite');

        if (! $service) {
            return;
        }

        $checkIn = now()->addDays(30)->toDateString();

        $exists = Booking::query()
            ->where('tourist_id', $this->tourist->tourist_id)
            ->where('service_id', $service->service_id)
            ->where('status', 'pending')
            ->whereHas('hotelRoomReservation', fn ($query) => $query->whereDate('check_in_date', $checkIn))
            ->exists();

        if ($exists) {
            return;
        }

        // No payment row: provider has not accepted yet, so there is nothing
        // to pay. This booking demonstrates the provider accept/reject inbox.
        $booking = Booking::create([
            'tourist_id' => $this->tourist->tourist_id,
            'service_id' => $service->service_id,
            'status' => 'pending',
            'total_amount' => 5000.00,
            'currency' => 'ETB',
        ]);

        HotelRoomReservation::create([
            'booking_id' => $booking->booking_id,
            'check_in_date' => $checkIn,
            'check_out_date' => now()->addDays(32)->toDateString(),
            'guest_count' => 3,
        ]);
    }

    private function seedNotifications(): void
    {
        $user = $this->tourist->user;

        if (! $user) {
            return;
        }

        $notifications = [
            [
                'type' => 'booking_accepted',
                'title' => 'Hotel reservation accepted',
                'message' => 'Your reservation at Goha Hotel Gondar was accepted and room RM-101 was allocated. It is awaiting payment.',
            ],
            [
                'type' => 'booking_confirmed',
                'title' => 'Payment confirmed',
                'message' => 'Your payment of 4000.00 ETB for your Gondar heritage tour guide booking was confirmed. Enjoy your trip!',
            ],
            [
                'type' => 'booking_request',
                'title' => 'Reservation request submitted',
                'message' => 'Your Deluxe Imperial Suite request was sent to Goha Hotel Gondar. You will be notified when the provider responds.',
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::firstOrCreate(
                [
                    'user_id' => $user->user_id,
                    'type' => $notification['type'],
                    'title' => $notification['title'],
                ],
                [
                    'message' => $notification['message'],
                    'channel' => 'in_app',
                    'sent_date' => now(),
                    'read_status' => false,
                ],
            );
        }
    }

    /**
     * Locate (or create) a booking through its deterministic demo payment
     * reference. Payment rows use the unique gateway_reference column, which
     * makes the whole chain idempotent without touching real gateways.
     *
     * Monetization: demo payments carry the same commission snapshot a real
     * Chapa confirmation would record, computed through CommissionService so
     * business rules are never duplicated here. Demo rows seeded before
     * commission existed are backfilled once; rows that already carry a
     * snapshot are never altered.
     */
    private function bookingWithPayment(string $reference, array $attributes, float $amount): Booking
    {
        $gatewayReference = self::REFERENCE_PREFIX.$reference;

        $existing = Payment::query()->where('gateway_reference', $gatewayReference)->first();

        if ($existing) {
            $this->backfillCommission($existing);

            // Ledger coherence: demo payments record ledger entries through
            // the same service as real payments; firstOrCreate + the unique
            // (payment_id, entry_type) constraint keep re-runs idempotent.
            app(LedgerService::class)->recordPayment($existing);

            return $existing->booking;
        }

        $booking = Booking::create($attributes);

        $payment = new Payment([
            'booking_id' => $booking->booking_id,
            'amount' => $amount,
            'status' => 'success',
            'payment_method' => 'chapa',
            'gateway_reference' => $gatewayReference,
        ]);

        $snapshot = app(CommissionService::class)->snapshotFor($booking);

        if ($snapshot !== null) {
            $payment->fill($snapshot);
        }

        $payment->save();

        app(LedgerService::class)->recordPayment($payment->fresh());

        return $booking;
    }

    /**
     * One-time repair for DEMO-SEED-* payments seeded before commission
     * capture existed. Only touches demo rows whose commission fields are
     * entirely unset; correct snapshots are left alone.
     */
    private function backfillCommission(Payment $payment): void
    {
        if ($payment->commission_rate !== null || $payment->commission_amount !== null || $payment->provider_net_amount !== null) {
            return;
        }

        $booking = $payment->booking;

        if (! $booking) {
            return;
        }

        $snapshot = app(CommissionService::class)->snapshotFor($booking);

        if ($snapshot !== null) {
            $payment->update($snapshot);
        }
    }

    private function review(Booking $booking, int $rating, string $comment): void
    {
        Review::firstOrCreate(
            ['booking_id' => $booking->booking_id],
            [
                'tourist_id' => $this->tourist->tourist_id,
                'rating' => $rating,
                'comment' => $comment,
                'review_date' => now()->subDays(10)->toDateString(),
            ],
        );
    }

    private function service(string $name): ?TourismService
    {
        return TourismService::query()->where('service_name', $name)->first();
    }
}
