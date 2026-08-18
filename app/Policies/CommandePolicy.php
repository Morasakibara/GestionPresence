<?php

namespace App\Policies;

use App\Models\Commande;
use App\Models\Utilisateur;

class CommandePolicy
{
    public function viewAny(Utilisateur $user): bool
    {
        return $user->role === 'Administrateur';
    }

    public function view(Utilisateur $user, Commande $commande): bool
    {
        return $user->role === 'Administrateur' || $commande->superviseur_id === $user->id;
    }

    public function create(Utilisateur $user): bool
    {
        return in_array($user->role, ['Superviseur', 'Administrateur']);
    }

    public function update(Utilisateur $user, Commande $commande): bool
    {
        return $user->role === 'Administrateur' || $commande->superviseur_id === $user->id;
    }

    public function delete(Utilisateur $user, Commande $commande): bool
    {
        return $user->role === 'Administrateur' || $commande->superviseur_id === $user->id;
    }
}
