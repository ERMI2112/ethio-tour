<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentException;
use App\Models\Booking;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function initialize(Request $request, Booking $booking, PaymentService $payments): RedirectResponse
    {
        Gate::authorize('payTourist', $booking);

        try {
            $result = $payments->initialize($booking);
        } catch (PaymentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->away($result['checkout_url']);
    }

    public function callback(Request $request, PaymentService $payments, NotificationService $notifications): RedirectResponse
    {
        $reference = (string) $request->query('tx_ref', $request->query('trx_ref', ''));

        try {
            $result = $payments->verifyAndFinalize($reference);
        } catch (PaymentException $exception) {
            return redirect()->route('login')->with('error', $exception->getMessage());
        }

        if ($result['confirmed']) {
            $notifications->createForUser($result['booking']->tourist?->user, 'payment_success', 'Payment confirmed', 'Your payment for booking #'.$result['booking']->booking_id.' was verified and the booking is confirmed.');
        }

        if ($request->user() && (int) $result['booking']->tourist?->user?->user_id === (int) $request->user()->user_id) {
            return redirect()->route('tourist.reservations.show', $result['booking'])->with('success', 'Payment confirmed and booking secured.');
        }

        return redirect()->route('login')->with('success', 'Payment verified. Log in to view your confirmed booking.');
    }

    public function webhook(Request $request, PaymentService $payments, NotificationService $notifications): Response
    {
        $rawPayload = $request->getContent();
        $secret = (string) config('services.chapa.webhook_secret');
        $signature = (string) ($request->header('x-chapa-signature') ?: $request->header('chapa-signature'));

        if ($secret === '' || $signature === '' || ! hash_equals(hash_hmac('sha256', $rawPayload, $secret), $signature)) {
            return response(['message' => 'Invalid webhook signature.'], 401);
        }

        $payload = $request->json()->all();
        $reference = (string) data_get($payload, 'tx_ref', data_get($payload, 'data.tx_ref', data_get($payload, 'reference', '')));

        try {
            $result = $payments->verifyAndFinalize($reference);
        } catch (PaymentException $exception) {
            return response(['message' => $exception->getMessage()], 422);
        }

        if ($result['confirmed']) {
            $notifications->createForUser($result['booking']->tourist?->user, 'payment_success', 'Payment confirmed', 'Your payment for booking #'.$result['booking']->booking_id.' was verified and the booking is confirmed.');
        }

        return response(['message' => 'Webhook processed.'], 200);
    }
}
