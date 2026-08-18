<?php

namespace App\Policies;

use App\Models\Retrait;
use App\Models\Utilisateur;

class RetraitPolicy
{
    public function viewAny(Utilisateur $user): bool
    {
        return $user->role === 'Administrateur';
    }

    public function view(Utilisateur $user, Retrait $retrait): bool
    {
        return $user->role === 'Administrateur' || $retrait->superviseur_id === $user->id;
    }

    public function create(Utilisateur $user): bool
    {
        return in_array($user->role, ['Superviseur', 'Administrateur']);
    }

    public function update(Utilisateur $user, Retrait $retrait): bool
    {
        return $user->role === 'Administrateur' || $retrait->superviseur_id === $user->id;
    }

    public function delete(Utilisateur $user, Retrait $retrait): bool
    {
        return $user->role === 'Administrateur' || $retrait->superviseur_id === $user->id;
    }
}
