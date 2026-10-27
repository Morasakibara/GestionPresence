<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * N'empêche pas la mise en cache côté navigateur des pages authentifiées.
 *
 * Sans ces en-têtes, le bouton « retour » (et le bfcache mobile) peut réafficher
 * un dashboard déjà chargé après déconnexion. Avec no-store, le navigateur
 * re-demande la page au serveur : l'utilisateur déconnecté retombe alors sur le
 * login (protégé par les middlewares d'authentification) au lieu d'un écran périmé.
 */
class NoCacheBackend
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (Auth::check() || $request->session()->isStarted()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Wed, 11 Jan 1984 05:00:00 GMT');
        }

        return $response;
    }
}
