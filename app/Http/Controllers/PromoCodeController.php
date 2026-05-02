<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    /**
     * Valide un code promo pour un client.
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $promo = PromoCode::where('code', strtoupper($request->code))->first();

        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'Code promo inexistant.'
            ], 404);
        }

        if (!$promo->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Ce code promo n\'est plus actif.'
            ], 400);
        }

        if ($promo->expires_at && now()->isAfter($promo->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce code promo a expiré.'
            ], 400);
        }

        if ($promo->max_uses && $promo->used_count >= $promo->max_uses) {
            return response()->json([
                'success' => false,
                'message' => 'Ce code promo a atteint sa limite d\'utilisation.'
            ], 400);
        }

        if ($request->amount < $promo->min_order) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant minimum pour utiliser ce code est de ' . number_format($promo->min_order, 0, ',', ' ') . ' FCFA.'
            ], 400);
        }

        $discount = $promo->calculateDiscount($request->amount);

        return response()->json([
            'success' => true,
            'message' => 'Code promo appliqué !',
            'discount' => $discount,
            'code' => $promo->code,
            'type' => $promo->type,
            'value' => $promo->value
        ]);
    }
}
