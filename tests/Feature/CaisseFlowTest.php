<?php

namespace Tests\Feature;

use App\Models\Commande;
use App\Models\Retrait;
use App\Models\ServiceFourni;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CaisseFlowTest extends TestCase
{
    use DatabaseTransactions;

    private Utilisateur $directrice;
    private Utilisateur $secretaire;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directrice = Utilisateur::create([
            'nom' => 'Directrice Test',
            'email' => 'directrice@test.com',
            'motDePasse' => bcrypt('password'),
            'role' => 'Superviseur',
        ]);
        Superviseur::create([
            'id' => $this->directrice->id,
            'equipe' => 'Impression',
            'type_superviseur' => 'directrice',
        ]);

        $this->secretaire = Utilisateur::create([
            'nom' => 'Secrétaire Test',
            'email' => 'secretaire@test.com',
            'motDePasse' => bcrypt('password'),
            'role' => 'Superviseur',
        ]);
        Superviseur::create([
            'id' => $this->secretaire->id,
            'equipe' => 'Photo',
            'type_superviseur' => 'secretaire',
        ]);
    }

    // ── DIRECTRICE ──

    public function test_directrice_dashboard_affiche_caisse(): void
    {
        $this->actingAs($this->directrice);
        session(['current_role' => 'Superviseur']);
        $this->get('/directrice/dashboard')->assertOk();
    }

    public function test_directrice_enregistrer_commande(): void
    {
        $this->actingAs($this->directrice);
        session(['current_role' => 'Superviseur']);
        $this->post('/directrice/commandes', [
                 'type' => 'impression',
                 'montant' => 5000,
                 'montant_paye' => 5000,
                 'statut_paiement' => 'paye',
                 'description' => 'Impression A4',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('commandes', [
            'superviseur_id' => $this->directrice->id,
            'type' => 'impression',
            'montant' => 5000,
            'montant_paye' => 5000,
            'statut_paiement' => 'paye',
        ]);
    }

    public function test_directrice_enregistrer_service(): void
    {
        $this->actingAs($this->directrice);
        session(['current_role' => 'Superviseur']);
        $this->post('/directrice/services', [
                 'type' => 'photocopie',
                 'montant' => 3000,
                 'description' => '50 photocopies',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('services_fournis', [
            'superviseur_id' => $this->directrice->id,
            'type' => 'photocopie',
            'montant' => 3000,
        ]);
    }

    public function test_directrice_retrait(): void
    {
        // D'abord créer une commande payée
        Commande::create([
            'superviseur_id' => $this->directrice->id,
            'type' => 'impression',
            'montant' => 20000,
            'montant_paye' => 20000,
            'statut_paiement' => 'paye',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($this->directrice);
        session(['current_role' => 'Superviseur']);$this->post('/directrice/retraits', [
                 'montant' => 5000,
                 'motif' => 'Achat papier',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('retraits', [
            'superviseur_id' => $this->directrice->id,
            'montant' => 5000,
            'motif' => 'Achat papier',
        ]);
    }

    public function test_directrice_retrait_depasse_caisse_refuse(): void
    {
        $this->actingAs($this->directrice);
        session(['current_role' => 'Superviseur']);$this->post('/directrice/retraits', [
                 'montant' => 50000,
                 'motif' => 'Retrait trop grand',
             ])
             ->assertSessionHas('error');
    }

    public function test_commande_payee_partiellement_caisse_reflete(): void
    {
        $this->actingAs($this->directrice);
        session(['current_role' => 'Superviseur']);$this->post('/directrice/commandes', [
                 'type' => 'impression',
                 'montant' => 10000,
                 'montant_paye' => 3000,
                 'statut_paiement' => 'partiel',
             ])
             ->assertRedirect();

        // Seul le montant payé (3000) doit être en caisse
        $commande = Commande::where('superviseur_id', $this->directrice->id)->first();
        $this->assertEquals(3000, $commande->montant_paye);
        $this->assertEquals('partiel', $commande->statut_paiement);
    }

    // ── SECRÉTAIRE ──

    public function test_secretaire_dashboard_affiche_caisse(): void
    {
        $this->actingAs($this->secretaire);
        session(['current_role' => 'Superviseur']);
        $this->get('/secretaire/dashboard')->assertOk();
    }

    public function test_secretaire_enregistrer_commande_shooting(): void
    {
        $this->actingAs($this->secretaire);
        session(['current_role' => 'Superviseur']);
        $this->post('/secretaire/commandes', [
                 'type' => 'shooting',
                 'montant' => 25000,
                 'montant_paye' => 25000,
                 'statut_paiement' => 'paye',
                 'description' => 'Shooting complet',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('commandes', [
            'superviseur_id' => $this->secretaire->id,
            'type' => 'shooting',
            'montant' => 25000,
        ]);
    }

    public function test_secretaire_retrait_depasse_caisse_refuse(): void
    {
        $this->actingAs($this->secretaire);
        session(['current_role' => 'Superviseur']);$this->post('/secretaire/retraits', [
                 'montant' => 99999,
                 'motif' => 'Test',
             ])
             ->assertSessionHas('error');
    }

    // ── ISOLATION ──

    public function test_isolation_directrice_secretaire(): void
    {
        Commande::create([
            'superviseur_id' => $this->directrice->id,
            'type' => 'impression',
            'montant' => 15000,
            'montant_paye' => 15000,
            'statut_paiement' => 'paye',
            'date' => now()->toDateString(),
        ]);

        // La directrice voit sa commande
        $this->actingAs($this->directrice);
        session(['current_role' => 'Superviseur']);
        $this->get('/directrice/commandes')->assertOk();

        // La secrétaire ne voit pas les commandes de la directrice
        $this->actingAs($this->secretaire);
        session(['current_role' => 'Superviseur']);
        $response = $this->get('/secretaire/commandes');
        $response->assertOk();
        $this->assertStringNotContainsString((string) $this->directrice->id, $response->content());
    }
}

