<?php
// app/Http/Resources/Admin/AdminOrderResource.php
namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'reference'      => $this->reference,
            'status'         => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'subtotal'       => $this->subtotal,
            'delivery_fee'   => $this->delivery_fee,
            'discount'       => $this->discount,
            'total_amount'   => $this->total_amount,
            'delivery_date'  => $this->delivery_date?->format('d/m/Y'),
            'delivery_slot'  => $this->delivery_slot,
            'created_at'     => $this->created_at->format('d/m/Y H:i'),
            'user'           => $this->whenLoaded('user', fn() => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
            ]),
            'deliverer' => $this->whenLoaded('deliverer', fn() => [
                'id'    => $this->deliverer->id,
                'name'  => $this->deliverer->name,
                'phone' => $this->deliverer->phone,
            ]),
            'items' => $this->whenLoaded('items'),
        ];
    }
}
