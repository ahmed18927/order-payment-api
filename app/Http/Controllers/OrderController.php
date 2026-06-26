<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->orderService->listOrders(
            userId: auth()->id(),
            filters: $request->only('status'),
        );

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder(
            userId: auth()->id(),
            data: $request->validated(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully.',
            'data'    => new OrderResource($order),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->findOrder($id, auth()->id());

        return response()->json([
            'success' => true,
            'data'    => new OrderResource($order),
        ]);
    }

    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->findOrder($id, auth()->id());
        $order = $this->orderService->updateOrder($order, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.',
            'data'    => new OrderResource($order),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $order = $this->orderService->findOrder($id, auth()->id());
        $this->orderService->deleteOrder($order);

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully.',
        ]);
    }
}