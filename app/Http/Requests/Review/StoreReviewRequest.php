<?php
// app/Http/Requests/Review/StoreReviewRequest.php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'meal_kit_id' => 'required|exists:meal_kits,id',
            'order_id'    => 'required|exists:orders,id',
            'rating'      => 'required|integer|between:1,5',
            'comment'     => 'nullable|string|max:1000',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
