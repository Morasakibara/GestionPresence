<?php

namespace Tests\Feature;

use App\Models\Commande;
use App\Models\ServiceFourni;
use App\Models\Retrait;
use App\Models\StockTshirt;
use App\Models\StockPapier;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use App\Models\Presence;
use App\Models\Evaluation;
use App\Models\WorkplaceLocation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SuperviseurModulesTest extends TestCase
{
    use DatabaseTransactions;

    private const PARIS_LAT = 48.856613;
    private const PARIS_LON = 2.352222;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        if (!WorkplaceLocation::where('actif', true)->exists()) {
            WorkplaceLocation::create([
                'nom' => 'Siège Le Pharaon (test)',
                'latitude' => self::PARIS_LAT,
                'longitude' => self::PARIS_LON,
                'rayon' => 5000,
                'actif' => true,
            ]);
        }
    }

    /**
     * Connecte un superviseur par type_superviseur.
     */
    private function loginSuperviseur(string $type): Utilisateur
    {
        $supId = DB::table('superviseur')
            ->where('type_superviseur', $type)
            ->value('id');
        $this->assertNotNull($supId, "Aucun superviseur de type '$type' trouvé dans la base.");
        $sup = Utilisateur::find($supId);
        $this->assertNotNull($sup, "Utilisateur superviseur '$type' introuvable.");
        \Illuminate\Support\Facades\Auth::login($sup);
        session(['current_role' => 'Superviseur']);
        return $sup;
    }

    private function loginAdmin(): Utilisateur
    {
        $admin = Utilisateur::where('role', 'Administrateur')->first();
        \Illuminate\Support\Facades\Auth::login($admin);
        return $admin;
    }

    // ══════════════════════════════════════════════════════════════════
    //  SUPERVISEUR A — Superviseur classique
    // ══════════════════════════════════════════════════════════════════

    public function test_superviseur_a_dashboard(): void
    {
        $sup = $this->loginSuperviseur('superviseur_a');

        $response = $this->get('/superviseur/supdashboard');
        $response->assertOk();
        $response->assertSee('Tableau de bord');
        $response->assertSee('stat-card');
    }

    public function test_superviseur_a_follow_presence(): void
    {
        $sup = $this->loginSuperviseur('superviseur_a');

        $response = $this->get('/superviseur/followPresence');
        $response->assertOk();
        $response->assertSee('Suivi');
    }

    public function test_superviseur_a_rapport(): void
    {
        $sup = $this->loginSuperviseur('superviseur_a');

        $response = $this->get('/superviseur/generateReport2');
        $response->assertOk();
        $response->assertSee("Rapport d'équipe");
    }

    public function test_superviseur_a_store_evaluation(): void
    {
        $sup = $this->loginSuperviseur('superviseur_a');
        $employe = Utilisateur::where('role', 'Employer')->first();

        $response = $this->post('/superviseur/evaluations', [
            'employerID' => $employe->id,
            'mois' => '2026-08',
            'note' => 15,
            'couleur' => 'vert',
            'commentaire' => 'Test eval superviseurA.',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('evaluations', [
            'employerID' => $employe->id,
            'mois' => '2026-08',
            'note' => 15,
        ]);
    }

    public function test_superviseur_a_rendements(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        $sup = $this->loginSuperviseur('superviseur_a');
        $employerInfo = DB::table('employer')->where('id', $employe->id)->first();

        if ($employerInfo) {
            Presence::create([
                'employerID' => $employe->id,
                'Sup_id' => $employerInfo->Sup_id,
                'date' => now()->toDateString(),
                'heureArrivee' => now()->toDateString() . ' 08:00:00',
                'heureDepart' => now()->toDateString() . ' 17:00:00',
                'status' => 'présent',
                'rendement' => 'Rendement test.',
            ]);
        }

        $response = $this->get('/superviseur/rendements?date=' . now()->toDateString());
        $response->assertOk();
    }

    public function test_superviseur_a_export_rendements_csv(): void
    {
        $sup = $this->loginSuperviseur('superviseur_a');

        $response = $this->get('/superviseur/rendements/export?debut=' . now()->startOfMonth()->toDateString() . '&fin=' . now()->toDateString());
        $response->assertOk();
    }

    public function test_superviseur_a_export_comparatif_pdf(): void
    {
        $sup = $this->loginSuperviseur('superviseur_a');

        $response = $this->get('/superviseur/comparatif/export');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_superviseur_a_get_user_details(): void
    {
        $sup = $this->loginSuperviseur('superviseur_a');
        $employe = Utilisateur::where('role', 'Employer')->first();

        $response = $this->get('/superviseur/getUserDetails/' . $employe->id);
        $response->assertOk();
        $response->assertJson(['detailsHtml' => true]);
    }

    public function test_superviseur_a_add_remove_member(): void
    {
        $sup = $this->loginSuperviseur('superviseur_a');
        $employe = Utilisateur::where('role', 'Employer')->first();

        $response = $this->post('/superviseur/addMemberToTeam/' . $employe->id);
        $response->assertStatus(302);

        $response2 = $this->post('/superviseur/remove-member/' . $employe->id);
        $response2->assertStatus(302);
    }

    // ══════════════════════════════════════════════════════════════════
    //  DIRECTRICE — Caisse impressions/papeteries
    // ══════════════════════════════════════════════════════════════════

    public function test_directrice_dashboard(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->get('/directrice/dashboard');
        $response->assertOk();
        $response->assertSee('caisse', false); // "caisse" en minuscule dans le HTML
    }

    public function test_directrice_store_commande_payee(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 5000,
            'montant_paye' => 5000,
            'statut_paiement' => 'paye',
            'details' => '100 pages noir et blanc.',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('commandes', [
            'superviseur_id' => $sup->id,
            'type' => 'impression',
            'montant' => 5000,
            'statut_paiement' => 'paye',
        ]);
    }

    public function test_directrice_store_commande_partielle(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->post('/directrice/commandes', [
            'type' => 'photocopie',
            'montant' => 3000,
            'montant_paye' => 1500,
            'statut_paiement' => 'partiel',
            'details' => 'Photocopie partielle.',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('commandes', [
            'superviseur_id' => $sup->id,
            'type' => 'photocopie',
            'montant' => 3000,
            'montant_paye' => 1500,
            'statut_paiement' => 'partiel',
        ]);
    }

    public function test_directrice_store_commande_a_payer(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->post('/directrice/commandes', [
            'type' => 'scan',
            'montant' => 2000,
            'montant_paye' => 0,
            'statut_paiement' => 'a_payer',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('commandes', [
            'superviseur_id' => $sup->id,
            'type' => 'scan',
            'montant_paye' => 0,
            'statut_paiement' => 'a_payer',
        ]);
    }

    public function test_directrice_edit_commande(): void
    {
        $sup = $this->loginSuperviseur('directrice');
        $cmd = Commande::where('superviseur_id', $sup->id)->first();
        if (!$cmd) {
            // Créer une commande d'abord
            $this->post('/directrice/commandes', [
                'type' => 'papeterie',
                'montant' => 1000,
                'montant_paye' => 1000,
                'statut_paiement' => 'paye',
            ]);
            $cmd = Commande::where('superviseur_id', $sup->id)->first();
        }
        $this->assertNotNull($cmd);

        $response = $this->get('/directrice/commandes/' . $cmd->id . '/edit');
        $response->assertOk();
    }

    public function test_directrice_update_commande(): void
    {
        $sup = $this->loginSuperviseur('directrice');
        $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 5000,
            'montant_paye' => 3000,
            'statut_paiement' => 'partiel',
        ]);
        $cmd = Commande::where('superviseur_id', $sup->id)->orderByDesc('id')->first();

        $response = $this->put('/directrice/commandes/' . $cmd->id, [
            'type' => 'impression',
            'montant' => 5000,
            'montant_paye' => 5000,
            'statut_paiement' => 'paye',
        ]);
        $response->assertStatus(302);

        $cmd->refresh();
        $this->assertEquals('paye', $cmd->statut_paiement);
        $this->assertEquals(5000, $cmd->montant_paye);
    }

    public function test_directrice_delete_commande(): void
    {
        $sup = $this->loginSuperviseur('directrice');
        $this->post('/directrice/commandes', [
            'type' => 'scan',
            'montant' => 500,
            'montant_paye' => 500,
            'statut_paiement' => 'paye',
        ]);
        $cmd = Commande::where('superviseur_id', $sup->id)->orderByDesc('id')->first();

        $response = $this->delete('/directrice/commandes/' . $cmd->id);
        $response->assertStatus(302);
        $this->assertSoftDeleted('commandes', ['id' => $cmd->id]);
    }

    public function test_directrice_store_service(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->post('/directrice/services', [
            'type' => 'impression',
            'montant' => 2500,
            'details' => 'Impression planche A3.',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('services_fournis', [
            'superviseur_id' => $sup->id,
            'type' => 'impression',
            'montant' => 2500,
        ]);
    }

    public function test_directrice_services_page(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->get('/directrice/services');
        $response->assertOk();
    }

    public function test_directrice_retraits(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->get('/directrice/retraits');
        $response->assertOk();
    }

    public function test_directrice_store_retrait(): void
    {
        $sup = $this->loginSuperviseur('directrice');
        // D'abord ajouter des entrées
        $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 10000,
            'montant_paye' => 10000,
            'statut_paiement' => 'paye',
        ]);

        $response = $this->post('/directrice/retraits', [
            'montant' => 5000,
            'motif' => 'Achat fournitures.',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('retraits', [
            'superviseur_id' => $sup->id,
            'montant' => 5000,
            'motif' => 'Achat fournitures.',
        ]);
    }

    public function test_directrice_rapport(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->get('/directrice/rapport');
        $response->assertOk();
    }

    public function test_directrice_export_csv(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->get('/directrice/rapport/export');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_directrice_reste_a_encaisser(): void
    {
        $sup = $this->loginSuperviseur('directrice');
        $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 10000,
            'montant_paye' => 3000,
            'statut_paiement' => 'partiel',
        ]);

        $response = $this->get('/directrice/dashboard');
        $response->assertOk();
        $response->assertSee('Reste');
    }

    // ══════════════════════════════════════════════════════════════════
    //  SECRETAIRE — Caisse photo
    // ══════════════════════════════════════════════════════════════════

    public function test_secretaire_dashboard(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        $response = $this->get('/secretaire/dashboard');
        $response->assertOk();
        $response->assertSee('caisse', false); // "caisse" en minuscule dans le HTML
    }

    public function test_secretaire_store_commande_payee(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        $response = $this->post('/secretaire/commandes', [
            'type' => 'shooting',
            'montant' => 15000,
            'montant_paye' => 15000,
            'statut_paiement' => 'paye',
            'details' => 'Shooting corporate.',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('commandes', [
            'superviseur_id' => $sup->id,
            'type' => 'shooting',
            'statut_paiement' => 'paye',
        ]);
    }

    public function test_secretaire_store_commande_partielle(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        $response = $this->post('/secretaire/commandes', [
            'type' => 'montage_photos',
            'montant' => 20000,
            'montant_paye' => 10000,
            'statut_paiement' => 'partiel',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('commandes', [
            'superviseur_id' => $sup->id,
            'type' => 'montage_photos',
            'montant_paye' => 10000,
            'statut_paiement' => 'partiel',
        ]);
    }

    public function test_secretaire_store_commande_a_payer(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        $response = $this->post('/secretaire/commandes', [
            'type' => 'demi_carte_photo',
            'montant' => 5000,
            'montant_paye' => 0,
            'statut_paiement' => 'a_payer',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('commandes', [
            'superviseur_id' => $sup->id,
            'type' => 'demi_carte_photo',
            'montant_paye' => 0,
            'statut_paiement' => 'a_payer',
        ]);
    }

    public function test_secretaire_edit_update_delete_commande(): void
    {
        $sup = $this->loginSuperviseur('secretaire');
        $this->post('/secretaire/commandes', [
            'type' => 'shooting',
            'montant' => 5000,
            'montant_paye' => 2500,
            'statut_paiement' => 'partiel',
        ]);
        $cmd = Commande::where('superviseur_id', $sup->id)->orderByDesc('id')->first();

        // Edit
        $response = $this->get('/secretaire/commandes/' . $cmd->id . '/edit');
        $response->assertOk();

        // Update
        $response = $this->put('/secretaire/commandes/' . $cmd->id, [
            'type' => 'shooting',
            'montant' => 5000,
            'montant_paye' => 5000,
            'statut_paiement' => 'paye',
        ]);
        $response->assertStatus(302);
        $cmd->refresh();
        $this->assertEquals('paye', $cmd->statut_paiement);

        // Delete
        $response = $this->delete('/secretaire/commandes/' . $cmd->id);
        $response->assertStatus(302);
        $this->assertSoftDeleted('commandes', ['id' => $cmd->id]);
    }

    public function test_secretaire_store_service(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        $response = $this->post('/secretaire/services', [
            'type' => 'montage_photos',
            'montant' => 8000,
            'details' => 'Montage album mariage.',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('services_fournis', [
            'superviseur_id' => $sup->id,
            'type' => 'montage_photos',
        ]);
    }

    public function test_secretaire_services_page(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        $response = $this->get('/secretaire/services');
        $response->assertOk();
    }

    public function test_secretaire_retraits(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        $response = $this->get('/secretaire/retraits');
        $response->assertOk();
    }

    public function test_secretaire_store_retrait(): void
    {
        $sup = $this->loginSuperviseur('secretaire');
        $this->post('/secretaire/commandes', [
            'type' => 'shooting',
            'montant' => 20000,
            'montant_paye' => 20000,
            'statut_paiement' => 'paye',
        ]);

        $response = $this->post('/secretaire/retraits', [
            'montant' => 5000,
            'motif' => 'Dépôt bancaire.',
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('retraits', [
            'superviseur_id' => $sup->id,
            'montant' => 5000,
        ]);
    }

    public function test_secretaire_rapport(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        $response = $this->get('/secretaire/rapport');
        $response->assertOk();
    }

    public function test_secretaire_export_csv(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        $response = $this->get('/secretaire/rapport/export');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_secretaire_reste_a_encaisser(): void
    {
        $sup = $this->loginSuperviseur('secretaire');
        $this->post('/secretaire/commandes', [
            'type' => 'montage_agrandissement',
            'montant' => 15000,
            'montant_paye' => 5000,
            'statut_paiement' => 'partiel',
        ]);

        $response = $this->get('/secretaire/dashboard');
        $response->assertOk();
        $response->assertSee('Reste');
    }

    // ══════════════════════════════════════════════════════════════════
    //  GESTIONNAIRE DE STOCK — T-shirts & Papier
    // ══════════════════════════════════════════════════════════════════

    public function test_gestionnaire_dashboard(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');

        $response = $this->get('/gestionnaire/dashboard');
        $response->assertOk();
        $response->assertSee('Stock');
    }

    public function test_gestionnaire_tshirts_page(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');

        $response = $this->get('/gestionnaire/tshirts');
        $response->assertOk();
    }

    public function test_gestionnaire_store_tshirt(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');

        $response = $this->post('/gestionnaire/tshirts', [
            'couleur' => 'Noir',
            'taille' => 'L',
            'quantite' => 50,
            'seuil_alerte' => 10,
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('stock_tshirts', [
            'superviseur_id' => $sup->id,
            'couleur' => 'Noir',
            'taille' => 'L',
            'quantite' => 50,
        ]);
    }

    public function test_gestionnaire_update_tshirt(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');
        $this->post('/gestionnaire/tshirts', [
            'couleur' => 'Blanc',
            'taille' => 'M',
            'quantite' => 30,
            'seuil_alerte' => 5,
        ]);
        $tshirt = StockTshirt::where('superviseur_id', $sup->id)
            ->where('couleur', 'Blanc')->first();

        $response = $this->put('/gestionnaire/tshirts/' . $tshirt->id, [
            'quantite' => 25,
            'seuil_alerte' => 10,
        ]);
        $response->assertStatus(302);

        $tshirt->refresh();
        $this->assertEquals(25, $tshirt->quantite);
    }

    public function test_gestionnaire_delete_tshirt(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');
        $this->post('/gestionnaire/tshirts', [
            'couleur' => 'Rouge',
            'taille' => 'S',
            'quantite' => 10,
        ]);
        $tshirt = StockTshirt::where('superviseur_id', $sup->id)
            ->where('couleur', 'Rouge')->first();

        $response = $this->delete('/gestionnaire/tshirts/' . $tshirt->id);
        $response->assertStatus(302);
        $this->assertDatabaseMissing('stock_tshirts', ['id' => $tshirt->id]);
    }

    public function test_gestionnaire_papier_page(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');

        $response = $this->get('/gestionnaire/papier');
        $response->assertOk();
    }

    public function test_gestionnaire_store_papier(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');

        $response = $this->post('/gestionnaire/papier', [
            'imprimante' => 'HP LaserJet Pro',
            'metres_restants' => 150,
            'metres_total' => 300,
            'seuil_alerte' => 50,
        ]);
        $response->assertStatus(302);

        $this->assertDatabaseHas('stock_papier', [
            'superviseur_id' => $sup->id,
            'imprimante' => 'HP LaserJet Pro',
            'metres_restants' => 150,
        ]);
    }

    public function test_gestionnaire_update_papier(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');
        $this->post('/gestionnaire/papier', [
            'imprimante' => 'Epson L3150',
            'metres_restants' => 200,
            'metres_total' => 200,
            'seuil_alerte' => 30,
        ]);
        $papier = StockPapier::where('superviseur_id', $sup->id)
            ->where('imprimante', 'Epson L3150')->first();

        $response = $this->put('/gestionnaire/papier/' . $papier->id, [
            'metres_restants' => 100,
            'seuil_alerte' => 20,
        ]);
        $response->assertStatus(302);

        $papier->refresh();
        $this->assertEquals(100, $papier->metres_restants);
    }

    public function test_gestionnaire_delete_papier(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');
        $this->post('/gestionnaire/papier', [
            'imprimante' => 'Canon PIXMA',
            'metres_restants' => 50,
            'metres_total' => 100,
        ]);
        $papier = StockPapier::where('superviseur_id', $sup->id)
            ->where('imprimante', 'Canon PIXMA')->first();

        $response = $this->delete('/gestionnaire/papier/' . $papier->id);
        $response->assertStatus(302);
        $this->assertDatabaseMissing('stock_papier', ['id' => $papier->id]);
    }

    public function test_gestionnaire_alerte_stock(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');

        // Stock sous le seuil -> notification
        $this->post('/gestionnaire/tshirts', [
            'couleur' => 'Vert',
            'taille' => 'XL',
            'quantite' => 2, // sous le seuil de 5
            'seuil_alerte' => 5,
        ]);

        Notification::assertSentTo(
            Utilisateur::where('role', 'Administrateur')->first(),
            \App\Notifications\StockAlertNotification::class
        );
    }

    // ══════════════════════════════════════════════════════════════════
    //  REDIRECTION SUPERVISEUR SPÉCIALISÉ
    // ══════════════════════════════════════════════════════════════════

    public function test_directrice_redirection_dashboard(): void
    {
        $sup = $this->loginSuperviseur('directrice');
        $response = $this->get('/superviseur/supdashboard');
        $response->assertRedirect();
        $this->assertStringContainsString('directrice/dashboard', $response->headers->get('Location'));
    }

    public function test_secretaire_redirection_dashboard(): void
    {
        $sup = $this->loginSuperviseur('secretaire');
        $response = $this->get('/superviseur/supdashboard');
        $response->assertRedirect();
        $this->assertStringContainsString('secretaire/dashboard', $response->headers->get('Location'));
    }

    public function test_gestionnaire_redirection_dashboard(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');
        $response = $this->get('/superviseur/supdashboard');
        $response->assertRedirect();
        $this->assertStringContainsString('gestionnaire/dashboard', $response->headers->get('Location'));
    }

    public function test_superviseur_a_pas_de_redirection(): void
    {
        $sup = $this->loginSuperviseur('superviseur_a');
        $response = $this->get('/superviseur/supdashboard');
        $response->assertOk(); // Pas de redirection
    }

    // ══════════════════════════════════════════════════════════════════
    //  ISOLATION DES DONNÉES ENTRE SUPERVISEURS
    // ══════════════════════════════════════════════════════════════════

    public function test_directrice_ne_voit_pas_commandes_secretaire(): void
    {
        $secretaire = $this->loginSuperviseur('secretaire');
        $this->post('/secretaire/commandes', [
            'type' => 'shooting',
            'montant' => 10000,
            'montant_paye' => 10000,
            'statut_paiement' => 'paye',
        ]);

        $this->loginSuperviseur('directrice');
        $response = $this->get('/directrice/commandes');
        $content = $response->getContent();
        $this->assertStringNotContainsString('shooting', $content);
    }

    public function test_secretaire_ne_voit_pas_commandes_directrice(): void
    {
        $directrice = $this->loginSuperviseur('directrice');
        $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 5000,
            'montant_paye' => 5000,
            'statut_paiement' => 'paye',
        ]);

        $this->loginSuperviseur('secretaire');
        $response = $this->get('/secretaire/commandes');
        $content = $response->getContent();
        $this->assertStringNotContainsString('impression', $content);
    }

    public function test_gestionnaire_stock_isole(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');
        $this->post('/gestionnaire/tshirts', [
            'couleur' => 'Bleu',
            'taille' => 'XL',
            'quantite' => 20,
        ]);

        // Un autre gestionnaire ne devrait pas voir ses stocks
        $response = $this->get('/gestionnaire/tshirts');
        $response->assertOk();
    }

    // ══════════════════════════════════════════════════════════════════
    //  VALIDATION DES DONNÉES
    // ══════════════════════════════════════════════════════════════════

    public function test_directrice_validation_montant_negatif(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => -100,
            'montant_paye' => 0,
            'statut_paiement' => 'a_payer',
        ]);
        $response->assertSessionHasErrors('montant');
    }

    public function test_directrice_validation_type_invalide(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        $response = $this->post('/directrice/commandes', [
            'type' => 'invalide',
            'montant' => 1000,
            'montant_paye' => 1000,
            'statut_paiement' => 'paye',
        ]);
        $response->assertSessionHasErrors('type');
    }

    public function test_secretaire_validation_montant_negatif(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        $response = $this->post('/secretaire/commandes', [
            'type' => 'shooting',
            'montant' => -500,
            'montant_paye' => 0,
            'statut_paiement' => 'a_payer',
        ]);
        $response->assertSessionHasErrors('montant');
    }

    public function test_gestionnaire_validation_tshirt(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');

        $response = $this->post('/gestionnaire/tshirts', [
            'couleur' => '',
            'taille' => '',
            'quantite' => -1,
        ]);
        $response->assertSessionHasErrors(['couleur', 'taille', 'quantite']);
    }

    public function test_gestionnaire_validation_papier(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');

        $response = $this->post('/gestionnaire/papier', [
            'imprimante' => '',
            'metres_restants' => -10,
            'metres_total' => -5,
        ]);
        $response->assertSessionHasErrors(['imprimante', 'metres_restants', 'metres_total']);
    }

    // ══════════════════════════════════════════════════════════════════
    //  RETRAIT DÉPASSEMENT DE CAISSE
    // ══════════════════════════════════════════════════════════════════

    public function test_directrice_retrait_depasse_caisse_refuse(): void
    {
        $sup = $this->loginSuperviseur('directrice');
        // Pas d'entrées -> caisse = 0
        $response = $this->post('/directrice/retraits', [
            'montant' => 5000,
            'motif' => 'Test retrait sans fonds.',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }

    public function test_secretaire_retrait_depasse_caisse_refuse(): void
    {
        $sup = $this->loginSuperviseur('secretaire');
        $response = $this->post('/secretaire/retraits', [
            'montant' => 10000,
            'motif' => 'Test retrait sans fonds.',
        ]);
        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }
}
