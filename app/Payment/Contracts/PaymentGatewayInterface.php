<?php

namespace App\Payment\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Process a payment and return the result.
     *
     * @param  array{amount: float, order_id: int, metadata?: array<string, mixed>}  $data
     * @return array{status: string, transaction_id: string, message: string}
     */
    public function process(array $data): array;

    public function getName(): string;
}