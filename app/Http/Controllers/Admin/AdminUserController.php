<?php
// app/Http/Controllers/Admin/AdminUserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Order};
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'client');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $clients = $query->withCount('orders')
            ->withSum(['orders as total_spent' => fn($q) =>
                $q->where('payment_status', 'paid')
            ], 'total_amount')
            ->latest()->paginate(20);

        return response()->json($clients);
    }

    public function show(int $id)
    {
        $client = User::where('role', 'client')->findOrFail($id);
        $orders = Order::where('user_id', $id)
            ->with('items.kit')->latest()->take(10)->get();

        return response()->json(['client' => $client, 'orders' => $orders]);
    }

    public function toggle(int $id)
    {
        $client = User::where('role', 'client')->findOrFail($id);
        $client->update(['is_active' => !$client->is_active]);
        return response()->json([
            'message'   => $client->is_active ? 'Compte activé.' : 'Compte suspendu.',
            'is_active' => $client->is_active,
        ]);
    }
}
