<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Commande;
use App\Models\ServiceFourni;
use App\Models\Retrait;
use App\Policies\CommandePolicy;
use App\Policies\ServiceFourniPolicy;
use App\Policies\RetraitPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Commande::class => CommandePolicy::class,
        ServiceFourni::class => ServiceFourniPolicy::class,
        Retrait::class => RetraitPolicy::class,
    ];

    public function boot(): void
    {
        // Admin bypass : l'admin peut tout faire
        Gate::before(function ($user) {
            if ($user->role === 'Administrateur') {
                return true;
            }
        });
    }
}
