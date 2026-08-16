<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class ReviewEligibilityService
{
    public function isEligible(Booking $booking): bool
    {
        if ($booking->review || in_array($booking->status, ['pending', 'rejected', 'cancelled'], true)) {
            return false;
        }

        if ($booking->status === 'completed') {
            return true;
        }

        if ($booking->status !== 'confirmed') {
            return false;
        }

        if ($booking->tourGuideReservation) {
            return $booking->tourGuideReservation->end_date?->isPast() ?? false;
        }

        if ($booking->hotelRoomReservation) {
            return $booking->hotelRoomReservation->check_out_date?->isPast() ?? false;
        }

        if ($booking->transportationReservation) {
            return $booking->transportationReservation->dropoff_at?->isPast() ?? false;
        }

        if ($booking->restaurantReservation) {
            return Carbon::parse($booking->restaurantReservation->reservation_date)->isBefore(today());
        }

        if ($booking->eventReservation?->ticketType?->event) {
            return $booking->eventReservation->ticketType->event->event_date?->isPast() ?? false;
        }

        return false;
    }
}
