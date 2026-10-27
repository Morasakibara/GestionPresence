<?php

namespace App\Http\Middleware;

use App\Http\Controllers\PinController;
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
            $jetonSession = $request->session()->get('session_token');

            // Jeton absent en session ET en base : session antérieure au mécanisme
            // (ou test actingAs) — rien à comparer, on laisse passer.
            // Sinon tout désaccord (session sans jeton alors que la base en porte un
            // actif, ou jeton périmé par une connexion sur un autre appareil) : on ferme.
            $desaccord = $jetonSession === null
                ? $user->session_token !== null
                : ! $user->jetonSessionValide($jetonSession);

            if ($desaccord) {
                PinController::clearPinSession();
                PinController::forgetRecoveryCookie();

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('error',
                    'Votre compte vient d\'être utilisé sur un autre appareil. '.
                    'Cette session a été fermée pour respecter la connexion unique.');
            }
        }

        return $next($request);
    }
}
