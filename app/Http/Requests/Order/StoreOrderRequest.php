<?php
// app/Http/Requests/Order/StoreOrderRequest.php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'address_id'          => 'required|exists:addresses,id',
            'delivery_zone_id'    => 'required|exists:delivery_zones,id',
            'delivery_date'       => 'required|date|after_or_equal:today',
            'delivery_slot'       => 'required|in:morning,afternoon,evening',
            'payment_method'      => 'required|in:flooz,tmoney,card,cash',
            'is_subscription'     => 'boolean',
            'promo_code'          => 'nullable|string|exists:promo_codes,code',
            'notes'               => 'nullable|string|max:500',
            'items'               => 'required|array|min:1',
            'items.*.meal_kit_id' => 'required|exists:meal_kits,id',
            'items.*.portions'    => 'required|in:1,2,4',
            'items.*.quantity'    => 'required|integer|min:1|max:10',
        ];
    }
}
