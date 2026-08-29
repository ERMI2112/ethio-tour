<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\ProviderLedgerEntry;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;

/**
 * Records the financial consequences of successful payments into the
 * immutable provider ledger.
 *
 * This service is deliberately NOT a second business-logic engine:
 * CommissionService owns the rules, Payment stores the snapshot, and this
 * ledger simply persists the consequences of that snapshot. No commission
 * percentages are computed here — amounts come from the payment record.
 *
 * Idempotency is enforced two ways: guarded firstOrCreate inside the caller's
 * transaction, plus a database-level UNIQUE(payment_id, entry_type)
 * constraint that makes duplicate creation impossible even under races.
 */
class LedgerService
{
    /**
     * Record ledger entries for a successful payment.
     *
     * - every successful payment with a resolvable payable earns a gross
     *   `earning` entry (guides and trial providers included);
     * - commissioned payments additionally earn a negative `commission`
     *   entry equal to the payment's stored commission snapshot;
     * - repeated calls (duplicate webhook/callback delivery, seeder re-runs)
     *   create nothing twice.
     */
    public function recordPayment(Payment $payment): void
    {
        if ($payment->status !== 'success') {
            return;
        }

        $booking = $payment->booking;

        if (! $booking) {
            return;
        }

        $payable = $this->resolvePayable($booking);

        if (! $payable) {
            return;
        }

        $currency = strtoupper((string) ($booking->currency ?: 'ETB'));

        ProviderLedgerEntry::firstOrCreate(
            ['payment_id' => $payment->payment_id, 'entry_type' => ProviderLedgerEntry::TYPE_EARNING],
            [
                'payable_type' => $payable->getMorphClass(),
                'payable_id' => $payable->getKey(),
                'booking_id' => $booking->booking_id,
                'amount' => Money::normalize((string) $payment->amount),
                'currency' => $currency,
                'description' => 'Gross earning from booking #'.$booking->booking_id,
                'created_at' => now(),
            ],
        );

        if ($payment->commission_amount !== null) {
            ProviderLedgerEntry::firstOrCreate(
                ['payment_id' => $payment->payment_id, 'entry_type' => ProviderLedgerEntry::TYPE_COMMISSION],
                [
                    'payable_type' => $payable->getMorphClass(),
                    'payable_id' => $payable->getKey(),
                    'booking_id' => $booking->booking_id,
                    'amount' => Money::negate((string) $payment->commission_amount),
                    'currency' => $currency,
                    'description' => 'Platform commission ('.$payment->commission_rate.'%) on booking #'.$booking->booking_id,
                    'created_at' => now(),
                ],
            );
        }
    }

    /**
     * The financially responsible party for a booking: the service/event
     * provider, or the tour guide for guide bookings.
     */
    public function resolvePayable(Booking $booking): ?Model
    {
        if ($booking->service_id !== null) {
            return $booking->tourismService?->serviceProvider;
        }

        if ($booking->guide_id !== null) {
            return $booking->tourGuide;
        }

        return $booking->eventReservation?->ticketType?->event?->serviceProvider;
    }

    /** @return class-string<Model>|null */
    public static function payableTypeFor(Model $payable): ?string
    {
        return in_array($payable::class, [ServiceProvider::class, TourGuide::class], true)
            ? $payable->getMorphClass()
            : null;
    }
}
