<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id','meal_kit_id','address_id','portions','frequency',
        'delivery_slot','status','next_delivery_date','pause_weeks'
    ];
    protected $casts = ['next_delivery_date' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function kit()  { return $this->belongsTo(MealKit::class, 'meal_kit_id'); }
}
