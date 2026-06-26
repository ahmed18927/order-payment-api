<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => (int)$this->id,
            'name'       => $this->name,
            'email'      => $this->email,
             'token' => $this->when(
                isset($this->token),
                $this->token
            ),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}