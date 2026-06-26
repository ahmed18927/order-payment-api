<?php

namespace App\Services;

use App\Exceptions\OrderHasPaymentsException;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function listOrders(int $userId, array $filters): LengthAwarePaginator
    {
        return $this->orderRepository->getAllForUser($userId, $filters);
    }

    public function findOrder(int $orderId, int $userId): Order
    {
        return $this->orderRepository->findForUser($orderId, $userId);
    }

    public function createOrder(int $userId, array $data): Order
    {
        return DB::transaction(function () use ($userId, $data) {
            $totalAmount = collect($data['items'])->sum(
                fn($item) => $item['quantity'] * $item['price']
            );

            $order = $this->orderRepository->create($userId, [
                'total_amount' => $totalAmount,
            ]);

            // foreach ($data['items'] as $item) {
            //     $order->items()->create([
            //         'product_name' => $item['product_name'],
            //         'quantity'     => $item['quantity'],
            //         'price'        => $item['price'],
            //         'subtotal'     => $item['quantity'] * $item['price'],
            //     ]);
            // }

            $order->items()->createMany(
    collect($data['items'])
        ->map(fn ($item) => [
            'product_name' => $item['product_name'],
            'quantity'     => $item['quantity'],
            'price'        => $item['price'],
            'subtotal'     => $item['quantity'] * $item['price'],
        ])
        ->all()
);
            return $order->load('items');
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        return $this->orderRepository->update($order, $data);
    }

    public function deleteOrder(Order $order): void
    {
        if ($order->hasPayments()) {
            throw new OrderHasPaymentsException();
        }

        $this->orderRepository->delete($order);
    }
}