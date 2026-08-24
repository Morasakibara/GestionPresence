<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Tests\TestCase;

class AdminFinanceTest extends TestCase
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

    /** GET /admin/caisse affiche l'état des caisses */
    public function test_etat_caisse(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/caisse');
        $response->assertOk();
        $response->assertSee('Caisse');
    }

    /** GET /admin/stock affiche l'état du stock */
    public function test_etat_stock(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/stock');
        $response->assertOk();
        $response->assertSee('Stock');
    }

    /** GET /admin/paiements affiche les stats de paiements */
    public function test_paiement_stats(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/paiements');
        $response->assertOk();
        $response->assertSee('Paiement');
    }

    /** GET /admin/audit-logs affiche le journal d'audit */
    public function test_audit_logs(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/audit-logs');
        $response->assertOk();
    }

    /** GET /admin/metrics affiche les métriques système */
    public function test_metrics(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/metrics');
        $response->assertOk();
        $response->assertSee('Métriques');
        $response->assertSee('Erreurs');
        $response->assertSee('Requêtes');
    }

    /** Un employé ne peut PAS accéder aux pages admin finance (redirigé) */
    public function test_employe_cannot_access_finance(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        if (!$employe) {
            $this->markTestSkipped('No employer in test DB');
        }
        $this->actingAs($employe);

        // isAdmin middleware redirige (302) vers dashboard ou login
        $this->get('/admin/caisse')->assertStatus(302);
        $this->get('/admin/stock')->assertStatus(302);
        $this->get('/admin/paiements')->assertStatus(302);
        $this->get('/admin/metrics')->assertStatus(302);
    }
}
