<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'user_id', 'address_id', 'delivery_zone_id', 'deliverer_id',
        'status', 'subtotal', 'delivery_fee', 'discount', 'total_amount',
        'payment_method', 'payment_status', 'payment_reference',
        'delivery_date', 'delivery_slot', 'delivered_at',
        'is_subscription', 'promo_code', 'notes',
    ];

    protected $casts = [
        'delivery_date'   => 'date',
        'delivered_at'    => 'datetime',
        'is_subscription' => 'boolean',
        'subtotal'        => 'float',
        'delivery_fee'    => 'float',
        'discount'        => 'float',
        'total_amount'    => 'float',
    ];

    // ── Génération automatique de la référence ────────────────
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $order->reference = 'FK-' . date('Y') . '-' . str_pad(
                static::count() + 1, 5, '0', STR_PAD_LEFT
            );
        });
    }

    // ── Relations ────────────────────────────────────────────
    public function user()        { return $this->belongsTo(User::class); }
    public function address()     { return $this->belongsTo(Address::class); }
    public function deliverer()   { return $this->belongsTo(User::class, 'deliverer_id'); }
    public function zone()        { return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id'); }
    public function items()       { return $this->hasMany(OrderItem::class); }
    public function reviews()     { return $this->hasMany(Review::class); }

    // ── Helpers de statut ─────────────────────────────────────
    public function isPending():    bool { return $this->status === 'pending'; }
    public function isPaid():       bool { return $this->status === 'paid'; }
    public function isDelivering(): bool { return $this->status === 'delivering'; }
    public function isDelivered():  bool { return $this->status === 'delivered'; }
    public function isCancelled():  bool { return $this->status === 'cancelled'; }

    // ── Scope ─────────────────────────────────────────────────
    public function scopeForDeliverer($query, $delivererId)
    {
        return $query->where('deliverer_id', $delivererId);
    }
}
