<?php
// app/Http/Controllers/Admin/AdminOrderController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, User};
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user:id,name,phone,email', 'address', 'deliverer:id,name,phone']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->search) {
            $query->where('reference', 'like', '%' . $request->search . '%');
        }

        $orders = $query->latest()->paginate(20);

        return response()->json($orders);
    }

    public function show(int $id)
    {
        $order = Order::with([
            'user', 'address', 'zone',
            'deliverer:id,name,phone',
            'items.kit:id,name,slug,images',
        ])->findOrFail($id);

        return response()->json($order);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:paid,preparing,ready,delivering,delivered,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $order->update([
            'status'       => $request->status,
            'delivered_at' => $request->status === 'delivered' ? now() : $order->delivered_at,
        ]);

        if (class_exists(OrderStatusChanged::class)) {
            broadcast(new OrderStatusChanged($order));
        }

        $order->user->notifications()->create([
            'title' => $this->statusTitle($request->status),
            'body'  => "Votre commande {$order->reference} : " . $this->statusBody($request->status),
            'type'  => 'order_status',
            'data'  => ['order_id' => $order->id, 'status' => $request->status],
        ]);

        return response()->json(['message' => 'Statut mis à jour.', 'order' => $order]);
    }

    public function assignDeliverer(Request $request, int $id)
    {
        $request->validate([
            'deliverer_id' => 'required|exists:users,id',
        ]);

        $deliverer = User::where('id', $request->deliverer_id)
            ->where('role', 'livreur')
            ->where('is_active', true)
            ->firstOrFail();

        $order = Order::findOrFail($id);
        $order->update([
            'deliverer_id' => $deliverer->id,
            'status'       => 'ready',
        ]);

        return response()->json([
            'message'   => "Commande assignée à {$deliverer->name}.",
            'deliverer' => $deliverer->only('id', 'name', 'phone'),
        ]);
    }

    public function export(Request $request)
    {
        // Export CSV via maatwebsite/excel
        // return Excel::download(new OrdersExport($request->all()), 'commandes.xlsx');
        return response()->json(['message' => 'Export en cours...']);
    }

    private function statusTitle(string $status): string
    {
        return match($status) {
            'preparing'  => 'Votre commande est en préparation 👨‍🍳',
            'ready'      => 'Votre commande est prête !',
            'delivering' => 'Votre commande est en route 🚚',
            'delivered'  => 'Commande livrée avec succès ✅',
            'cancelled'  => 'Commande annulée',
            default      => 'Mise à jour de votre commande',
        };
    }

    private function statusBody(string $status): string
    {
        return match($status) {
            'preparing'  => 'nos cuisiniers préparent vos ingrédients.',
            'ready'      => 'un livreur va bientôt la prendre en charge.',
            'delivering' => 'votre livreur est en chemin.',
            'delivered'  => 'bon appétit !',
            'cancelled'  => 'vous serez remboursé sous 48h.',
            default      => 'statut mis à jour.',
        };
    }
}
