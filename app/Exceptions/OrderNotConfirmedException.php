<?php

namespace App\Exceptions;

use Exception;

class OrderNotConfirmedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Payment can only be processed for confirmed orders.');
    }
}