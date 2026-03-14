<?php
// app/Http/Requests/Address/StoreAddressRequest.php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'label'            => 'required|string|max:50',
            'delivery_zone_id' => 'required|exists:delivery_zones,id',
            'address_text'     => 'required|string|min:5',
            'landmark'         => 'nullable|string|max:200',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'city'             => 'nullable|string|max:100',
            'is_default'       => 'boolean',
        ];
    }
}
