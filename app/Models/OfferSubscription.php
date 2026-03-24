<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferSubscription extends Model
{
    protected $fillable = [
        'offer_id', 'name', 'slug', 'meals_per_week', 'price',
        'description', 'features', 'popular', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'features'  => 'array',
        'popular'   => 'boolean',
        'is_active' => 'boolean',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
