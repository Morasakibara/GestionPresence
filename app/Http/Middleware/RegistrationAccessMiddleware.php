<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Vérifier si l'accès est accordé et s'il n'a pas expiré
        if (!session('registration_access_granted') ||
            session('registration_access_time') < now()->subMinutes(30)->timestamp) {

            // Supprimer les variables de session expirées
            session()->forget(['registration_access_granted', 'registration_access_time']);

            return redirect()->route('index')->with('error', 'Vous devez fournir un code d\'accès valide pour accéder à cette page');
        }

        return $next($request);
    }
}
