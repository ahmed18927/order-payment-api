<?php

namespace App\Services;

use App\Exceptions\OrderNotConfirmedException;
use App\Models\Order;
use App\Models\Payment;
use App\Payment\PaymentGatewayFactory;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly PaymentGatewayFactory $gatewayFactory,
        private readonly OrderService $orderService,
    ) {}

    public function listPayments(int $userId): LengthAwarePaginator
    {
        return $this->paymentRepository->getAll($userId);
    }

    public function getOrderPayments(int $orderId, int $userId): Collection
    {
        return $this->paymentRepository->getForOrder($orderId, $userId);
    }

    public function processPayment(int $orderId, string $gateway, int $userId): Payment
    {
        $order = $this->orderService->findOrder($orderId, $userId);

        if (! $order->isConfirmed()) {
            throw new OrderNotConfirmedException();
        }

        return DB::transaction(function () use ($order, $gateway) {
            $gatewayInstance = $this->gatewayFactory->make($gateway);

            $result = $gatewayInstance->process([
                'amount'   => $order->total_amount,
                'order_id' => $order->id,
            ]);

            return $this->paymentRepository->create([
                'order_id' => $order->id,
                'gateway'  => $gateway,
                'amount'   => $order->total_amount,
                'status'   => $result['status'],
            ]);
        });
    }
}