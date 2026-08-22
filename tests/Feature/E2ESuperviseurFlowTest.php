<?php

namespace Tests\Feature;

use App\Models\Commande;
use App\Models\ServiceFourni;
use App\Models\Retrait;
use App\Models\StockTshirt;
use App\Models\StockPapier;
use App\Models\Utilisateur;
use App\Models\Presence;
use App\Models\Evaluation;
use App\Models\WorkplaceLocation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Tests E2E bout-en-bout : chaque test simule un parcours utilisateur complet.
 * Pas de Laravel Dusk (Windows file locking) — tests HTTP complets avec follow redirects.
 */
class E2ESuperviseurFlowTest extends TestCase
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
                'nom' => 'Siège Le Pharaon (test E2E)',
                'latitude' => self::PARIS_LAT,
                'longitude' => self::PARIS_LON,
                'rayon' => 5000,
                'actif' => true,
            ]);
        }
    }

    private function loginSuperviseur(string $type): Utilisateur
    {
        $supId = DB::table('superviseur')
            ->where('type_superviseur', $type)
            ->value('id');
        $this->assertNotNull($supId, "Superviseur '$type' absent.");
        $sup = Utilisateur::find($supId);
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
    //  E2E — SUPERVISEUR A : Parcours complet
    // ══════════════════════════════════════════════════════════════════

    public function test_e2e_superviseur_a_parcours_complet(): void
    {
        $sup = $this->loginSuperviseur('superviseur_a');

        // 1. Dashboard — vérifier stats
        $r = $this->get('/superviseur/supdashboard');
        $r->assertOk();
        $r->assertSee('Tableau de bord');
        $r->assertSee('stat-card');

        // 2. Suivi des présences
        $r = $this->get('/superviseur/followPresence');
        $r->assertOk();

        // 3. Détails d'un employé
        $employe = Utilisateur::where('role', 'Employer')->first();
        $r = $this->get('/superviseur/getUserDetails/' . $employe->id);
        $r->assertOk();
        $r->assertJsonStructure(['detailsHtml', 'viewMoreUrl']);

        // 4. Fiche de détails
        $r = $this->get('/superviseur/viewUser/' . $employe->id);
        $r->assertOk();
        $r->assertSee($employe->nom);

        // 5. Générer un rapport d'équipe
        $r = $this->get('/superviseur/generateReport2');
        $r->assertOk();
        $r->assertSee("Rapport d'équipe");

        // 6. Enregistrer une évaluation
        $r = $this->post('/superviseur/evaluations', [
            'employerID' => $employe->id,
            'mois' => now()->format('Y-m'),
            'note' => 16,
            'couleur' => 'vert',
            'commentaire' => 'E2E: excellent travail.',
        ]);
        $r->assertStatus(302);
        $this->assertDatabaseHas('evaluations', [
            'employerID' => $employe->id,
            'mois' => now()->format('Y-m'),
            'note' => 16,
        ]);

        // 7. Rendements d'équipe
        $r = $this->get('/superviseur/rendements?date=' . now()->toDateString());
        $r->assertOk();

        // 8. Export CSV rendements
        $r = $this->get('/superviseur/rendements/export?debut=' . now()->startOfMonth()->toDateString() . '&fin=' . now()->toDateString());
        $r->assertOk();

        // 9. Export PDF comparatif
        $r = $this->get('/superviseur/comparatif/export');
        $r->assertOk();
        $r->assertHeader('Content-Type', 'application/pdf');

        // 10. Export CSV évolution évaluations
        $r = $this->get('/superviseur/evaluations/evolution/export');
        $r->assertOk();
        $r->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // ══════════════════════════════════════════════════════════════════
    //  E2E — DIRECTRICE : Caisse complète
    // ══════════════════════════════════════════════════════════════════

    public function test_e2e_directrice_parcours_complet(): void
    {
        $sup = $this->loginSuperviseur('directrice');

        // 1. Dashboard
        $r = $this->get('/directrice/dashboard');
        $r->assertOk();
        $r->assertSee('caisse', false);

        // 2. Page commandes
        $r = $this->get('/directrice/commandes');
        $r->assertOk();

        // 3. Créer commande payée
        $r = $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 15000,
            'montant_paye' => 15000,
            'statut_paiement' => 'paye',
            'details' => 'Impression 200 pages A4.',
        ]);
        $r->assertStatus(302);
        $cmdId = Commande::where('superviseur_id', $sup->id)->orderByDesc('id')->first()->id;

        // 4. Créer commande partielle
        $r = $this->post('/directrice/commandes', [
            'type' => 'photocopie',
            'montant' => 8000,
            'montant_paye' => 3000,
            'statut_paiement' => 'partiel',
        ]);
        $r->assertStatus(302);

        // 5. Créer commande à payer
        $r = $this->post('/directrice/commandes', [
            'type' => 'scan',
            'montant' => 2000,
            'montant_paye' => 0,
            'statut_paiement' => 'a_payer',
        ]);
        $r->assertStatus(302);

        // 6. Vérifier la caisse sur le dashboard
        $r = $this->get('/directrice/dashboard');
        $r->assertOk();
        $r->assertSee('Reste');

        // 7. Modifier la commande partielle (payer le reste)
        $cmdPartielle = Commande::where('superviseur_id', $sup->id)
            ->where('statut_paiement', 'partiel')->first();
        if ($cmdPartielle) {
            $r = $this->put('/directrice/commandes/' . $cmdPartielle->id, [
                'type' => 'photocopie',
                'montant' => 8000,
                'montant_paye' => 8000,
                'statut_paiement' => 'paye',
            ]);
            $r->assertStatus(302);
        }

        // 8. Éditer une commande
        $r = $this->get('/directrice/commandes/' . $cmdId . '/edit');
        $r->assertOk();

        // 9. Services
        $r = $this->get('/directrice/services');
        $r->assertOk();
        $r = $this->post('/directrice/services', [
            'type' => 'papeterie',
            'montant' => 3500,
            'details' => 'Papeterie divers.',
        ]);
        $r->assertStatus(302);

        // 10. Retraits
        $r = $this->get('/directrice/retraits');
        $r->assertOk();
        $r = $this->post('/directrice/retraits', [
            'montant' => 2000,
            'motif' => 'Dépôt bancaire E2E.',
        ]);
        $r->assertStatus(302);
        $this->assertDatabaseHas('retraits', [
            'superviseur_id' => $sup->id,
            'motif' => 'Dépôt bancaire E2E.',
        ]);

        // 11. Retrait refusé si dépassement
        $r = $this->post('/directrice/retraits', [
            'montant' => 999999,
            'motif' => 'Retrait trop élevé.',
        ]);
        $r->assertSessionHas('error');

        // 12. Rapport
        $r = $this->get('/directrice/rapport');
        $r->assertOk();
        $r = $this->get('/directrice/rapport/export');
        $r->assertOk();
        $r->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // ══════════════════════════════════════════════════════════════════
    //  E2E — SECRETAIRE : Caisse photo complète
    // ══════════════════════════════════════════════════════════════════

    public function test_e2e_secretaire_parcours_complet(): void
    {
        $sup = $this->loginSuperviseur('secretaire');

        // 1. Dashboard
        $r = $this->get('/secretaire/dashboard');
        $r->assertOk();

        // 2. Commandes — les 3 statuts de paiement
        $r = $this->post('/secretaire/commandes', [
            'type' => 'shooting',
            'montant' => 25000,
            'montant_paye' => 25000,
            'statut_paiement' => 'paye',
            'details' => 'Shooting mariage E2E.',
        ]);
        $r->assertStatus(302);

        $r = $this->post('/secretaire/commandes', [
            'type' => 'montage_photos',
            'montant' => 15000,
            'montant_paye' => 5000,
            'statut_paiement' => 'partiel',
        ]);
        $r->assertStatus(302);

        $r = $this->post('/secretaire/commandes', [
            'type' => 'demi_carte_photo',
            'montant' => 3000,
            'montant_paye' => 0,
            'statut_paiement' => 'a_payer',
        ]);
        $r->assertStatus(302);

        // 3. Vérifier dashboard avec reste à encaisser
        $r = $this->get('/secretaire/dashboard');
        $r->assertOk();
        $r->assertSee('Reste');

        // 4. Modifier une commande
        $cmdPartielle = Commande::where('superviseur_id', $sup->id)
            ->where('statut_paiement', 'partiel')->first();
        $r = $this->put('/secretaire/commandes/' . $cmdPartielle->id, [
            'type' => 'montage_photos',
            'montant' => 15000,
            'montant_paye' => 15000,
            'statut_paiement' => 'paye',
        ]);
        $r->assertStatus(302);

        // 5. Services
        $r = $this->post('/secretaire/services', [
            'type' => 'agrandissement_photos',
            'montant' => 7000,
            'details' => 'Agrandissement 50x70.',
        ]);
        $r->assertStatus(302);

        // 6. Retraits
        $r = $this->post('/secretaire/retraits', [
            'montant' => 3000,
            'motif' => 'Achat cartes mémoire.',
        ]);
        $r->assertStatus(302);

        // 7. Rapport + export
        $r = $this->get('/secretaire/rapport');
        $r->assertOk();
        $r = $this->get('/secretaire/rapport/export');
        $r->assertOk();

        // 8. Suppression commande
        $cmdToDelete = Commande::where('superviseur_id', $sup->id)
            ->where('statut_paiement', 'a_payer')->first();
        if ($cmdToDelete) {
            $r = $this->delete('/secretaire/commandes/' . $cmdToDelete->id);
            $r->assertStatus(302);
            $this->assertSoftDeleted('commandes', ['id' => $cmdToDelete->id]);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  E2E — GESTIONNAIRE STOCK : Gestion complète
    // ══════════════════════════════════════════════════════════════════

    public function test_e2e_gestionnaire_stock_parcours_complet(): void
    {
        $sup = $this->loginSuperviseur('gestionnaire_stock');

        // 1. Dashboard
        $r = $this->get('/gestionnaire/dashboard');
        $r->assertOk();

        // 2. T-shirts — CRUD complet
        $r = $this->post('/gestionnaire/tshirts', [
            'couleur' => 'Noir',
            'taille' => 'M',
            'quantite' => 50,
            'seuil_alerte' => 10,
        ]);
        $r->assertStatus(302);
        $tshirt = StockTshirt::where('superviseur_id', $sup->id)
            ->where('couleur', 'Noir')->first();

        // Modifier
        $r = $this->put('/gestionnaire/tshirts/' . $tshirt->id, [
            'quantite' => 45,
        ]);
        $r->assertStatus(302);
        $tshirt->refresh();
        $this->assertEquals(45, $tshirt->quantite);

        // Page T-shirts
        $r = $this->get('/gestionnaire/tshirts');
        $r->assertOk();
        $r->assertSee('Noir');

        // Supprimer
        $r = $this->delete('/gestionnaire/tshirts/' . $tshirt->id);
        $r->assertStatus(302);
        $this->assertDatabaseMissing('stock_tshirts', ['id' => $tshirt->id]);

        // 3. Alertes stock — quantité sous le seuil
        $r = $this->post('/gestionnaire/tshirts', [
            'couleur' => 'Blanc',
            'taille' => 'L',
            'quantite' => 2,
            'seuil_alerte' => 10,
        ]);
        $r->assertStatus(302);
        Notification::assertSentTo(
            Utilisateur::where('role', 'Administrateur')->first(),
            \App\Notifications\StockAlertNotification::class
        );

        // 4. Papier — CRUD complet
        $r = $this->post('/gestionnaire/papier', [
            'imprimante' => 'HP LaserJet Pro M404',
            'metres_restants' => 200,
            'metres_total' => 300,
            'seuil_alerte' => 50,
        ]);
        $r->assertStatus(302);
        $papier = StockPapier::where('superviseur_id', $sup->id)->first();

        // Modifier
        $r = $this->put('/gestionnaire/papier/' . $papier->id, [
            'metres_restants' => 150,
        ]);
        $r->assertStatus(302);
        $papier->refresh();
        $this->assertEquals(150, $papier->metres_restants);

        // Page papier
        $r = $this->get('/gestionnaire/papier');
        $r->assertOk();

        // Supprimer
        $r = $this->delete('/gestionnaire/papier/' . $papier->id);
        $r->assertStatus(302);
        $this->assertDatabaseMissing('stock_papier', ['id' => $papier->id]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  E2E — CROSS-MODULE : Isolation données
    // ══════════════════════════════════════════════════════════════════

    public function test_e2e_isolation_donnees_entre_superviseurs(): void
    {
        // La directrice crée une commande
        $directrice = $this->loginSuperviseur('directrice');
        $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 10000,
            'montant_paye' => 10000,
            'statut_paiement' => 'paye',
        ]);
        $cmdDirectrice = Commande::where('superviseur_id', $directrice->id)->orderByDesc('id')->first();

        // La secrétaire crée une commande
        $secretaire = $this->loginSuperviseur('secretaire');
        $this->post('/secretaire/commandes', [
            'type' => 'shooting',
            'montant' => 20000,
            'montant_paye' => 20000,
            'statut_paiement' => 'paye',
        ]);
        $cmdSecretaire = Commande::where('superviseur_id', $secretaire->id)->orderByDesc('id')->first();

        // La directrice ne voit PAS la commande de la secrétaire
        $this->loginSuperviseur('directrice');
        $r = $this->get('/directrice/commandes');
        $this->assertStringNotContainsString('shooting', $r->getContent());

        // La secrétaire ne voit PAS la commande de la directrice
        $this->loginSuperviseur('secretaire');
        $r = $this->get('/secretaire/commandes');
        $this->assertStringNotContainsString('impression', $r->getContent());

        // La directrice ne peut PAS modifier la commande de la secrétaire
        $this->loginSuperviseur('directrice');
        $r = $this->put('/directrice/commandes/' . $cmdSecretaire->id, [
            'type' => 'impression',
            'montant' => 1,
            'montant_paye' => 1,
            'statut_paiement' => 'paye',
        ]);
        $r->assertStatus(404);
    }

    // ══════════════════════════════════════════════════════════════════
    //  E2E — REDIRECTIONS
    // ══════════════════════════════════════════════════════════════════

    public function test_e2e_redirections_par_type(): void
    {
        $tests = [
            'directrice' => '/directrice/dashboard',
            'secretaire' => '/secretaire/dashboard',
            'gestionnaire_stock' => '/gestionnaire/dashboard',
        ];

        foreach ($tests as $type => $expectedUrl) {
            $this->loginSuperviseur($type);
            $r = $this->get('/superviseur/supdashboard');
            $r->assertRedirect();
            $this->assertStringContainsString($expectedUrl, $r->headers->get('Location'));
        }

        // Superviseur A ne redirige pas
        $this->loginSuperviseur('superviseur_a');
        $r = $this->get('/superviseur/supdashboard');
        $r->assertOk();
    }

    // ══════════════════════════════════════════════════════════════════
    //  E2E — EMPLOYÉ : Pointage complet
    // ══════════════════════════════════════════════════════════════════

    public function test_e2e_employe_pointage_complet(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        \Illuminate\Support\Facades\Auth::login($employe);
        session(['current_role' => 'Employer']);

        // 1. Voir la page de présence
        $r = $this->get('/presence');
        $r->assertOk();
        $r->assertSee('Marquer');

        // 2. Marquer arrivée
        $r = $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ]);
        $r->assertStatus(302);
        $this->assertDatabaseHas('presence', [
            'employerID' => $employe->id,
            'status' => 'en attente',
        ]);

        // 3. Tenter départ sans rendement
        $r = $this->post('/mark-departure', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ]);
        $r->assertSessionHasErrors('rendement');

        // 4. Départ avec rendement
        $r = $this->post('/mark-departure', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'rendement' => 'E2E: Développement API + tests.',
        ]);
        $r->assertStatus(302);

        $presence = Presence::where('employerID', $employe->id)
            ->whereDate('date', now()->toDateString())
            ->where('rendement', 'E2E: Développement API + tests.')
            ->first();
        $this->assertNotNull($presence);
        $this->assertEquals('présent', $presence->status);
        $this->assertEquals('E2E: Développement API + tests.', $presence->rendement);

        // 5. Consulter les fiches de rendement
        $r = $this->get('/user/rendement');
        $r->assertOk();
        $r->assertSee('E2E: Développement API + tests.');

        // 6. Dashboard employé
        $r = $this->get('/user/dashboard');
        $r->assertOk();
        $r->assertSee('Mon évaluation du mois');
    }
}
