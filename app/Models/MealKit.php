<?php
// app/Models/MealKit.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class MealKit extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'ingredients',
        'images', 'prep_time', 'difficulty',
        'calories', 'proteins', 'carbs', 'fats', 'fiber',
        'price_1p', 'price_2p', 'price_4p',
        'is_vegetarian', 'is_new', 'is_active',
        'rating_avg', 'rating_count', 'order_count',
        'recipe_steps',
    ];

    protected $casts = [
        'images'        => 'array',
        'recipe_steps'  => 'array',
        'is_vegetarian' => 'boolean',
        'is_new'        => 'boolean',
        'is_active'     => 'boolean',
        'rating_avg'    => 'float',
        'price_1p'      => 'float',
        'price_2p'      => 'float',
        'price_4p'      => 'float',
    ];

    // ── Slug automatique depuis le nom ────────────────────────
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    // ── Relations ────────────────────────────────────────────
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Helper : Prix selon les portions ─────────────────────
    public function getPriceByPortions(int $portions): float
    {
        return match($portions) {
            1 => (float)$this->price_1p,
            2 => (float)$this->price_2p,
            4 => (float)$this->price_4p,
            default => (float)$this->price_1p,
        };
    }

    // ── Scopes ───────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeVegetarian($query)
    {
        return $query->where('is_vegetarian', true);
    }
}
