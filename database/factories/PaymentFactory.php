<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_uuid' => (string) Str::uuid(),
            'order_id'     => Order::factory(),
            'gateway'      => $this->faker->randomElement(['paypal', 'credit_card']),
            'amount'       => $this->faker->randomFloat(2, 10, 1000),
            'status'       => $this->faker->randomElement(['pending', 'successful', 'failed']),
        ];
    }
}