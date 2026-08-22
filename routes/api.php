<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\PresenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Le Pharaon (Mobile App)
|--------------------------------------------------------------------------
|
| Public:
|   POST /api/login        — authentification, retourne un token Sanctum
|
| Protégées (auth:sanctum):
|   GET  /api/user         — profil utilisateur
|   POST /api/logout       — déconnexion
|   POST /api/mark-arrival — marquer l'arrivée
|   POST /api/mark-departure — marquer le départ (fiche rendement)
|   GET  /api/presences    — historique présences
|   GET  /api/presences/today — présence du jour
|   GET  /api/commandes    — lister commandes (superviseurs)
|   POST /api/commandes    — enregistrer commande
|   GET  /api/services     — lister services (superviseurs)
|
*/

// ── Authentification ──
Route::post('/login', [AuthController::class, 'login']);

// ── Routes protégées ──
Route::middleware('auth:sanctum')->group(function () {
    // Profil
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Présence (pointage)
    Route::post('/mark-arrival', [PresenceController::class, 'markArrival']);
    Route::post('/mark-departure', [PresenceController::class, 'markDeparture']);
    Route::get('/presences', [PresenceController::class, 'index']);
    Route::get('/presences/today', [PresenceController::class, 'today']);

    // Commandes & Services (superviseurs spécialisés)
    Route::get('/commandes', [CommandeController::class, 'index']);
    Route::post('/commandes', [CommandeController::class, 'store']);
    Route::get('/services', [CommandeController::class, 'services']);
});
