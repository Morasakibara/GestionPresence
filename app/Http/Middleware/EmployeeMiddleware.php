<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->role === 'Employer' || Auth::user()->role === 'Superviseur')) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Accès non autorisé.');
    }
}