<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Commande;
use App\Models\ServiceFourni;
use App\Models\Retrait;
use App\Models\Utilisateur;
use App\Models\Presence;
use App\Models\Conge;
use App\Policies\CommandePolicy;
use App\Policies\ServiceFourniPolicy;
use App\Policies\RetraitPolicy;
use App\Policies\UtilisateurPolicy;
use App\Policies\PresencePolicy;
use App\Policies\CongePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Commande::class      => CommandePolicy::class,
        ServiceFourni::class => ServiceFourniPolicy::class,
        Retrait::class       => RetraitPolicy::class,
        Utilisateur::class   => UtilisateurPolicy::class,
        Presence::class      => PresencePolicy::class,
        Conge::class         => CongePolicy::class,
    ];

    public function boot(): void
    {
        // Admin bypass : l'admin peut tout faire
        Gate::before(function ($user) {
            if ($user->role === 'Administrateur') {
                return true;
            }
        });

        // Gates de rôle pour le middleware et les vues
        Gate::define('is-admin', fn($user) => $user->role === 'Administrateur');
        Gate::define('is-superviseur', fn($user) => $user->role === 'Superviseur');
        Gate::define('is-employe', fn($user) => $user->role === 'Employer');

        // Gates par type de superviseur (vérifient le model Superviseur)
        Gate::define('is-directrice', function ($user) {
            $info = \App\Models\Superviseur::find($user->id);
            return $info && $info->type_superviseur === \App\Models\Superviseur::TYPE_DIRECTRICE;
        });
        Gate::define('is-secretaire', function ($user) {
            $info = \App\Models\Superviseur::find($user->id);
            return $info && $info->type_superviseur === \App\Models\Superviseur::TYPE_SECRETAIRE;
        });
        Gate::define('is-gestionnaire', function ($user) {
            $info = \App\Models\Superviseur::find($user->id);
            return $info && $info->type_superviseur === \App\Models\Superviseur::TYPE_GESTIONNAIRE_STOCK;
        });
    }
}
