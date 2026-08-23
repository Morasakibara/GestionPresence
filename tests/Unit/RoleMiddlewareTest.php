<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Tests du RoleMiddleware — utilise des routes de test.
 * Pas de DB : le middleware vérifie juste le rôle de l'utilisateur.
 */
class RoleMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Routes de test
        Route::middleware(['auth', 'role:Administrateur,Superviseur'])
            ->get('/test/admin-or-sup', fn() => response()->json(['ok' => true]));

        Route::middleware(['auth', 'role:Administrateur'])
            ->get('/test/admin-only', fn() => response()->json(['ok' => true]));

        Route::middleware(['auth', 'role:Employer'])
            ->get('/test/employe-only', fn() => response()->json(['ok' => true]));
    }

    private function fakeLogin(string $role): void
    {
        $user = new \App\Models\Utilisateur();
        $user->id = 999;
        $user->role = $role;
        $user->nom = 'Test';
        $user->email = 'test@test.com';
        Auth::login($user);
    }

    // ─── role:Administrateur,Superviseur ───────────────────────────

    public function test_admin_passes(): void
    {
        $this->fakeLogin('Administrateur');
        $this->get('/test/admin-or-sup')->assertOk();
    }

    public function test_superviseur_passes(): void
    {
        $this->fakeLogin('Superviseur');
        $this->get('/test/admin-or-sup')->assertOk();
    }

    public function test_employe_blocked(): void
    {
        $this->fakeLogin('Employer');
        $this->get('/test/admin-or-sup')->assertStatus(403);
    }

    // ─── role:Administrateur only ──────────────────────────────────

    public function test_admin_passes_admin_only(): void
    {
        $this->fakeLogin('Administrateur');
        $this->get('/test/admin-only')->assertOk();
    }

    public function test_superviseur_blocked_admin_only(): void
    {
        $this->fakeLogin('Superviseur');
        $this->get('/test/admin-only')->assertStatus(403);
    }

    // ─── role:Employer only ────────────────────────────────────────

    public function test_employe_passes_employe_only(): void
    {
        $this->fakeLogin('Employer');
        $this->get('/test/employe-only')->assertOk();
    }

    public function test_admin_bypasses_employe_only(): void
    {
        $this->fakeLogin('Administrateur');
        $this->get('/test/employe-only')->assertOk(); // Gate::before bypass
    }

    // ─── unauthenticated ───────────────────────────────────────────

    public function test_unauthenticated_redirected(): void
    {
        $this->get('/test/admin-or-sup')->assertRedirect();
    }
}
