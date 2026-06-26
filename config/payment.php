<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    |
    | To add a new gateway:
    | 1. Add its credentials here.
    | 2. Create a new Gateway class implementing PaymentGatewayInterface.
    | 3. Register it in PaymentGatewayFactory::$gateways array.
    | No other code changes needed.
    |
    */

    'gateways' => [
        'paypal' => [
            'key'    => env('PAYPAL_KEY', ''),
            'secret' => env('PAYPAL_SECRET', ''),
        ],

        'credit_card' => [
            'key'    => env('CREDIT_CARD_KEY', ''),
            'secret' => env('CREDIT_CARD_SECRET', ''),
        ],

        // Future gateway example:
        // 'stripe' => [
        //     'key'    => env('STRIPE_KEY', ''),
        //     'secret' => env('STRIPE_SECRET', ''),
        // ],
    ],
];