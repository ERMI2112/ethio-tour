<?php

namespace App\Services;

use App\Models\Booking;
use App\Support\Money;

/**
 * Resolves and computes platform commission for a booking payment.
 *
 * Single source of truth for commission rules, shared by the live payment
 * confirmation flow (PaymentService) and the demo seeding path, so seeded
 * demo data obeys exactly the same business rules as real Chapa settlements.
 *
 * All math runs on integer minor units via Money — never on floats.
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

        $grossMinor = Money::toMinor((string) $booking->total_amount);
        $commissionMinor = Money::percentage($grossMinor, Money::percentToBasisPoints($rate));

        return [
            'commission_rate' => Money::normalize($rate),
            'commission_amount' => Money::fromMinor($commissionMinor),
            'provider_net_amount' => Money::fromMinor($grossMinor - $commissionMinor),
        ];
    }

    public function resolveRate(Booking $booking): ?string
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

        return $rate === null ? null : (string) $rate;
    }
}
