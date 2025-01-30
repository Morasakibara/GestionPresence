<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsSuperviseur
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role == 'Superviseur') {
            return $next($request);
        }

        return redirect('/');
    }
}
