<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user  = User::factory()->create();
        $this->token = auth()->login($this->user);
    }

    private function authHeader(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_can_process_paypal_payment_for_confirmed_order(): void
    {
        $order = Order::factory()->create([
            'user_id'      => $this->user->id,
            'status'       => 'confirmed',
            'total_amount' => 100.00,
        ]);

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'gateway'  => 'paypal',
        ], $this->authHeader());

        $response->assertStatus(201)
            ->assertJsonPath('data.gateway', 'paypal')
            ->assertJsonStructure(['data' => ['payment_uuid', 'status']]);
    }

    public function test_can_process_credit_card_payment(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'confirmed',
        ]);

        $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'gateway'  => 'credit_card',
        ], $this->authHeader())->assertStatus(201);
    }

    public function test_cannot_pay_pending_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'pending',
        ]);

        $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'gateway'  => 'paypal',
        ], $this->authHeader())
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_invalid_gateway_returns_validation_error(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'confirmed',
        ]);

        $this->postJson('/api/payments', [
            'order_id' => $order->id,
            'gateway'  => 'bitcoin',
        ], $this->authHeader())
            ->assertStatus(422);
    }
}