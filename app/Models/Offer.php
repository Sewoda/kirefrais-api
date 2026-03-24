<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'slug', 'name', 'persons', 'icon', 'description', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(OfferSubscription::class)->orderBy('sort_order');
    }
}
