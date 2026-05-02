<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    /**
     * Liste tous les administrateurs.
     */
    public function index()
    {
        $admins = User::where('role', 'admin')->latest()->get();
        return response()->json($admins);
    }

    /**
     * Enregistre un nouvel administrateur.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        $admin = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => $request->password, // Mutator/Hashed in model
            'role'      => 'admin',
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Administrateur créé avec succès.',
            'admin'   => $admin
        ], 201);
    }

    /**
     * Met à jour un administrateur.
     */
    public function update(Request $request, $id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->password) {
            $data['password'] = $request->password;
        }

        $admin->update($data);

        return response()->json([
            'message' => 'Administrateur mis à jour avec succès.',
            'admin'   => $admin
        ]);
    }

    /**
     * Supprime un administrateur.
     */
    public function destroy($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        // Empêcher l'auto-suppression
        if (auth()->id() === $admin->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        $admin->delete();

        return response()->json(['message' => 'Administrateur supprimé avec succès.']);
    }

    /**
     * Active/Désactive un administrateur.
     */
    public function toggleStatus($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        if (auth()->id() === $admin->id) {
            return response()->json(['message' => 'Vous ne pouvez pas désactiver votre propre compte.'], 403);
        }

        $admin->update(['is_active' => !$admin->is_active]);

        return response()->json([
            'message'   => $admin->is_active ? 'Compte activé.' : 'Compte désactivé.',
            'is_active' => $admin->is_active
        ]);
    }
}
