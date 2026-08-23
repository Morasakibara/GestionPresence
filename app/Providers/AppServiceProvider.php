<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // ─── Rate Limiter : Login ────────────────────────────────────────
        // 5 tentatives par minute par adresse IP — protège contre le brute-force
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->input('email') . '|' . $request->ip()
            )->responseCallback(function (Request $request, array $headers) {
                return response()->view('auth.login', [
                    'error' => 'Trop de tentatives de connexion. Réessayez dans ' . ceil(($headers['X-RateLimit-Reset'] - time()) / 60) . ' minute(s).',
                ], 429, $headers);
            });
        });

        // ─── Rate Limiter : Register ─────────────────────────────────────
        // 3 inscriptions par heure par IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        // ─── Rate Limiter : API ──────────────────────────────────────────
        // 60 requêtes par minute pour les routes API authentifiées
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // ─── Rate Limiter : API Login (mobile) ──────────────────────────
        // 5 tentatives par minute pour le login API mobile
        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
