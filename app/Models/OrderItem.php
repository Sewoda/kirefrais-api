<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id','meal_kit_id','portions','quantity','unit_price','total_price'];

    public function kit()  { return $this->belongsTo(MealKit::class, 'meal_kit_id'); }
    public function order(){ return $this->belongsTo(Order::class); }
}
