<?php

namespace Tests\Unit;

use App\Models\Utilisateur;
use App\Policies\UtilisateurPolicy;
use Tests\TestCase;

/**
 * Tests unitaires de UtilisateurPolicy — pure logique, pas de DB.
 * On mock les modèles pour isoler la vérification des règles.
 */
class UtilisateurPolicyTest extends TestCase
{

    private UtilisateurPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UtilisateurPolicy();
    }

    private function fakeUser(string $role, int $id = 1): Utilisateur
    {
        $user = new Utilisateur();
        $user->id = $id;
        $user->role = $role;
        return $user;
    }

    // ─── viewAny ────────────────────────────────────────────────────

    public function test_admin_can_view_any(): void
    {
        $admin = $this->fakeUser('Administrateur');
        $this->assertTrue($this->policy->viewAny($admin));
    }

    public function test_superviseur_cannot_view_any(): void
    {
        $sup = $this->fakeUser('Superviseur');
        $this->assertFalse($this->policy->viewAny($sup));
    }

    public function test_employe_cannot_view_any(): void
    {
        $emp = $this->fakeUser('Employer');
        $this->assertFalse($this->policy->viewAny($emp));
    }

    // ─── create ─────────────────────────────────────────────────────

    public function test_admin_can_create(): void
    {
        $admin = $this->fakeUser('Administrateur');
        $this->assertTrue($this->policy->create($admin));
    }

    public function test_superviseur_cannot_create(): void
    {
        $sup = $this->fakeUser('Superviseur');
        $this->assertFalse($this->policy->create($sup));
    }

    public function test_employe_cannot_create(): void
    {
        $emp = $this->fakeUser('Employer');
        $this->assertFalse($this->policy->create($emp));
    }

    // ─── update ─────────────────────────────────────────────────────

    public function test_admin_can_update_any(): void
    {
        $admin  = $this->fakeUser('Administrateur', 1);
        $target = $this->fakeUser('Employer', 2);
        $this->assertTrue($this->policy->update($admin, $target));
    }

    public function test_user_can_update_self(): void
    {
        $user = $this->fakeUser('Employer', 5);
        $this->assertTrue($this->policy->update($user, $user));
    }

    public function test_employe_cannot_update_other(): void
    {
        $emp1 = $this->fakeUser('Employer', 10);
        $emp2 = $this->fakeUser('Employer', 11);
        $this->assertFalse($this->policy->update($emp1, $emp2));
    }

    public function test_superviseur_cannot_update_other(): void
    {
        $sup    = $this->fakeUser('Superviseur', 1);
        $target = $this->fakeUser('Employer', 2);
        $this->assertFalse($this->policy->update($sup, $target));
    }

    // ─── delete ─────────────────────────────────────────────────────

    public function test_admin_can_delete_other(): void
    {
        $admin  = $this->fakeUser('Administrateur', 1);
        $target = $this->fakeUser('Employer', 2);
        $this->assertTrue($this->policy->delete($admin, $target));
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->fakeUser('Administrateur', 1);
        $this->assertFalse($this->policy->delete($admin, $admin));
    }

    public function test_superviseur_cannot_delete(): void
    {
        $sup    = $this->fakeUser('Superviseur', 1);
        $target = $this->fakeUser('Employer', 2);
        $this->assertFalse($this->policy->delete($sup, $target));
    }

    public function test_employe_cannot_delete(): void
    {
        $emp    = $this->fakeUser('Employer', 1);
        $target = $this->fakeUser('Employer', 2);
        $this->assertFalse($this->policy->delete($emp, $target));
    }
}
