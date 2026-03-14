<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = ['code','type','value','min_order','max_uses','used_count','is_active','expires_at'];

    public function isValid(float $orderAmount): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && now()->isAfter($this->expires_at)) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        if ($orderAmount < $this->min_order) return false;
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'fixed')   return min((float)$this->value, $amount);
        if ($this->type === 'percent') return (float)$amount * ((float)$this->value / 100);
        return 0;
    }
}
