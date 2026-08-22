<?php

namespace Tests\Unit;

use App\Models\Commande;
use App\Models\ServiceFourni;
use App\Models\Retrait;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use App\Services\CaisseService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CaisseServiceTest extends TestCase
{
    use DatabaseTransactions;

    private int $superviseurId;
    private string $type;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un superviseur test
        $user = Utilisateur::create([
            'nom' => 'Test Directrice',
            'email' => 'test.caisse@lepharaon.com',
            'motDePasse' => bcrypt('password'),
            'role' => 'Superviseur',
        ]);

        Superviseur::create([
            'id' => $user->id,
            'equipe' => 'Test Caisse',
            'type_superviseur' => 'directrice',
        ]);

        $this->superviseurId = $user->id;
        $this->type = 'directrice';
    }

    public function test_solde_initial_est_nul(): void
    {
        $caisse = new CaisseService($this->superviseurId, $this->type);
        $solde = $caisse->solde();

        $this->assertEquals(0, $solde['en_caisse']);
        $this->assertEquals(0, $solde['impaye_total']);
        $this->assertEquals(0, $solde['total_commandes']);
        $this->assertEquals(0, $solde['total_services']);
        $this->assertEquals(0, $solde['total_retraits']);
    }

    public function test_solde_apres_commande_payee(): void
    {
        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'impression',
            'montant' => 10000,
            'montant_paye' => 10000,
            'statut_paiement' => 'paye',
            'date' => now()->toDateString(),
        ]);

        $caisse = new CaisseService($this->superviseurId, $this->type);
        $solde = $caisse->solde();

        $this->assertEquals(10000, $solde['en_caisse']);
        $this->assertEquals(0, $solde['impaye_total']);
        $this->assertEquals(10000, $solde['total_commandes']);
        $this->assertEquals(10000, $solde['total_commandes_paye']);
    }

    public function test_solde_apres_commande_partielle(): void
    {
        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'photocopie',
            'montant' => 15000,
            'montant_paye' => 5000,
            'statut_paiement' => 'partiel',
            'date' => now()->toDateString(),
        ]);

        $caisse = new CaisseService($this->superviseurId, $this->type);
        $solde = $caisse->solde();

        // Seul le montant payé entre en caisse
        $this->assertEquals(5000, $solde['en_caisse']);
        // Le reste est impayé (affiché en négatif)
        $this->assertEquals(10000, $solde['impaye_total']);
    }

    public function test_solde_apres_commande_a_payer(): void
    {
        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'scan',
            'montant' => 8000,
            'montant_paye' => 0,
            'statut_paiement' => 'a_payer',
            'date' => now()->toDateString(),
        ]);

        $caisse = new CaisseService($this->superviseurId, $this->type);
        $solde = $caisse->solde();

        $this->assertEquals(0, $solde['en_caisse']);
        $this->assertEquals(8000, $solde['impaye_total']);
    }

    public function test_solde_avec_service(): void
    {
        ServiceFourni::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'impression',
            'montant' => 5000,
            'date' => now()->toDateString(),
        ]);

        $caisse = new CaisseService($this->superviseurId, $this->type);
        $solde = $caisse->solde();

        $this->assertEquals(5000, $solde['en_caisse']);
        $this->assertEquals(5000, $solde['total_services']);
    }

    public function test_solde_avec_retrait(): void
    {
        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'impression',
            'montant' => 20000,
            'montant_paye' => 20000,
            'statut_paiement' => 'paye',
            'date' => now()->toDateString(),
        ]);

        Retrait::create([
            'superviseur_id' => $this->superviseurId,
            'montant' => 5000,
            'motif' => 'Test',
            'date' => now()->toDateString(),
        ]);

        $caisse = new CaisseService($this->superviseurId, $this->type);
        $solde = $caisse->solde();

        $this->assertEquals(15000, $solde['en_caisse']); // 20000 - 5000
        $this->assertEquals(5000, $solde['total_retraits']);
    }

    public function test_commandes_impayees(): void
    {
        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'impression',
            'montant' => 10000,
            'montant_paye' => 10000,
            'statut_paiement' => 'paye',
            'date' => now()->toDateString(),
        ]);

        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'photocopie',
            'montant' => 20000,
            'montant_paye' => 8000,
            'statut_paiement' => 'partiel',
            'date' => now()->toDateString(),
        ]);

        $caisse = new CaisseService($this->superviseurId, $this->type);
        $impayees = $caisse->commandesImpayees();

        $this->assertCount(1, $impayees);
        $this->assertEquals('partiel', $impayees->first()->statut_paiement);
    }

    public function test_reste_a_encaisser(): void
    {
        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'impression',
            'montant' => 15000,
            'montant_paye' => 5000,
            'statut_paiement' => 'partiel',
            'date' => now()->toDateString(),
        ]);

        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'scan',
            'montant' => 10000,
            'montant_paye' => 0,
            'statut_paiement' => 'a_payer',
            'date' => now()->toDateString(),
        ]);

        $caisse = new CaisseService($this->superviseurId, $this->type);

        $this->assertEquals(20000, $caisse->resteAEncaisser()); // (15000-5000) + (10000-0)
    }

    public function test_evolution_7_jours(): void
    {
        $caisse = new CaisseService($this->superviseurId, $this->type);
        $graphique = $caisse->evolution7jours();

        $this->assertCount(7, $graphique['labels']);
        $this->assertCount(7, $graphique['entrees']);
        $this->assertCount(7, $graphique['sorties']);
    }

    public function test_repartition_types(): void
    {
        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'impression',
            'montant' => 5000,
            'montant_paye' => 5000,
            'statut_paiement' => 'paye',
            'date' => now()->toDateString(),
        ]);

        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'impression',
            'montant' => 3000,
            'montant_paye' => 3000,
            'statut_paiement' => 'paye',
            'date' => now()->toDateString(),
        ]);

        ServiceFourni::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'papeterie',
            'montant' => 2000,
            'date' => now()->toDateString(),
        ]);

        $caisse = new CaisseService($this->superviseurId, $this->type);
        $repartition = $caisse->repartitionTypes();

        $this->assertEquals(8000, $repartition['commandesParType']['impression']);
        $this->assertEquals(2000, $repartition['servicesParType']['papeterie']);
    }

    public function test_dernieres_transactions(): void
    {
        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'impression',
            'montant' => 1000,
            'montant_paye' => 1000,
            'statut_paiement' => 'paye',
            'date' => now()->toDateString(),
        ]);

        $caisse = new CaisseService($this->superviseurId, $this->type);
        $transactions = $caisse->dernieresTransactions(5);

        $this->assertCount(1, $transactions['derniersCommandes']);
        $this->assertCount(0, $transactions['derniersServices']);
        $this->assertCount(0, $transactions['derniersRetraits']);
    }

    public function test_dashboard_data_contient_toutes_les_cles(): void
    {
        $caisse = new CaisseService($this->superviseurId, $this->type);
        $data = $caisse->dashboardData();

        $this->assertArrayHasKey('sommeEnCaisse', $data);
        $this->assertArrayHasKey('impayeTotal', $data);
        $this->assertArrayHasKey('commandesImpayees', $data);
        $this->assertArrayHasKey('resteAEncaisser', $data);
        $this->assertArrayHasKey('labels7j', $data);
        $this->assertArrayHasKey('dataEntrees7j', $data);
        $this->assertArrayHasKey('dataSorties7j', $data);
        $this->assertArrayHasKey('derniersCommandes', $data);
        $this->assertArrayHasKey('derniersServices', $data);
        $this->assertArrayHasKey('derniersRetraits', $data);
        $this->assertArrayHasKey('historiqueEncaissements', $data);
    }

    public function test_isole_entre_superviseurs(): void
    {
        // Créer un 2ème superviseur
        $user2 = Utilisateur::create([
            'nom' => 'Autre Directrice',
            'email' => 'autre.caisse@lepharaon.com',
            'motDePasse' => bcrypt('password'),
            'role' => 'Superviseur',
        ]);
        Superviseur::create(['id' => $user2->id, 'equipe' => 'Autre', 'type_superviseur' => 'directrice']);

        // Commande pour le superviseur 1
        Commande::create([
            'superviseur_id' => $this->superviseurId,
            'type' => 'impression',
            'montant' => 10000,
            'montant_paye' => 10000,
            'statut_paiement' => 'paye',
            'date' => now()->toDateString(),
        ]);

        // Le superviseur 2 ne voit rien
        $caisse2 = new CaisseService($user2->id, $this->type);
        $solde2 = $caisse2->solde();
        $this->assertEquals(0, $solde2['en_caisse']);

        // Le superviseur 1 voit sa commande
        $caisse1 = new CaisseService($this->superviseurId, $this->type);
        $solde1 = $caisse1->solde();
        $this->assertEquals(10000, $solde1['en_caisse']);
    }
}
