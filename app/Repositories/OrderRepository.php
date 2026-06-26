<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function getAllForUser(int $userId, array $filters): LengthAwarePaginator
    {
        $query = Order::with('items')
            ->where('user_id', $userId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate(10);
    }

    public function findForUser(int $orderId, int $userId): Order
    {
        return Order::with('items', 'payments')
            ->where('user_id', $userId)
            ->findOrFail($orderId);
    }

    public function create(int $userId, array $data): Order
    {
        return Order::create([
            'user_id'      => $userId,
            'status'       => 'pending',
            'total_amount' => $data['total_amount'],
        ]);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order->fresh();
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }
}