<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ── Inscription ───────────────────────────────────────────
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => $request->password,
            'role'     => 'client',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inscription réussie.',
            'user'    => new UserResource($user),
            'token'   => $token,
        ], 201);
    }

    // ── Connexion ─────────────────────────────────────────────
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect.'
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Votre compte a été désactivé.'
            ], 403);
        }

        // Supprimer les anciens tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie.',
            'user'    => new UserResource($user),
            'token'   => $token,
        ]);
    }

    // ── Déconnexion ───────────────────────────────────────────
    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    // ── Profil de l'utilisateur connecté ──────────────────────
    public function me(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        return new UserResource($user);
    }

    // ── Mot de passe oublié (Mock) ────────────────────────────
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => "Aucun compte n'est associé à cette adresse email."
        ]);

        // Simuler un délai d'envoi d'email
        sleep(1);

        return response()->json([
            'message' => 'Un email de réinitialisation a été envoyé à votre adresse.'
        ]);
    }
}
