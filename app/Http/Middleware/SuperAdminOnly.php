<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Réserve les pages de développement (métriques système) au super administrateur
 * défini dans SUPER_ADMIN_EMAILS.
 */
class SuperAdminOnly
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Accès réservé au super administrateur.');
        }

        return $next($request);
    }
}
