<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Redirige l'utilisateur vers le fournisseur d'authentification.
     */
    public function redirectToProvider($provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return response()->json(['message' => 'Fournisseur non supporté'], 400);
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Gère le retour du fournisseur d'authentification.
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return redirect(config('app.frontend_url') . '/auth/connexion?error=auth_failed');
        }

        // Trouver ou créer l'utilisateur
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(Str::random(24)),
                'role' => 'client',
                'is_active' => true,
            ]);
        }

        // Générer le token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Rediriger vers le frontend avec le token
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        return redirect($frontendUrl . '/auth/callback?token=' . $token);
    }
}
