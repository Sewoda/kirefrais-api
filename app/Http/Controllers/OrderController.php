<?php
// app/Http/Controllers/OrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MealKit;
use App\Models\PromoCode;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // ── Créer une commande ────────────────────────────────────
    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();
        
        // 1. Idempotence Check : Éviter les doublons accidentels (même date, même contenu, 2 dernières min)
        $duplicate = Order::where('user_id', $user->id)
            ->where('delivery_date', $request->delivery_date)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->exists();
            
        if ($duplicate && !app()->environment('testing')) {
            return response()->json(['message' => 'Une commande similaire a déjà été passée récemment.'], 422);
        }

        $subtotal = 0;
        $totalKitsInOrder = collect($request->items)->sum('quantity');

        // 2. Logique de calcul du sous-total (Abonnement uniquement)
        if ($request->selected_pack_id) {
            // Achat d'un NOUVEAU pack
            $pack = \App\Models\OfferSubscription::findOrFail($request->selected_pack_id);
            $subtotal = $pack->price;
        } 
        elseif ($user->has_active_subscription) {
            // Utilisation d'un pack EXISTANT
            $quota = $user->weekly_kit_quota;

            if ($quota <= 0) {
                return response()->json([
                    'message' => "Votre abonnement est épuisé ou expiré. Veuillez en racheter un.",
                ], 422);
            }
            
            // Calcul de la consommation pour cette date
            $alreadyConsumed = \App\Models\OrderItem::whereHas('order', function($q) use ($user, $request) {
                $q->where('user_id', $user->id)
                  ->where('delivery_date', $request->delivery_date)
                  ->where('status', '!=', 'cancelled');
            })->sum('quantity');

            $availableQuota = max(0, $quota - $alreadyConsumed);

            if ($totalKitsInOrder > $availableQuota) {
                return response()->json([
                    'message' => "Quota insuffisant pour cette date. Disponible : {$availableQuota} kit(s).",
                ], 422);
            }

            $subtotal = 0; // Inclus dans l'abonnement
        } 
        else {
            // Ni pack sélectionné, ni abonnement actif
            return response()->json([
                'message' => "Vous devez sélectionner un abonnement pour commander.",
            ], 422);
        }

        // 3. Calcul final
        $address = null;
        $deliveryFee = 0;
        if ($request->address_id) {
            $address = \App\Models\Address::with('deliveryZone')->find($request->address_id);
            $deliveryFee = $address?->deliveryZone?->delivery_fee ?? 0;
        }

        $discount = 0;
        $promo = null;
        if ($request->promo_code) {
            $promo = \App\Models\PromoCode::where('code', $request->promo_code)->where('is_active', true)->first();
            if ($promo) {
                $discount = $promo->type === 'fixed' ? $promo->value : ($subtotal * ($promo->value / 100));
            }
        }

        $totalAmount = max(0, $subtotal + $deliveryFee - $discount);

        $order = Order::create([
            'user_id'         => $user->id,
            'offer_subscription_id' => $request->selected_pack_id,
            'address_id'      => $request->address_id,
            'delivery_zone_id' => $address?->delivery_zone_id,
            'subtotal'        => $subtotal,
            'delivery_fee'    => $deliveryFee,
            'discount'        => $discount,
            'total_amount'    => $totalAmount,
            'status'          => 'pending',
            'payment_status'  => 'pending',
            'payment_method'  => $request->payment_method ?? 'all',
            'delivery_date'   => $request->delivery_date,
            'delivery_slot'   => $request->delivery_slot,
            'notes'           => $request->notes,
            'is_subscription' => $request->is_subscription ?? false,
            'promo_code'      => $request->promo_code,
        ]);

        foreach ($request->items as $item) {
            $kit = \App\Models\MealKit::find($item['meal_kit_id']);
            // Si c'est un abonné qui utilise son quota, le prix unitaire est 0 (car inclus dans le pack déjà payé)
            $unitPrice = ($user->has_active_subscription && !$request->selected_pack_id) ? 0 : ($kit->price ?? 0);
            
            $order->items()->create([
                'meal_kit_id' => $item['meal_kit_id'],
                'portions'    => $item['portions'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $unitPrice,
                'total_price' => $unitPrice * $item['quantity'],
            ]);
        }

        // Incrémenter l'usage du code promo
        if ($promo) {
            $promo->increment('used_count');
        }

        return response()->json([
            'message' => 'Commande créée avec succès.',
            'order'   => new OrderResource($order->load(['items.kit', 'address'])),
        ], 201);
    }

    // ── Liste des commandes du client ─────────────────────────
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.kit', 'address'])
            ->latest()
            ->paginate(10);

        return OrderResource::collection($orders);
    }

    // ── Détail d'une commande ─────────────────────────────────
    public function show(Request $request, int $id)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with(['items.kit', 'address', 'deliverer'])
            ->findOrFail($id);

        return new OrderResource($order);
    }
}
