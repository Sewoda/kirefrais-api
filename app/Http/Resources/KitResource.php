<?php
// app/Http/Resources/KitResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KitResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'description'  => $this->description,
            'ingredients'  => $this->ingredients,
            'images'       => $this->images,
            'prep_time'    => $this->prep_time,
            'difficulty'   => $this->difficulty,
            'nutrition'    => [
                'calories' => $this->calories,
                'proteins' => $this->proteins,
                'carbs'    => $this->carbs,
                'fats'     => $this->fats,
                'fiber'    => $this->fiber,
            ],
            'prices'       => [
                '1p' => $this->price_1p,
                '2p' => $this->price_2p,
                '4p' => $this->price_4p,
            ],
            'is_vegetarian' => $this->is_vegetarian,
            'is_new'        => $this->is_new,
            'rating_avg'    => $this->rating_avg,
            'rating_count'  => $this->rating_count,
            'category_id'   => $this->category_id,
            'video_url'     => $this->video_url,
            'recipe_steps'  => $this->recipe_steps,
            'category'      => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
            ]),
        ];
    }
}
