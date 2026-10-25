<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Marquer;
use App\Models\Presence;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminEmployeeDeletionTest extends TestCase
{
    use DatabaseTransactions;

    private Utilisateur $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Utilisateur::create([
            'nom' => 'Admin Suppression',
            'email' => uniqid('admin.suppression.').'@test.com',
            'motDePasse' => bcrypt('password'),
            'role' => 'Administrateur',
        ]);
    }

    private function createSupervisor(): Utilisateur
    {
        $user = Utilisateur::create([
            'nom' => 'Superviseur Test',
            'email' => uniqid('superviseur.test.').'@test.com',
            'motDePasse' => bcrypt('password'),
            'role' => 'Superviseur',
        ]);

        Superviseur::create([
            'id' => $user->id,
            'equipe' => 'Équipe Test',
        ]);

        return $user;
    }

    private function createEmployer(Utilisateur $supervisor): Utilisateur
    {
        $user = Utilisateur::create([
            'nom' => 'Employé Test',
            'email' => uniqid('employe.test.').'@test.com',
            'motDePasse' => bcrypt('password'),
            'role' => 'Employer',
        ]);

        Employer::create([
            'id' => $user->id,
            'Sup_id' => $supervisor->id,
            'poste' => 'Employer',
        ]);

        return $user;
    }

    public function test_admin_supprime_un_employe_avec_son_historique(): void
    {
        $supervisor = $this->createSupervisor();
        $employer = $this->createEmployer($supervisor);

        $presence = Presence::create([
            'Sup_id' => $supervisor->id,
            'employerID' => $employer->id,
            'date' => now()->toDateString(),
            'status' => 'présent',
        ]);
        Marquer::create([
            'id' => $presence->id,
            'Empl_id' => $employer->id,
        ]);

        $this->actingAs($this->admin)
            ->post('/admin/delete-employee', ['email' => $employer->email])
            ->assertRedirect(route('admin.deleteEmployee'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('utilisateur', ['id' => $employer->id]);
        $this->assertDatabaseMissing('employer', ['id' => $employer->id]);
        $this->assertDatabaseMissing('presence', ['id' => $presence->id]);
        $this->assertDatabaseMissing('marquer', ['id' => $presence->id]);
    }

    public function test_admin_supprime_un_superviseur_sans_equipe_et_son_historique(): void
    {
        $supervisor = $this->createSupervisor();
        $otherSupervisor = $this->createSupervisor();
        $employer = $this->createEmployer($otherSupervisor);

        $presence = Presence::create([
            'Sup_id' => $supervisor->id,
            'employerID' => $employer->id,
            'date' => now()->toDateString(),
            'status' => 'présent',
        ]);

        $this->actingAs($this->admin)
            ->post('/admin/delete-employee', ['email' => $supervisor->email])
            ->assertRedirect(route('admin.deleteEmployee'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('utilisateur', ['id' => $supervisor->id]);
        $this->assertDatabaseMissing('superviseur', ['id' => $supervisor->id]);
        $this->assertDatabaseMissing('presence', ['id' => $presence->id]);
        $this->assertDatabaseHas('employer', ['id' => $employer->id]);
    }

    public function test_superviseur_encadrant_une_equipe_ne_peut_pas_etre_supprime(): void
    {
        $supervisor = $this->createSupervisor();
        $employer = $this->createEmployer($supervisor);

        $this->actingAs($this->admin)
            ->post('/admin/delete-employee', ['email' => $supervisor->email])
            ->assertRedirect(route('admin.deleteEmployee'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('utilisateur', ['id' => $supervisor->id]);
        $this->assertNotSoftDeleted('utilisateur', ['id' => $employer->id]);
        $this->assertDatabaseHas('superviseur', ['id' => $supervisor->id]);
        $this->assertDatabaseHas('employer', ['id' => $employer->id]);
    }

    public function test_admin_ne_peut_pas_se_supprimer_depuis_la_liste(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/delete-employee-from-list', ['email' => $this->admin->email])
            ->assertRedirect(route('admin.showEmployeeList'))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('utilisateur', ['id' => $this->admin->id]);
    }

    public function test_suppression_depuis_la_liste_soft_delete_le_compte(): void
    {
        $supervisor = $this->createSupervisor();
        $employer = $this->createEmployer($supervisor);

        $this->actingAs($this->admin)
            ->post('/admin/delete-employee-from-list', ['email' => $employer->email])
            ->assertRedirect(route('admin.showEmployeeList'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('utilisateur', ['id' => $employer->id]);
        $this->assertDatabaseMissing('employer', ['id' => $employer->id]);

        $this->actingAs($this->admin)
            ->get('/admin/calculate-presence')
            ->assertOk()
            ->assertDontSee($employer->email);
    }
}
