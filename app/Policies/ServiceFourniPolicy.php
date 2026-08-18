<?php

namespace App\Policies;

use App\Models\ServiceFourni;
use App\Models\Utilisateur;

class ServiceFourniPolicy
{
    public function viewAny(Utilisateur $user): bool
    {
        return $user->role === 'Administrateur';
    }

    public function view(Utilisateur $user, ServiceFourni $service): bool
    {
        return $user->role === 'Administrateur' || $service->superviseur_id === $user->id;
    }

    public function create(Utilisateur $user): bool
    {
        return in_array($user->role, ['Superviseur', 'Administrateur']);
    }

    public function update(Utilisateur $user, ServiceFourni $service): bool
    {
        return $user->role === 'Administrateur' || $service->superviseur_id === $user->id;
    }

    public function delete(Utilisateur $user, ServiceFourni $service): bool
    {
        return $user->role === 'Administrateur' || $service->superviseur_id === $user->id;
    }
}
