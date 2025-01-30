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

        // Vérification du rôle
        if (!$user || !in_array($user->role, ['Employer', 'Superviseur'])) {
            return redirect('/')->withErrors('Vous n\'êtes pas autorisé.');
        }

        $user = Auth::user();

    // Vérification du rôle
    if (!$user || !in_array($user->role, ['Employer', 'Superviseur'])) {
        return redirect('/')->withErrors('Vous n\'êtes pas autorisé.');
    }

    // Vérification du rôle actuel (utilisateur ou superviseur)
    $currentRole = session('current_role', null);
    if (!$currentRole) {
        // Si aucun rôle n'est sélectionné, rediriger vers la page de sélection de rôle
        return redirect()->route('role.selection');
    }

    // Vérifier si l'URL correspond au rôle actuel
    $path = $request->path();
    if ($currentRole === 'Employer' && !Str::startsWith($path, 'Employer')) {
        return redirect('/user/dashboard');
    } elseif ($currentRole === 'Superviseur' && !Str::startsWith($path, 'Superviseur')) {
        return redirect('/superviseur/supdashboard');
    }

/*
        // Vérification du réseau Wi-Fi
        $allowedNetwork = "BUY_YOUR_DATA"; // Remplacer par le nom du réseau
        if ($request->ip() != $this->getAllowedWifiIp()) {
            return response('Cette application ne fonctionne que sur un réseau Wi-Fi de l\' entreprise.', 403);
        }*/

        return $next($request);
    }

   /* private function getAllowedWifiIp()
    {
        // Récupère l'IP du réseau Wi-Fi "actif" (personnaliser ici)
        return '192.168.0.1'; // Exemple d'IP du réseau Wi-Fi
    }*/

}
