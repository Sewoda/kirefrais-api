<?php
// app/Http/Resources/OrderItemResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'kit'         => new KitResource($this->whenLoaded('kit')),
            'portions'    => $this->portions,
            'quantity'    => $this->quantity,
            'unit_price'  => $this->unit_price,
            'total_price' => $this->total_price,
        ];
    }
}
