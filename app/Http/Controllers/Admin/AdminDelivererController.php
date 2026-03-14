<?php
// app/Http/Controllers/Admin/AdminDelivererController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Order};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminDelivererController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'livreur');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $livreurs = $query->withCount([
            'deliveries as total_deliveries',
            'deliveries as completed_deliveries' => fn($q) => $q->where('status', 'delivered'),
        ])->latest()->paginate(15);

        return response()->json($livreurs);
    }

    public function show(int $id)
    {
        $livreur = User::where('role', 'livreur')->findOrFail($id);

        $stats = [
            'total'     => Order::where('deliverer_id', $id)->count(),
            'completed' => Order::where('deliverer_id', $id)->where('status', 'delivered')->count(),
            'this_week' => Order::where('deliverer_id', $id)
                ->where('delivered_at', '>=', now()->startOfWeek())
                ->where('status', 'delivered')->count(),
        ];

        return response()->json(['livreur' => $livreur, 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        $livreur = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => $request->password,
            'role'     => 'livreur',
        ]);

        return response()->json(['message' => 'Livreur créé.', 'livreur' => $livreur], 201);
    }

    public function update(Request $request, int $id)
    {
        $livreur = User::where('role', 'livreur')->findOrFail($id);

        $request->validate([
            'name'  => 'sometimes|string|max:100',
            'email' => "sometimes|email|unique:users,email,{$id}",
            'phone' => 'sometimes|string',
        ]);

        $livreur->update($request->only('name', 'email', 'phone'));

        return response()->json(['message' => 'Livreur mis à jour.', 'livreur' => $livreur]);
    }

    public function toggle(int $id)
    {
        $livreur = User::where('role', 'livreur')->findOrFail($id);
        $livreur->update(['is_active' => !$livreur->is_active]);
        return response()->json([
            'message'   => $livreur->is_active ? 'Livreur activé.' : 'Livreur désactivé.',
            'is_active' => $livreur->is_active,
        ]);
    }
}
