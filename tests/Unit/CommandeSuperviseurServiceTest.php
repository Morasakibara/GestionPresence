<?php

namespace Tests\Unit;

use App\Models\Commande;
use App\Models\ServiceFourni;
use App\Models\Retrait;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use App\Services\CommandeSuperviseurService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Testing\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommandeSuperviseurServiceTest extends TestCase
{
    use DatabaseTransactions;

    private int $superviseurId;
    private array $typesDirectrice = [
        'impression'     => 'Impressions',
        'photocopie'     => 'Photocopies',
        'papeterie'      => 'Papeteries',
        'scan'           => 'Scan',
        'plastification' => 'Plastification',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        // Créer un superviseur directrice
        $user = Utilisateur::create([
            'nom'        => 'Directrice Test',
            'email'      => uniqid('dir_') . '@test.com',
            'motDePasse' => bcrypt('password'),
            'role'       => 'Superviseur',
        ]);

        Superviseur::create([
            'id'               => $user->id,
            'equipe'           => 'Directrice',
            'type_superviseur' => 'directrice',
        ]);

        $this->superviseurId = $user->id;
    }

    private function service(): CommandeSuperviseurService
    {
        return new CommandeSuperviseurService(
            $this->superviseurId,
            'directrice',
            $this->typesDirectrice
        );
    }

    private function makeRequest(array $data = []): \Illuminate\Http\Request
    {
        return \Illuminate\Http\Request::create('/test', 'POST', $data);
    }

    // ─── toutesLesCommandes ─────────────────────────────────────────

    public function test_toutes_les_commandes_retourne_collection(): void
    {
        Commande::create([
            'superviseur_id'  => $this->superviseurId,
            'type'            => 'impression',
            'montant'         => 5000,
            'montant_paye'    => 5000,
            'statut_paiement' => 'paye',
            'date'            => now()->toDateString(),
        ]);

        $result = $this->service()->toutesLesCommandes();
        $this->assertCount(1, $result);
        $this->assertEquals('impression', $result->first()->type);
    }

    public function test_toutes_les_commandes_isole_par_superviseur(): void
    {
        // Créer un 2ème superviseur pour tester l'isolation
        $autreSup = Utilisateur::create([
            'nom'        => 'Autre Superviseur',
            'email'      => uniqid('sup2_') . '@test.com',
            'motDePasse' => bcrypt('password'),
            'role'       => 'Superviseur',
        ]);
        Superviseur::create(['id' => $autreSup->id, 'equipe' => 'Beta', 'type_superviseur' => null]);

        Commande::create([
            'superviseur_id'  => $autreSup->id,
            'type'            => 'scan',
            'montant'         => 3000,
            'montant_paye'    => 3000,
            'statut_paiement' => 'paye',
            'date'            => now()->toDateString(),
        ]);

        $result = $this->service()->toutesLesCommandes();
        $this->assertCount(0, $result);
    }

    // ─── enregistrerCommande ────────────────────────────────────────

    public function test_enregistrer_commande_payee(): void
    {
        $request = $this->makeRequest([
            'type'            => 'impression',
            'montant'         => 15000,
            'montant_paye'    => 15000,
            'statut_paiement' => 'paye',
            'details'         => 'Test impression.',
        ]);

        $cmd = $this->service()->enregistrerCommande($request);

        $this->assertNotNull($cmd);
        $this->assertEquals('impression', $cmd->type);
        $this->assertEquals(15000, $cmd->montant);
        $this->assertEquals(15000, $cmd->montant_paye);
        $this->assertEquals('paye', $cmd->statut_paiement);
        $this->assertEquals($this->superviseurId, $cmd->superviseur_id);
    }

    public function test_enregistrer_commande_partielle(): void
    {
        $request = $this->makeRequest([
            'type'            => 'photocopie',
            'montant'         => 10000,
            'montant_paye'    => 4000,
            'statut_paiement' => 'partiel',
        ]);

        $cmd = $this->service()->enregistrerCommande($request);

        $this->assertEquals('partiel', $cmd->statut_paiement);
        $this->assertEquals(4000, $cmd->montant_paye);
    }

    public function test_enregistrer_commande_a_payer(): void
    {
        $request = $this->makeRequest([
            'type'            => 'scan',
            'montant'         => 2000,
            'montant_paye'    => 0,
            'statut_paiement' => 'a_payer',
        ]);

        $cmd = $this->service()->enregistrerCommande($request);

        $this->assertEquals('a_payer', $cmd->statut_paiement);
        $this->assertEquals(0, $cmd->montant_paye);
    }

    public function test_enregistrer_commande_force_montant_paye_si_paye(): void
    {
        // Si statut = paye mais montant_paye < montant → corriger
        $request = $this->makeRequest([
            'type'            => 'papeterie',
            'montant'         => 8000,
            'montant_paye'    => 5000,
            'statut_paiement' => 'paye',
        ]);

        $cmd = $this->service()->enregistrerCommande($request);

        $this->assertEquals(8000, $cmd->montant_paye); // Corrigé au montant total
    }

    // ─── encaisserReste ─────────────────────────────────────────────

    public function test_encaisser_reste_partiellement(): void
    {
        $cmd = Commande::create([
            'superviseur_id'  => $this->superviseurId,
            'type'            => 'impression',
            'montant'         => 10000,
            'montant_paye'    => 3000,
            'statut_paiement' => 'partiel',
            'date'            => now()->toDateString(),
        ]);

        $request = $this->makeRequest([
            'commande_id'  => $cmd->id,
            'montant_verse' => 4000,
        ]);

        $result = $this->service()->encaisserReste($request);

        $this->assertTrue($result['success']);
        $cmd->refresh();
        $this->assertEquals(7000, $cmd->montant_paye);
        $this->assertEquals('partiel', $cmd->statut_paiement);
    }

    public function test_encaisser_reste_total(): void
    {
        $cmd = Commande::create([
            'superviseur_id'  => $this->superviseurId,
            'type'            => 'scan',
            'montant'         => 5000,
            'montant_paye'    => 2000,
            'statut_paiement' => 'partiel',
            'date'            => now()->toDateString(),
        ]);

        $request = $this->makeRequest([
            'commande_id'  => $cmd->id,
            'montant_verse' => 3000,
        ]);

        $result = $this->service()->encaisserReste($request);

        $this->assertTrue($result['success']);
        $cmd->refresh();
        $this->assertEquals(5000, $cmd->montant_paye);
        $this->assertEquals('paye', $cmd->statut_paiement);
    }

    public function test_encaisser_reste_depassement_refuse(): void
    {
        $cmd = Commande::create([
            'superviseur_id'  => $this->superviseurId,
            'type'            => 'photocopie',
            'montant'         => 5000,
            'montant_paye'    => 3000,
            'statut_paiement' => 'partiel',
            'date'            => now()->toDateString(),
        ]);

        $request = $this->makeRequest([
            'commande_id'  => $cmd->id,
            'montant_verse' => 5000, // reste = 2000, on envoie 5000
        ]);

        $result = $this->service()->encaisserReste($request);

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_encaisser_reste_commande_inexistante(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $request = $this->makeRequest([
            'commande_id'  => 99999,
            'montant_verse' => 1000,
        ]);

        $this->service()->encaisserReste($request);
    }

    // ─── modifierCommande ───────────────────────────────────────────

    public function test_modifier_commande_montant_paye(): void
    {
        $cmd = Commande::create([
            'superviseur_id'  => $this->superviseurId,
            'type'            => 'impression',
            'montant'         => 10000,
            'montant_paye'    => 0,
            'statut_paiement' => 'a_payer',
            'date'            => now()->toDateString(),
        ]);

        $request = $this->makeRequest([
            'montant_paye'    => 5000,
            'statut_paiement' => 'partiel',
            'details'         => 'Virement reçu.',
        ]);

        $updated = $this->service()->modifierCommande($request, $cmd->id);

        $this->assertEquals(5000, $updated->montant_paye);
        $this->assertEquals('partiel', $updated->statut_paiement);
        $this->assertEquals('Virement reçu.', $updated->details);
    }

    public function test_modifier_commande_ne_diminue_pas_montant_paye(): void
    {
        $cmd = Commande::create([
            'superviseur_id'  => $this->superviseurId,
            'type'            => 'scan',
            'montant'         => 8000,
            'montant_paye'    => 6000,
            'statut_paiement' => 'partiel',
            'date'            => now()->toDateString(),
        ]);

        $request = $this->makeRequest([
            'montant_paye'    => 2000, // inférieur à 6000 existant
            'statut_paiement' => 'a_payer',
        ]);

        $updated = $this->service()->modifierCommande($request, $cmd->id);

        // Le montant ne doit pas diminuer
        $this->assertEquals(6000, $updated->montant_paye);
    }

    // ─── supprimerCommande ──────────────────────────────────────────

    public function test_supprimer_commande(): void
    {
        $cmd = Commande::create([
            'superviseur_id'  => $this->superviseurId,
            'type'            => 'plastification',
            'montant'         => 1000,
            'montant_paye'    => 1000,
            'statut_paiement' => 'paye',
            'date'            => now()->toDateString(),
        ]);

        $cmdId = $cmd->id;
        $this->service()->supprimerCommande($cmdId);

        // Vérifier que la méthode ne lève pas d'exception
        $this->assertTrue(true);
    }

    // ─── dashboardData ──────────────────────────────────────────────

    public function test_dashboard_data_retourne_cles_requises(): void
    {
        $data = $this->service()->dashboardData();

        $this->assertArrayHasKey('en_caisse', $data);
        $this->assertArrayHasKey('impaye_total', $data);
        $this->assertArrayHasKey('commandesParType', $data);
        $this->assertArrayHasKey('typesCaisse', $data);
    }

    // ─── rapportData ────────────────────────────────────────────────

    public function test_rapport_data_retourne_collections(): void
    {
        // Créer des données de test
        Commande::create([
            'superviseur_id'  => $this->superviseurId,
            'type'            => 'impression',
            'montant'         => 5000,
            'montant_paye'    => 5000,
            'statut_paiement' => 'paye',
            'date'            => now()->toDateString(),
        ]);

        ServiceFourni::create([
            'superviseur_id' => $this->superviseurId,
            'type'           => 'impression',
            'montant'        => 3000,
            'date'           => now()->toDateString(),
        ]);

        $debut = now()->startOfMonth()->toDateString();
        $fin   = now()->toDateString();

        $data = $this->service()->rapportData($debut, $fin);

        $this->assertArrayHasKey('commandes', $data);
        $this->assertArrayHasKey('services', $data);
        $this->assertArrayHasKey('retraits', $data);
        $this->assertArrayHasKey('totalCommandes', $data);
        $this->assertArrayHasKey('totalServices', $data);
        $this->assertArrayHasKey('totalRetraits', $data);
        $this->assertArrayHasKey('detailJournalier', $data);
        $this->assertGreaterThan(0, $data['totalCommandes']);
        $this->assertGreaterThan(0, $data['totalServices']);
    }

    public function test_rapport_data_periode_vide(): void
    {
        $debut = '2020-01-01';
        $fin   = '2020-01-31';

        $data = $this->service()->rapportData($debut, $fin);

        $this->assertEquals(0, $data['totalCommandes']);
        $this->assertEquals(0, $data['totalServices']);
        $this->assertCount(0, $data['commandes']);
    }
}
