<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Exceptions\PaymentException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

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
        $response = $this->client()->post(rtrim((string) config('services.chapa.base_url'), '/').'/transaction/initialize', $payload);

        if ($response->failed()) {
            throw new PaymentException('Chapa could not initialize the payment.');
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
