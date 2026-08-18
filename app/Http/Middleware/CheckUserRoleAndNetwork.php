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
            return redirect()->route('auth.role_selection');
        }

         // Vérifier si l'URL correspond au rôle actuel
         $path = $request->path();

         // Ajouter des exceptions pour les chemins communs aux deux rôles
        $commonPaths = ['presence', 'mark-arrival', 'mark-departure','role-switch'];
        if (in_array($path, $commonPaths)) {
            return $next($request);
        }
        // Ajouter une condition pour éviter de rediriger si on est déjà sur la page de sélection de rôle
        if ($path === 'Auth/role_selection') {
            return $next($request);
        }

        // Vérifier si l'URL correspond au rôle actuel
        $path = $request->path();
       // Pour le rôle Employer
        if ($currentRole === 'Employer') {
            // Si l'URL ne commence PAS par 'user' et n'est pas déjà '/user/dashboard'
            if (!Str::startsWith($path, 'user') && $path !== 'user/dashboard') {
                return redirect('/user/dashboard');
            }
        }
        // Pour le rôle Superviseur — autoriser les préfixes superviseur + postes spécialisés
        elseif ($currentRole === 'Superviseur') {
            $allowedPrefixes = ['superviseur', 'directrice', 'secretaire', 'gestionnaire'];
            $allowed = false;
            foreach ($allowedPrefixes as $prefix) {
                if (Str::startsWith($path, $prefix)) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed) {
                return redirect('/superviseur/supdashboard');
            }
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
