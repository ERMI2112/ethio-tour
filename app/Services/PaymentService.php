<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentException;
use App\Models\Booking;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Throwable;

class PaymentService
{
    public const PAYABLE_BOOKING_STATUSES = ['accepted', 'payment_pending'];

    public function __construct(
        private readonly PaymentGatewayInterface $gateway,
        private readonly CommissionService $commission,
        private readonly LedgerService $ledger,
    ) {}

    public function canPay(Booking $booking): bool
    {
        return in_array($booking->status, self::PAYABLE_BOOKING_STATUSES, true)
            && $booking->total_amount !== null
            && Money::isPositive((string) $booking->total_amount)
            && trim((string) $booking->currency) !== '';
    }

    /** @return array{payment: Payment, checkout_url: string} */
    public function initialize(Booking $booking): array
    {
        if (! $this->canPay($booking)) {
            throw new PaymentException('This booking is not ready for payment.');
        }

        $failure = null;
        $result = DB::transaction(function () use ($booking, &$failure): array {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->booking_id);

            if (! $this->canPay($lockedBooking)) {
                throw new PaymentException('This booking is no longer ready for payment.');
            }

            $payment = Payment::query()->where('booking_id', $lockedBooking->booking_id)->lockForUpdate()->first();

            if ($payment?->status === 'success') {
                throw new PaymentException('This booking has already been paid.');
            }

            $txRef = 'ETHIO-'.strtoupper(bin2hex(random_bytes(12)));
            $payment ??= new Payment(['booking_id' => $lockedBooking->booking_id]);
            $payment->fill([
                'amount' => $lockedBooking->total_amount,
                'status' => 'pending',
                'payment_method' => 'chapa',
                'gateway_reference' => $txRef,
            ]);
            $payment->save();

            $tourist = $lockedBooking->tourist()->with('user')->first();
            $name = preg_split('/\s+/', trim((string) ($tourist?->full_name ?? 'Ethio Tour Guest')), 2);

            try {
                $response = $this->gateway->initializeTransaction([
                    'amount' => Money::normalize((string) $lockedBooking->total_amount),
                    'currency' => strtoupper((string) $lockedBooking->currency),
                    'email' => (string) ($tourist?->user?->email ?? ''),
                    'first_name' => $name[0] ?? 'Ethio',
                    'last_name' => $name[1] ?? 'Tour Guest',
                    'tx_ref' => $txRef,
                    'callback_url' => route('payments.chapa.callback'),
                    'return_url' => route('payments.chapa.callback'),
                    'customization' => [
                        'title' => 'Ethio Tour',
                        'description' => 'Payment for booking '.$lockedBooking->booking_id,
                    ],
                ]);
            } catch (Throwable $exception) {
                $payment->update(['status' => 'failed']);
                $failure = $exception;

                return ['payment' => $payment->fresh(), 'checkout_url' => ''];
            }

            $lockedBooking->update(['status' => 'payment_pending']);

            return [
                'payment' => $payment->fresh(),
                'checkout_url' => (string) data_get($response, 'data.checkout_url'),
            ];
        }, attempts: 3);

        if ($failure instanceof Throwable) {
            throw $failure instanceof PaymentException
                ? $failure
                : new PaymentException('Chapa payment initialization failed.', 0, $failure);
        }

        if ($result['checkout_url'] === '') {
            throw new PaymentException('The payment checkout URL was empty.');
        }

        return $result;
    }

    /** @return array{payment: Payment, booking: Booking, confirmed: bool} */
    public function verifyAndFinalize(string $transactionReference): array
    {
        if ($transactionReference === '') {
            throw new PaymentException('A transaction reference is required.');
        }

        $payment = Payment::query()->where('gateway_reference', $transactionReference)->first();

        if (! $payment) {
            throw new PaymentException('Payment transaction not found.');
        }

        $existingBooking = $payment->booking;

        if (! $existingBooking || $payment->amount === null || $existingBooking->total_amount === null || Money::compare((string) $payment->amount, (string) $existingBooking->total_amount) !== 0) {
            $payment->update(['status' => 'failed']);
            throw new PaymentException('Payment amount does not match the booking.');
        }

        if ($payment->status === 'success' && $existingBooking?->status === 'confirmed') {
            return ['payment' => $payment, 'booking' => $existingBooking, 'confirmed' => false];
        }

        try {
            $verification = $this->gateway->verifyTransaction($transactionReference);
        } catch (Throwable $exception) {
            throw $exception instanceof PaymentException
                ? $exception
                : new PaymentException('Chapa payment verification failed.', 0, $exception);
        }
        $data = is_array($verification['data'] ?? null) ? $verification['data'] : $verification;
        $verifiedReference = (string) ($data['tx_ref'] ?? $data['reference'] ?? $verification['tx_ref'] ?? '');
        $verifiedStatus = strtolower((string) ($data['status'] ?? $verification['status'] ?? ''));
        $verifiedCurrency = strtoupper((string) ($data['currency'] ?? ''));
        $verifiedAmount = Money::normalize((string) ($data['amount'] ?? '-1'));

        $matches = hash_equals($transactionReference, $verifiedReference)
            && $verifiedAmount === Money::normalize((string) $existingBooking->total_amount)
            && $verifiedCurrency === strtoupper((string) $payment->booking()->value('currency'))
            && in_array($verifiedStatus, ['success', 'successful', 'completed'], true);

        if (! $matches) {
            $payment->update(['status' => 'failed']);
            throw new PaymentException('Payment verification did not match the booking.');
        }

        return DB::transaction(function () use ($payment): array {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->payment_id);
            $booking = Booking::query()->lockForUpdate()->findOrFail($lockedPayment->booking_id);

            if ($lockedPayment->status === 'success' && $booking->status === 'confirmed') {
                return ['payment' => $lockedPayment, 'booking' => $booking, 'confirmed' => false];
            }

            if (! in_array($booking->status, ['accepted', 'payment_pending', 'confirmed'], true)) {
                throw new PaymentException('This booking cannot be confirmed by payment.');
            }

            $lockedPayment->update(['status' => 'success']);
            $confirmed = $booking->status !== 'confirmed';

            // Monetization: snapshot the provider's plan commission at the
            // moment the money settles. Guide bookings and providers in
            // trial (no active subscription) are not commissionable.
            $this->applyCommission($lockedPayment, $booking);

            // Financial ledger: record the consequences of this settlement
            // in the same transaction — payment, commission snapshot, and
            // ledger entries commit together or not at all. Recording is
            // idempotent, so duplicate webhook/callback delivery cannot
            // create duplicate entries.
            $this->ledger->recordPayment($lockedPayment->fresh());

            if ($confirmed) {
                $booking->update(['status' => 'confirmed']);
            }

            return ['payment' => $lockedPayment->fresh(), 'booking' => $booking->fresh(), 'confirmed' => $confirmed];
        });
    }

    private function applyCommission(Payment $payment, Booking $booking): void
    {
        $snapshot = $this->commission->snapshotFor($booking);

        if ($snapshot === null) {
            return;
        }

        $payment->update($snapshot);
    }
}
