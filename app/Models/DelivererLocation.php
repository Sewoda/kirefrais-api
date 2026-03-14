<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DelivererLocation extends Model
{
    protected $fillable = ['deliverer_id','order_id','latitude','longitude'];
}
