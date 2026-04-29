<?php
// app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $activeSub = $this->subscriptions()->where('status', 'active')->first();
        
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'email'  => $this->email,
            'phone'  => $this->phone,
            'avatar' => $this->avatar,
            'role'   => $this->role,
            'has_active_subscription' => !!$activeSub,
            'subscription_details' => $activeSub ? [
                'meals_per_week' => $activeSub->meals_per_week ?? 3, // Fallback si non défini
                'portions' => $activeSub->portions ?? 2,
            ] : null,
        ];
    }
}
