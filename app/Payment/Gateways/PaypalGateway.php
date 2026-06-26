<?php

namespace App\Payment\Gateways;

use App\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

class PaypalGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly string $key,
        private readonly string $secret,
    ) {}

    public function process(array $data): array
    {
       
        $success = rand(0, 9) !== 0; 

        return [
            'status'         => $success ? 'successful' : 'failed',
            'transaction_id' => 'PP-' . strtoupper(Str::random(12)),
            'message'        => $success
                ? 'PayPal payment processed successfully.'
                : 'PayPal payment failed.',
        ];
    }

    public function getName(): string
    {
        return 'paypal';
    }
}