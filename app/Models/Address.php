<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['user_id', 'delivery_zone_id', 'label', 'address_text', 'landmark', 'latitude', 'longitude', 'city', 'is_default'];
    protected $casts    = ['is_default' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
    public function deliveryZone() { return $this->belongsTo(DeliveryZone::class); }
}
