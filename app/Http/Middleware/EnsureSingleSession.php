<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Impose une seule session active par compte.
 *
 * À chaque connexion, un jeton unique est stocké en base (empreinte) et en session.
 * Si le jeton de la session courante ne correspond plus à celui en base — parce
 * qu'une connexion a eu lieu sur un autre appareil — on ferme cette session.
 * N'exige PAS une colonne session sur l'API (Sanctum) : réservé au web connecté.
 */
class EnsureSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'jetonSessionValide')) {
            // Jeton absent côté session (vieux navigateur) ou désaccord avec la base
            // → cette session n'est plus la référence : on la déconnecte.
            if (!$user->jetonSessionValide($request->session()->get('session_token'))) {
                \App\Http\Controllers\PinController::clearPinSession();
                \App\Http\Controllers\PinController::forgetRecoveryCookie();

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('error',
                    'Votre compte vient d\'être utilisé sur un autre appareil. ' .
                    'Cette session a été fermée pour respecter la connexion unique.');
            }
        }

        return $next($request);
    }
}
