<?php

namespace App\Exceptions;

use Exception;

class PaymentGatewayNotFoundException extends Exception
{
    public function __construct(string $gateway)
    {
        parent::__construct("Payment gateway '{$gateway}' is not supported.");
    }
}