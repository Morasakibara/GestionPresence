<?php

namespace App\Policies;

use App\Models\Utilisateur;
use App\Models\Employer;
use Illuminate\Auth\Access\HandlesAuthorization;

class UtilisateurPolicy
{
    use HandlesAuthorization;

    /**
     * Seul l'admin peut gérer les comptes utilisateurs.
     */
    public function viewAny(Utilisateur $user): bool
    {
        return $user->role === 'Administrateur';
    }

    public function view(Utilisateur $user, Utilisateur $target): bool
    {
        // Admin peut tout voir
        if ($user->role === 'Administrateur') return true;

        // Superviseur peut voir les membres de son équipe (via la table employer)
        if ($user->role === 'Superviseur') {
            $inTeam = Employer::where('Sup_id', $user->id)
                ->where('id', $target->id)
                ->exists();
            if ($inTeam) return true;
        }

        // Chacun peut voir son propre profil
        return $user->id === $target->id;
    }

    public function create(Utilisateur $user): bool
    {
        return $user->role === 'Administrateur';
    }

    public function update(Utilisateur $user, Utilisateur $target): bool
    {
        if ($user->role === 'Administrateur') return true;

        // Chacun peut modifier son propre profil (nom, avatar, mot de passe)
        return $user->id === $target->id;
    }

    public function delete(Utilisateur $user, Utilisateur $target): bool
    {
        return $user->role === 'Administrateur' && $user->id !== $target->id;
    }
}
