<?php
// app/Http/Resources/OrderResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'reference'        => $this->reference,
            'status'           => $this->status,
            'subtotal'         => $this->subtotal,
            'delivery_fee'     => $this->delivery_fee,
            'discount'         => $this->discount,
            'total_amount'     => $this->total_amount,
            'payment_method'   => $this->payment_method,
            'payment_status'   => $this->payment_status,
            'delivery_date'    => $this->delivery_date->format('Y-m-d'),
            'delivery_slot'    => $this->delivery_slot,
            'is_subscription'  => $this->is_subscription,
            'notes'            => $this->notes,
            'created_at'       => $this->created_at->format('d/m/Y H:i'),
            'items'            => OrderItemResource::collection($this->whenLoaded('items')),
            'address'          => $this->whenLoaded('address'),
            'deliverer'        => $this->whenLoaded('deliverer', fn() => [
                'name'  => $this->deliverer->name,
                'phone' => $this->deliverer->phone,
            ]),
        ];
    }
}
