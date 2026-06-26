<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function getAll(int $userId): LengthAwarePaginator
    {
        return Payment::whereHas('order', fn($q) => $q->where('user_id', $userId))
            ->with('order')
            ->latest()
            ->paginate(10);
    }

    public function getForOrder(int $orderId, int $userId): Collection
    {
        Order::where('user_id', $userId)->findOrFail($orderId);

        return Payment::where('order_id', $orderId)->get();
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }
}