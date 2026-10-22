<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;

/**
 * Étape 1.2 — Matrice des rôles : le verrou manager.
 *
 * Les actions sensibles (supprimer, annuler ou modifier le prix d'une commande
 * ayant déjà un versement) exigent le mot de passe d'un compte Administrateur.
 * La validation est tracée dans le journal d'audit, signé avec la chaîne de hachage.
 */
class ManagerAuthService
{
    public const MODULE = 'securite';

    /**
     * Vérifie le mot de passe d'un gestionnaire (Administrateur).
     *
     * @return Utilisateur|null l'administrateur qui a valide, ou null.
     */
    public function valider(?string $motDePasse): ?Utilisateur
    {
        if (!$motDePasse) {
            return null;
        }

        return Utilisateur::where('role', 'Administrateur')
            ->orderBy('id')
            ->get()
            ->first(fn($admin) => Hash::check($motDePasse, $admin->motDePasse));
    }

    /**
     * Trace une utilisation du verrou manager dans le journal d'audit.
     */
    public function tracer(string $action, string $contexte, ?Utilisateur $gestionnaire, ?Utilisateur $demandeur = null): void
    {
        AuditService::log(
            $action,
            self::MODULE,
            $gestionnaire
                ? "Autorisation manager de {$gestionnaire->nom} — {$contexte}"
                : "Refus — mot de passe administration invalide — {$contexte}",
            [
                'contexte'       => $contexte,
                'demandeur_id'   => $demandeur?->id,
                'demandeur_nom'  => $demandeur?->nom ?? 'anonyme',
                'authentifie'    => (bool) $gestionnaire,
            ],
            null,
            request()
        );
    }
}
