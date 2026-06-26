<?php

namespace App\Payment\Gateways;

use App\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

class CreditCardGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly string $key,
        private readonly string $secret,
    ) {}

    public function process(array $data): array
    {
        // Simulated Credit Card processing
        // In production: integrate with Stripe/Braintree SDK here
        $success = rand(0, 9) !== 0;

        return [
            'status'         => $success ? 'successful' : 'failed',
            'transaction_id' => 'CC-' . strtoupper(Str::random(12)),
            'message'        => $success
                ? 'Credit card payment processed successfully.'
                : 'Credit card payment declined.',
        ];
    }

    public function getName(): string
    {
        return 'credit_card';
    }
}