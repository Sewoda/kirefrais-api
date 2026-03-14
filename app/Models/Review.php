<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id','meal_kit_id','order_id','rating','comment','photo_url','is_approved'];
    protected $casts    = ['is_approved' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
    public function kit()  { return $this->belongsTo(MealKit::class, 'meal_kit_id'); }
}
