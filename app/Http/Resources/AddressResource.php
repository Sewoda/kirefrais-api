<?php
// app/Http/Resources/AddressResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'label'        => $this->label,
            'address_text' => $this->address_text,
            'landmark'     => $this->landmark,
            'latitude'     => $this->latitude,
            'longitude'    => $this->longitude,
            'city'         => $this->city,
            'is_default'   => $this->is_default,
        ];
    }
}
