<?php
// app/Http/Controllers/DeliveryController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\DelivererLocation;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use App\Events\DelivererLocationUpdated;

class DeliveryController extends Controller
{
    // ── Liste des livraisons assignées au livreur ─────────────
    public function index(Request $request)
    {
        $orders = Order::forDeliverer($request->user()->id)
            ->whereIn('status', ['ready', 'delivering'])
            ->with(['items.kit', 'address'])
            ->get();

        return OrderResource::collection($orders);
    }

    // ── Marquer une commande comme En Cours de Livraison ──────
    public function take(Request $request, int $id)
    {
        $order = Order::forDeliverer($request->user()->id)->findOrFail($id);

        $order->update(['status' => 'delivering']);

        return response()->json([
            'message' => 'Commande en cours de livraison.',
            'order'   => new OrderResource($order->load('address')),
        ]);
    }

    // ── Mettre à jour la position GPS (temps réel) ────────────
    public function updateLocation(Request $request, int $id)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $order = Order::forDeliverer($request->user()->id)->findOrFail($id);

        // Sauvegarder la position
        DelivererLocation::create([
            'deliverer_id' => $request->user()->id,
            'order_id'     => $order->id,
            'latitude'     => $request->latitude,
            'longitude'    => $request->longitude,
        ]);

        // Diffuser l'événement Pusher pour le client
        broadcast(new DelivererLocationUpdated(
            $order->id,
            $request->latitude,
            $request->longitude
        ))->toOthers();

        return response()->json(['message' => 'Position GPS mise à jour.']);
    }

    // ── Confirmer la livraison ──────────────────────────────
    public function complete(Request $request, int $id)
    {
        $order = Order::forDeliverer($request->user()->id)->findOrFail($id);

        $order->update([
            'status'       => 'delivered',
            'delivered_at' => now()
        ]);

        return response()->json(['message' => 'Livraison terminée.']);
    }

    // ── Statistiques du livreur ──────────────────────────────
    public function stats(Request $request)
    {
        $user = $request->user();
        
        $totalDeliveries = Order::forDeliverer($user->id)
            ->where('status', 'delivered')
            ->count();
            
        $todayDeliveries = Order::forDeliverer($user->id)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', now())
            ->count();
            
        $pendingDeliveries = Order::forDeliverer($user->id)
            ->whereIn('status', ['ready', 'delivering'])
            ->count();

        return response()->json([
            'total_delivered' => $totalDeliveries,
            'today_delivered' => $todayDeliveries,
            'pending'         => $pendingDeliveries,
            'rating'          => 4.8, // Valeur factice pour le moment
        ]);
    }

    // ── Historique des livraisons ────────────────────────────
    public function history(Request $request)
    {
        $orders = Order::forDeliverer($request->user()->id)
            ->where('status', 'delivered')
            ->with(['items.kit', 'address'])
            ->latest('delivered_at')
            ->paginate(15);

        return OrderResource::collection($orders);
    }
}
