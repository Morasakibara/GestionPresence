<?php

namespace App\Policies;

use App\Models\Conge;
use App\Models\Utilisateur;
use Illuminate\Auth\Access\HandlesAuthorization;

class CongePolicy
{
    use HandlesAuthorization;

    /**
     * Un employé ne voit que ses propres congés.
     * Le superviseur voit ceux de son équipe.
     * L'admin voit tout.
     */
    public function viewAny(Utilisateur $user): bool
    {
        return in_array($user->role, ['Administrateur', 'Superviseur']);
    }

    public function view(Utilisateur $user, Conge $conge): bool
    {
        if ($user->role === 'Administrateur') return true;

        if ($user->role === 'Superviseur') {
            return $conge->superviseur_id === $user->id || $conge->user_id === $user->id;
        }

        return $conge->user_id === $user->id;
    }

    /**
     * Tout employé/superviseur peut demander un congé.
     */
    public function create(Utilisateur $user): bool
    {
        return in_array($user->role, ['Employer', 'Superviseur']);
    }

    /**
     * Le superviseur peut approuver/refuser les congés de son équipe.
     * L'admin peut tout gérer.
     */
    public function update(Utilisateur $user, Conge $conge): bool
    {
        if ($user->role === 'Administrateur') return true;

        return $conge->superviseur_id === $user->id;
    }

    /**
     * L'employé peut annuler sa demande si elle est encore 'en_attente'.
     * L'admin/superviseur peut supprimer n'importe quel congé.
     */
    public function delete(Utilisateur $user, Conge $conge): bool
    {
        if ($user->role === 'Administrateur') return true;

        if ($conge->user_id === $user->id) {
            return $conge->statut === 'en_attente';
        }

        return $conge->superviseur_id === $user->id;
    }
}
