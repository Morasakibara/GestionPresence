<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Presence;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use Tests\TestCase;

class AdminReportExportTest extends TestCase
{
    

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function loginAdmin(): void
    {
        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->actingAs($admin);
    }

    /** GET /admin/generate-report affiche le formulaire */
    public function test_generate_report_form(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/generate-report');
        $response->assertOk();
        $response->assertSee('Générer');
    }

    /** POST /admin/generate-report avec format html affiche le rapport */
    public function test_generate_report_html(): void
    {
        $this->loginAdmin();

        $response = $this->post('/admin/generate-report', [
            'start_date' => now()->subWeek()->toDateString(),
            'end_date' => now()->toDateString(),
            'export_format' => 'html',
        ]);

        $response->assertOk();
    }

    /** POST /admin/report exporte le rapport en PDF */
    public function test_export_report_pdf(): void
    {
        $this->loginAdmin();

        $response = $this->post('/admin/report', [
            'start_date' => now()->subWeek()->toDateString(),
            'end_date' => now()->toDateString(),
        ]);

        // PDF renvoie un 200 avec un contenu binaire
        $response->assertStatus(200);
    }

    /** GET /admin/evaluations/export génère un Excel */
    public function test_export_evaluations_csv(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/evaluations/export?mois=' . now()->format('Y-m'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** GET /admin/evaluations/evolution/export génère un CSV */
    public function test_export_evolution_csv(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/evaluations/evolution/export');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** POST /admin/evaluations enregistre une évaluation */
    public function test_store_evaluation(): void
    {
        $this->loginAdmin();

        $employe = Employer::first();
        if (!$employe) {
            $this->markTestSkipped('No employer in test DB');
        }

        $response = $this->post('/admin/evaluations', [
            'employerID' => $employe->id,
            'mois' => now()->format('Y-m'),
            'note' => 16,
            'couleur' => 'vert',
            'commentaire' => 'Excellent travail',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** GET /admin/employe/{id}/bulletin génère le bulletin PDF */
    public function test_evaluation_bulletin(): void
    {
        $this->loginAdmin();

        $employe = Employer::first();
        if (!$employe) {
            $this->markTestSkipped('No employer in test DB');
        }

        $response = $this->get("/admin/employe/{$employe->id}/bulletin?mois=" . now()->format('Y-m'));
        $response->assertStatus(200);
    }

    /** Validation : dates requises */
    public function test_generate_report_validation(): void
    {
        $this->loginAdmin();

        $response = $this->post('/admin/generate-report', []);
        $response->assertSessionHasErrors(['start_date', 'end_date', 'export_format']);
    }
}
