<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user  = User::factory()->create(['password' => bcrypt('password')]);
        $this->token = auth()->login($this->user);
    }

    private function authHeader(): array
    {
        return ['Authorization' => "Bearer {$this->token}"];
    }

    public function test_user_can_create_order(): void
    {
        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_name' => 'Laptop', 'quantity' => 1, 'price' => 999.99],
            ],
        ], $this->authHeader());

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', 999.99);

        $this->assertDatabaseHas('orders', ['user_id' => $this->user->id]);
        $this->assertDatabaseHas('order_items', ['product_name' => 'Laptop']);
    }

    public function test_user_can_list_orders(): void
    {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $this->getJson('/api/orders', $this->authHeader())
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_user_can_update_order_status(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $this->putJson("/api/orders/{$order->id}", ['status' => 'confirmed'], $this->authHeader())
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed');
    }

    public function test_user_can_delete_order(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $this->deleteJson("/api/orders/{$order->id}", [], $this->authHeader())
            ->assertStatus(200);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_cannot_delete_order_with_payments(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status'  => 'confirmed',
        ]);

        Payment::factory()->create(['order_id' => $order->id]);

        $this->deleteJson("/api/orders/{$order->id}", [], $this->authHeader())
            ->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_orders_can_be_filtered_by_status(): void
    {
        Order::factory()->create(['user_id' => $this->user->id, 'status' => 'confirmed']);
        Order::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);

        $response = $this->getJson('/api/orders?status=confirmed', $this->authHeader());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }
}