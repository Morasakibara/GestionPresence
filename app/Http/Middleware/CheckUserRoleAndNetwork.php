<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckUserRoleAndNetwork
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Vérification si l'utilisateur est connecté
        if (!$user) {
            return redirect('/')->with('error', 'Vous devez être connecté.');
        }

        // Vérification des rôles valides
        if (!in_array($user->role, ['Employer', 'Superviseur'])) {
            return redirect('/')->with('error', 'Accès non autorisé.');
        }

        // Vérification du rôle actuel (Employer ou Superviseur) pour le routing
        $currentRole = session('current_role', null);
        if (!$currentRole) {
            // Rediriger si aucun rôle n'a été sélectionné
            return redirect()->route('role.selection');
        }

        // Vérifier si l'URL correspond au rôle actuel
        $path = $request->path();
        if ($currentRole === 'Employer' && !Str::startsWith($path, 'user')) {
            return redirect('/user/dashboard');
        } elseif ($currentRole === 'Superviseur' && !Str::startsWith($path, 'Superviseur')) {
            return redirect('/superviseur/supdashboard');
        }

        // (Optionnel) Vérification du réseau Wi-Fi (activer si nécessaire)
        /*
        $allowedNetwork = "BUY_YOUR_DATA"; // Nom du réseau autorisé
        if ($request->ip() != $this->getAllowedWifiIp()) {
            return response('Cette application ne fonctionne que sur un réseau Wi-Fi de l\'entreprise.', 403);
        }
        */

        return $next($request);
    }

    /*
    private function getAllowedWifiIp()
    {
        // IP autorisée du réseau Wi-Fi
        return '192.168.0.1';
    }
    */
}
