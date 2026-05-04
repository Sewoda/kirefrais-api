<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password',
        'avatar', 'role', 'google_id', 'facebook_id', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token', 'google_id', 'facebook_id'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ── Helpers de rôle ──────────────────────────────────────
    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isLivreur(): bool { return $this->role === 'livreur'; }
    public function isClient(): bool  { return $this->role === 'client'; }

    // ── Relations ────────────────────────────────────────────
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function deliveries()
    {
        // Commandes assignées à ce livreur
        return $this->hasMany(Order::class, 'deliverer_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    protected $appends = ['has_active_subscription', 'weekly_kit_quota'];

    /**
     * Check if user has at least one active subscription.
     */
    public function getHasActiveSubscriptionAttribute(): bool
    {
        return $this->subscriptions()->where('status', 'active')->exists();
    }

    /**
     * Calculate the total number of kits allowed per week.
     * Legacy subscriptions (with meal_kit_id) count as 1.
     * Pack-based subscriptions use meals_per_week column.
     */
    public function getWeeklyKitQuotaAttribute(): int
    {
        $activeSubs = $this->subscriptions()->where('status', 'active')->get();
        
        return $activeSubs->reduce(function ($carry, $sub) {
            // Si c'est un pack (meals_per_week défini), on utilise sa valeur, sinon 1 kit par défaut
            return $carry + ($sub->meals_per_week ?? ($sub->meal_kit_id ? 1 : 0));
        }, 0);
    }

    public function favoriteKits()
    {
        return $this->belongsToMany(MealKit::class, 'favorite_meal_kit');
    }
}
