<?php

namespace App\Policies;

use App\Models\Presence;
use App\Models\Utilisateur;
use Illuminate\Auth\Access\HandlesAuthorization;

class PresencePolicy
{
    use HandlesAuthorization;

    /**
     * Un employé ne peut voir que ses propres présences.
     * Un superviseur peut voir celles de son équipe.
     */
    public function viewAny(Utilisateur $user): bool
    {
        return in_array($user->role, ['Administrateur', 'Superviseur']);
    }

    public function view(Utilisateur $user, Presence $presence): bool
    {
        if ($user->role === 'Administrateur') return true;

        if ($user->role === 'Superviseur') {
            return $presence->superviseur_id === $user->id
                || $presence->employerID === $user->id;
        }

        return $presence->employerID === $user->id;
    }

    /**
     * Seul un employé connecté peut marquer sa propre présence.
     */
    public function create(Utilisateur $user): bool
    {
        return in_array($user->role, ['Employer', 'Superviseur']);
    }

    /**
     * Le superviseur peut valider/invalider une localisation.
     */
    public function update(Utilisateur $user, Presence $presence): bool
    {
        if ($user->role === 'Administrateur') return true;

        if ($user->role === 'Superviseur') {
            return $presence->superviseur_id === $user->id;
        }

        // L'employé peut modifier sa propre fiche de rendement
        return $presence->employerID === $user->id;
    }

    public function delete(Utilisateur $user, Presence $presence): bool
    {
        return $user->role === 'Administrateur';
    }
}
