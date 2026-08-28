<?php

namespace App\Services;

use App\Models\Booking;

/**
 * Resolves and computes platform commission for a booking payment.
 *
 * Single source of truth shared by the live payment confirmation flow
 * (PaymentService) and the demo seeding path, so seeded demo data obeys
 * exactly the same business rules as real Chapa settlements.
 *
 * Pilot policy:
 * - provider-service and event bookings use the provider's active
 *   subscription plan commission rate;
 * - providers without an active subscription (trial) are not charged;
 * - tour guide bookings carry no commission.
 */
class CommissionService
{
    /**
     * @return array{commission_rate: string, commission_amount: string, provider_net_amount: string}|null
     *                                                                                                     Commission snapshot fields, or null when the booking is not commissionable.
     */
    public function snapshotFor(Booking $booking): ?array
    {
        $rate = $this->resolveRate($booking);

        if ($rate === null || $booking->total_amount === null) {
            return null;
        }

        $amount = (float) $booking->total_amount;
        $commission = round($amount * $rate / 100, 2);

        return [
            'commission_rate' => number_format($rate, 2, '.', ''),
            'commission_amount' => number_format($commission, 2, '.', ''),
            'provider_net_amount' => number_format($amount - $commission, 2, '.', ''),
        ];
    }

    public function resolveRate(Booking $booking): ?float
    {
        $provider = null;

        if ($booking->service_id !== null) {
            $provider = $booking->tourismService?->serviceProvider;
        } else {
            $provider = $booking->eventReservation?->ticketType?->event?->serviceProvider;
        }

        if (! $provider) {
            return null; // guide bookings: no subscription plan, no commission
        }

        $rate = $provider->providerSubscriptions()
            ->where('status', 'active')
            ->latest('start_date')
            ->first()
            ?->subscriptionPlan
            ?->commission_rate;

        return $rate === null ? null : (float) $rate;
    }
}
