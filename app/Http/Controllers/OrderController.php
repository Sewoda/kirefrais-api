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

        // Calculer le sous-total
        $subtotal = 0;
        foreach ($request->items as $item) {
            $kit       = MealKit::findOrFail($item['meal_kit_id']);
            $price     = $kit->getPriceByPortions($item['portions']);
            $subtotal += $price * $item['quantity'];
        }

        // Frais de livraison depuis la zone
        $zone        = \App\Models\DeliveryZone::findOrFail($request->delivery_zone_id);
        $deliveryFee = $zone->delivery_fee;

        // Appliquer le code promo
        $discount  = 0;
        $promoCode = null;
        if ($request->promo_code) {
            $promoCode = PromoCode::where('code', $request->promo_code)->first();
            if ($promoCode && $promoCode->isValid((float)$subtotal)) {
                $discount = $promoCode->calculateDiscount((float)$subtotal);
            }
        }

        $total = $subtotal + $deliveryFee - $discount;

        // Créer la commande
        $order = Order::create([
            'user_id'          => $user->id,
            'address_id'       => $request->address_id,
            'delivery_zone_id' => $request->delivery_zone_id,
            'status'           => 'pending',
            'subtotal'         => $subtotal,
            'delivery_fee'     => $deliveryFee,
            'discount'         => $discount,
            'total_amount'     => $total,
            'payment_method'   => $request->payment_method,
            'payment_status'   => 'pending',
            'delivery_date'    => $request->delivery_date,
            'delivery_slot'    => $request->delivery_slot,
            'is_subscription'  => $request->is_subscription ?? false,
            'promo_code'       => $request->promo_code,
            'notes'            => $request->notes,
        ]);

        // Créer les lignes de commande
        foreach ($request->items as $item) {
            $kit   = MealKit::find($item['meal_kit_id']);
            $price = $kit->getPriceByPortions($item['portions']);
            OrderItem::create([
                'order_id'    => $order->id,
                'meal_kit_id' => $item['meal_kit_id'],
                'portions'    => $item['portions'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $price,
                'total_price' => $price * $item['quantity'],
            ]);
        }

        // Incrémenter l'usage du code promo
        if ($promoCode) {
            $promoCode->increment('used_count');
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
