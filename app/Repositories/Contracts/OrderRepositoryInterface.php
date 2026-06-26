<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function getAllForUser(int $userId, array $filters): LengthAwarePaginator;

    public function findForUser(int $orderId, int $userId): Order;

    public function create(int $userId, array $data): Order;

    public function update(Order $order, array $data): Order;

    public function delete(Order $order): void;
}