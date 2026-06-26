<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface
{
    public function getAll(int $userId): LengthAwarePaginator;

    public function getForOrder(int $orderId, int $userId): Collection;

    public function create(array $data): Payment;
}