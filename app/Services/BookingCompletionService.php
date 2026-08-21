<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingCompletionService
{
    public function __construct(private readonly NotificationService $notifications) {}

    /**
     * Complete one confirmed booking when its domain-specific end has passed.
     * Returns false when the booking is not eligible for a transition.
     */
    public function complete(Booking $booking): bool
    {
        return DB::transaction(function () use ($booking): bool {
            $locked = Booking::query()
                ->with([
                    'payment',
                    'hotelRoomReservation',
                    'restaurantReservation',
                    'transportationReservation',
                    'tourGuideReservation',
                    'eventReservation.ticketType.event',
                ])
                ->lockForUpdate()
                ->find($booking->booking_id);

            if (! $locked || $locked->status !== 'confirmed' || ! $this->isDue($locked) || ! $this->paymentIsReady($locked)) {
                return false;
            }

            $locked->update(['status' => 'completed']);
            $recipient = $locked->fresh('tourist.user')->tourist?->user;
            $reference = '#'.$locked->booking_id;
            $this->notifications->createForUserAndAdministrators($recipient, 'booking_completed', 'Booking completed', 'Your booking '.$reference.' has completed. It is now eligible for review.');
            $this->notifications->createForUserAndAdministrators($recipient, 'review_available', 'Review available', 'You can now leave a review for completed booking '.$reference.'.');

            return true;
        }, attempts: 3);
    }

    /** @return int number of bookings transitioned */
    public function completeDueBookings(): int
    {
        $completed = 0;

        Booking::query()
            ->where('status', 'confirmed')
            ->select('booking_id')
            ->orderBy('booking_id')
            ->each(function (Booking $booking) use (&$completed): void {
                if ($this->complete($booking)) {
                    $completed++;
                }
            });

        return $completed;
    }

    private function isDue(Booking $booking): bool
    {
        $end = $this->completionBoundary($booking);

        return $end !== null && now(config('app.timezone'))->greaterThan($end);
    }

    private function completionBoundary(Booking $booking): ?Carbon
    {
        if ($reservation = $booking->hotelRoomReservation) {
            return $this->dateBoundary($reservation->check_out_date);
        }

        if ($reservation = $booking->restaurantReservation) {
            return $this->dateTimeBoundary($reservation->reservation_date, $reservation->end_time);
        }

        if ($reservation = $booking->transportationReservation) {
            return $reservation->dropoff_at?->copy()->setTimezone(config('app.timezone'));
        }

        if ($reservation = $booking->tourGuideReservation) {
            return $this->dateBoundary($reservation->end_date);
        }

        if ($event = $booking->eventReservation?->ticketType?->event) {
            return $this->dateTimeBoundary($event->event_date, $event->end_time);
        }

        return null;
    }

    private function paymentIsReady(Booking $booking): bool
    {
        if ($booking->total_amount === null || (float) $booking->total_amount <= 0 || trim((string) $booking->currency) === '') {
            return true;
        }

        return in_array($booking->payment?->status, ['success', 'successful', 'completed'], true)
            && $booking->payment?->amount !== null
            && number_format((float) $booking->payment->amount, 2, '.', '') === number_format((float) $booking->total_amount, 2, '.', '');
    }

    private function dateBoundary(mixed $date): ?Carbon
    {
        return $date ? Carbon::parse($date, config('app.timezone'))->startOfDay() : null;
    }

    private function dateTimeBoundary(mixed $date, mixed $time): ?Carbon
    {
        if (! $date || ! $time) {
            return null;
        }

        $dateString = Carbon::parse($date, config('app.timezone'))->format('Y-m-d');

        return Carbon::parse($dateString.' '.(string) $time, config('app.timezone'));
    }
}
