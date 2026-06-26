<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $payments = $this->paymentService->listPayments(auth()->id());

        return PaymentResource::collection($payments);
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->processPayment(
            orderId: $request->order_id,
            gateway: $request->gateway,
            userId: auth()->id(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment processed.',
            'data'    => new PaymentResource($payment),
        ], 201);
    }

    public function orderPayments(int $orderId): JsonResponse
    {
        $payments = $this->paymentService->getOrderPayments($orderId, auth()->id());

        return response()->json([
            'success' => true,
            'data'    => PaymentResource::collection($payments),
        ]);
    }
}