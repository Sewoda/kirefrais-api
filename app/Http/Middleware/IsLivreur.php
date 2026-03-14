<?php
// app/Http/Middleware/IsLivreur.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsLivreur
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || (!$user->isLivreur() && !$user->isAdmin())) {
            return response()->json([
                'message' => 'Accès refusé. Rôle livreur requis.'
            ], 403);
        }
        return $next($request);
    }
}
