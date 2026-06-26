<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // 'id'           => (int)$this->id,
            'payment_uuid' => $this->payment_uuid,
            'order_id'     => $this->order_id,
            'gateway'      => $this->gateway,
            'amount'       => (float) $this->amount,
            'status'       => $this->status,
            'created_at'   => $this->created_at->toDateTimeString(),
        ];
    }
}   