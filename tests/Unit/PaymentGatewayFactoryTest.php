<?php

namespace Tests\Unit;

use App\Exceptions\PaymentGatewayNotFoundException;
use App\Payment\Gateways\CreditCardGateway;
use App\Payment\Gateways\PaypalGateway;
use App\Payment\PaymentGatewayFactory;
use Tests\TestCase;

class PaymentGatewayFactoryTest extends TestCase
{
    private PaymentGatewayFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new PaymentGatewayFactory();
    }

    public function test_factory_resolves_paypal_gateway(): void
    {
        $gateway = $this->factory->make('paypal');
        $this->assertInstanceOf(PaypalGateway::class, $gateway);
    }

    public function test_factory_resolves_credit_card_gateway(): void
    {
        $gateway = $this->factory->make('credit_card');
        $this->assertInstanceOf(CreditCardGateway::class, $gateway);
    }

    public function test_factory_throws_for_unknown_gateway(): void
    {
        $this->expectException(PaymentGatewayNotFoundException::class);
        $this->factory->make('bitcoin');
    }

    public function test_strategy_pattern_all_gateways_implement_interface(): void
    {
        foreach ($this->factory->supportedGateways() as $name) {
            $gateway = $this->factory->make($name);
            $this->assertInstanceOf(\App\Payment\Contracts\PaymentGatewayInterface::class, $gateway);
        }
    }
}