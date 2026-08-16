<?php

namespace App\Providers;

use App\Models\Presence;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Nombre de présences suspectes non encore traitées (badge sidebar admin)
        View::composer('layouts.app', function ($view) {
            $view->with('suspectNonTraitees', Presence::where('suspect', true)
                ->where('statut_traitement', 'nouveau')
                ->count());
        });
    }
}
