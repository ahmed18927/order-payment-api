<?php

namespace App\Payment;

use App\Exceptions\PaymentGatewayNotFoundException;
use App\Payment\Contracts\PaymentGatewayInterface;
use App\Payment\Gateways\CreditCardGateway;
use App\Payment\Gateways\PaypalGateway;

class PaymentGatewayFactory
{
    /**
     * @var array<string, class-string<PaymentGatewayInterface>>
     */
    private array $gateways = [
        'paypal'      => PaypalGateway::class,
        'credit_card' => CreditCardGateway::class,
    ];

    public function make(string $gateway): PaymentGatewayInterface
    {
        if (! isset($this->gateways[$gateway])) {
            throw new PaymentGatewayNotFoundException($gateway);
        }

        $config = config("payment.gateways.{$gateway}");

        return new $this->gateways[$gateway](
            key: $config['key'],
            secret: $config['secret'],
        );
    }

    public function supportedGateways(): array
    {
        return array_keys($this->gateways);
    }
}