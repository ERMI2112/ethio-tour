<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChapaGateway implements PaymentGatewayInterface
{
    private function client(): PendingRequest
    {
        $secret = (string) config('services.chapa.secret_key');

        if ($secret === '') {
            throw new PaymentException('Chapa is not configured for this environment.');
        }

        return Http::withToken($secret)
            ->acceptJson()
            ->timeout(20);
    }

    public function initializeTransaction(array $payload): array
    {
        $normalizedPayload = $payload;
        $email = trim((string) ($payload['email'] ?? ''));
        $domain = (string) substr(strrchr($email, '@'), 1);
        $isDummyDomain = in_array(strtolower($domain), ['test.com', 'example.com', 'example.org', 'example.net', 'localhost', 'test', 'invalid', 'local'], true)
            || str_ends_with(strtolower($domain), '.test')
            || str_ends_with(strtolower($domain), '.local');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $isDummyDomain) {
            $localPart = $email !== '' ? explode('@', $email)[0] : 'traveler';
            $localPart = preg_replace('/[^a-zA-Z0-9._-]/', '', $localPart);
            $normalizedPayload['email'] = ($localPart !== '' ? $localPart : 'traveler').'.ethiotour@gmail.com';
        }

        $response = $this->client()->post(rtrim((string) config('services.chapa.base_url'), '/').'/transaction/initialize', $normalizedPayload);

        if ($response->failed()) {
            Log::error('Chapa init failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);

            $errorBody = $response->json();
            $msg = 'Chapa could not initialize the payment.';
            if (is_array($errorBody) && isset($errorBody['message'])) {
                if (is_array($errorBody['message'])) {
                    $errors = [];
                    foreach ($errorBody['message'] as $field => $errs) {
                        $errors[] = $field.': '.(is_array($errs) ? implode(', ', $errs) : $errs);
                    }
                    $msg .= ' ('.implode('; ', $errors).')';
                } elseif (is_string($errorBody['message']) && $errorBody['message'] !== '') {
                    $msg = $errorBody['message'];
                }
            }

            throw new PaymentException($msg);
        }

        $body = $response->json();

        if (! is_array($body) || strtolower((string) ($body['status'] ?? '')) !== 'success') {
            throw new PaymentException((string) ($body['message'] ?? 'Chapa rejected the payment request.'));
        }

        $checkoutUrl = data_get($body, 'data.checkout_url');

        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new PaymentException('Chapa did not return a hosted checkout URL.');
        }

        return $body;
    }

    public function verifyTransaction(string $transactionReference): array
    {
        $response = $this->client()->get(rtrim((string) config('services.chapa.base_url'), '/').'/transaction/verify/'.rawurlencode($transactionReference));

        if ($response->failed()) {
            throw new PaymentException('Chapa could not verify the payment.');
        }

        $body = $response->json();

        if (! is_array($body)) {
            throw new PaymentException('Chapa returned an invalid verification response.');
        }

        return $body;
    }
}
