<?php
// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'is_active'];

    public function kits()
    {
        return $this->hasMany(MealKit::class);
    }
}
