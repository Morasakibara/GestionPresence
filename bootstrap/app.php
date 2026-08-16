<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases de middlewares personnalisés de l'application
        $middleware->alias([
            'isAdmin' => \App\Http\Middleware\IsAdmin::class,
            'check.role.network' => \App\Http\Middleware\CheckUserRoleAndNetwork::class,
            'registration.access' => \App\Http\Middleware\RegistrationAccessMiddleware::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('presence:auto-absences')->dailyAt('18:45');
        // Alerte automatique des évaluations rouges : le 1er de chaque mois à 8h00
        $schedule->command('presence:alertes-evaluations-rouges')->monthlyOn(1, '08:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
