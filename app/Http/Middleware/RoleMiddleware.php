<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware centralisé de vérification de rôle.
 *
 * Usage dans les routes :
 *   ->middleware('role:Administrateur')
 *   ->middleware('role:Administrateur,Superviseur')
 *   ->middleware('role:Directrice,Secrétaire,Gestionnaire')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Connexion requise.');
        }

        // L'admin a toujours accès
        if ($user->role === 'Administrateur') {
            return $next($request);
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Accès non autorisé pour le rôle : ' . $user->role);
        }

        return $next($request);
    }
}
