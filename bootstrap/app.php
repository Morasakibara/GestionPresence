<?php

use App\Http\Middleware\CheckUserRoleAndNetwork;
use App\Http\Middleware\EnsureSingleSession;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\NoCacheBackend;
use App\Http\Middleware\RegistrationAccessMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SuperAdminOnly;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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
            'isAdmin' => IsAdmin::class,
            'check.role.network' => CheckUserRoleAndNetwork::class,
            'registration.access' => RegistrationAccessMiddleware::class,
            'role' => RoleMiddleware::class,
            'superadmin' => SuperAdminOnly::class,
        ]);

        // Pages authentifiées en no-store (bouton « retour » après logout renvoie au
        // login) + connexion unique par compte (une seule session active à la fois).
        $middleware->web(append: [
            NoCacheBackend::class,
            EnsureSingleSession::class,
        ]);

        // Remplacer le CSRF natif par notre version (désactive en testing)
        $middleware->replaceInGroup(
            'web',
            ValidateCsrfToken::class,
            VerifyCsrfToken::class
        );
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('presence:auto-absences')->dailyAt('18:45');
        $schedule->command('presence:alertes-evaluations-rouges')->monthlyOn(1, '08:00');
        $schedule->command('presence:rappel-fiches-rendement')->weeklyOn(5, '17:00');

        // Envois WhatsApp : traitement de la file (database) chaque minute
        $schedule->command('queue:work --stop-when-empty --max-time=50')
            ->everyMinute()
            ->withoutOverlapping();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Rediriger les rate-limits vers la page de login avec un message clair
        $exceptions->renderable(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Trop de requêtes. Réessayez dans quelques instants.',
                ], 429);
            }

            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
            $minutes = ceil($retryAfter / 60);

            return redirect()
                ->route('login')
                ->withErrors(['email' => "Trop de tentatives. Réessayez dans {$minutes} minute(s)."])
                ->withInput($request->only('email'));
        });
    })
    ->create();
