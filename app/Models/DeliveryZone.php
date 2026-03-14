<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $fillable = ['name','city','delivery_fee','estimated_minutes','is_active'];
    protected $casts    = ['is_active' => 'boolean'];
}
