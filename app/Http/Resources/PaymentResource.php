<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray (Request $request): array
    {
        return [
            'payment_method' => $this->payment_method,
            'amount' => number_format($this->amount, 2),
            'status' => $this->status,
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}