<?php
// app/Http/Controllers/Admin/AdminPromoController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class AdminPromoController extends Controller
{
    public function index()
    {
        return response()->json(PromoCode::latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'       => 'required|string|max:30|unique:promo_codes,code',
            'type'       => 'required|in:fixed,percent',
            'value'      => 'required|numeric|min:1',
            'min_order'  => 'nullable|numeric|min:0',
            'max_uses'   => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $promo = PromoCode::create($request->all());
        return response()->json(['message' => 'Code promo créé.', 'promo' => $promo], 201);
    }

    public function update(Request $request, int $id)
    {
        $promo = PromoCode::findOrFail($id);
        $promo->update($request->all());
        return response()->json(['message' => 'Code promo mis à jour.', 'promo' => $promo]);
    }

    public function destroy(int $id)
    {
        PromoCode::findOrFail($id)->delete();
        return response()->json(['message' => 'Code promo supprimé.']);
    }

    public function toggle(int $id)
    {
        $promo = PromoCode::findOrFail($id);
        $promo->update(['is_active' => !$promo->is_active]);
        return response()->json(['is_active' => $promo->is_active]);
    }
}
