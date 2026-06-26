<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => (int)$this->id,
            'status'       => $this->status,
            'total_amount' => (float) $this->total_amount,
            'items'        => OrderItemResource::collection($this->whenLoaded('items')),
            'payments'     => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
        ];
    }
}