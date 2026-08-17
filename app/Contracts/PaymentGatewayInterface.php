<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /** @return array<string, mixed> */
    public function initializeTransaction(array $payload): array;

    /** @return array<string, mixed> */
    public function verifyTransaction(string $transactionReference): array;
}
