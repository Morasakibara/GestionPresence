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
        if ($request->cookie('registration_access') !== 'granted') {
            return redirect()->route('index')
                ->with('error', 'Vous devez fournir un code d\'accès valide pour accéder à cette page');
        }

        return $next($request);
    }
}
