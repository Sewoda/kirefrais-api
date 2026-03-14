<?php
// app/Http/Resources/ReviewResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'rating'     => $this->rating,
            'comment'    => $this->comment,
            'photo_url'  => $this->photo_url,
            'is_approved'=> $this->is_approved,
            'created_at' => $this->created_at->format('d/m/Y'),
            'user'       => new UserResource($this->whenLoaded('user')),
            'kit'        => new KitResource($this->whenLoaded('kit')),
        ];
    }
}
