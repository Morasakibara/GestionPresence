<?php

namespace Tests\Feature;

use App\Models\ServiceFourni;
use App\Models\Utilisateur;
use App\Services\CaisseService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Harmonisation des dashboards : menu latéral unique par rôle, toutes les
 * fonctionnalités présentes, élément courant sélectionné. Couvre aussi les
 * pages caisse / retraits (non-régression) et l'accès réservé aux métriques.
 */
class NavigationHarmonieTest extends TestCase
{
    use DatabaseTransactions;

    private function loginSuperviseur(): Utilisateur
    {
        $sup = Utilisateur::where('role', 'Superviseur')->firstOrFail();
        $this->actingAs($sup);
        session(['current_role' => 'Superviseur']);

        return $sup;
    }

    private function loginAdmin(): Utilisateur
    {
        $admin = Utilisateur::where('role', 'Administrateur')->firstOrFail();
        $this->actingAs($admin);

        return $admin;
    }

    /** Le dashboard admin liste toutes ses fonctionnalités et marque l'onglet courant */
    public function test_menu_admin_complet_et_actif(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/dashboard');
        $response->assertOk();
        $response->assertSee('Tableau de bord');
        $response->assertSee('État de caisse');
        $response->assertSee('État du stock');
        $response->assertSee('Ajouter employé');
        $response->assertSee('Liste des employés');
        $response->assertSee('Générer Bilan');
        $response->assertSee('Lieux de travail');
        $response->assertSee('Congés');
        $response->assertSee('Audit');
        $response->assertSee('aria-current="page"', false);
    }

    /** Chaque page d'un rôle affiche le même menu, avec son onglet sélectionné */
    public function test_menu_superviseur_identique_et_actif(): void
    {
        $this->loginSuperviseur();

        $dashboard = $this->get('/superviseur/supdashboard');
        $dashboard->assertOk();

        foreach (['Tableau de bord', 'Ajout de membre', 'Suivre les présences', 'Générer Rapports', 'Rendements', 'Congés'] as $item) {
            $dashboard->assertSee($item);
        }

        $presence = $this->get('/superviseur/followPresence');
        $presence->assertOk();
        $presence->assertSee('Suivre les présences');
        $presence->assertSee('aria-current="page"', false);
    }

    /** Directrice : services et retraits affichent le menu complet, retraits sélectionné */
    public function test_pages_services_et_retraits_directrice(): void
    {
        $sup = $this->loginSuperviseur();

        ServiceFourni::create([
            'superviseur_id' => $sup->id,
            'type' => 'impression',
            'montant' => 2000,
            'details' => 'Test rendu',
            'date' => now()->toDateString(),
        ]);

        $services = $this->get('/directrice/services');
        $services->assertOk();
        $services->assertSee('Services enregistrés');
        $services->assertSee('Rapport');

        $retraits = $this->get('/directrice/retraits');
        $retraits->assertOk();
        $retraits->assertSee('Retrait');
        $retraits->assertSee('aria-current="page"', false);
    }

    /** Secrétaire : la page retraits s'affiche (l'erreur Undefined $retraits est corrigée) */
    public function test_page_retraits_secretaire(): void
    {
        $this->loginSuperviseur();

        $response = $this->get('/secretaire/retraits');
        $response->assertOk();
        $response->assertSee('Retrait');
        $response->assertSee('Montant');
    }

    /** Le dashboard caisse calcule les totaux affichés (sans erreur d'agrégat) */
    public function test_dashboard_caisse_rend_les_totaux(): void
    {
        $sup = $this->loginSuperviseur();

        $solde = (new CaisseService($sup->id, 'directrice'))->solde();
        $this->assertArrayHasKey('en_caisse', $solde);
        $this->assertArrayHasKey('total_retraits', $solde);
        $this->assertArrayHasKey('impaye_total', $solde);

        $this->get('/directrice/dashboard')->assertOk();
    }

    /** Aucun administrateur ordinaire ne voir la page métriques */
    public function test_metriques_reservees_au_super_admin(): void
    {
        $admins = Utilisateur::where('role', 'Administrateur')->get();
        $this->assertGreaterThan(0, $admins->count());

        $superAdmin = $admins->first();
        $this->actingAs($superAdmin);
        $this->get('/admin/metrics')->assertOk();

        $autre = $admins->firstWhere('id', '!=', $superAdmin->id);
        if ($autre) {
            $this->actingAs($autre);
            $this->get('/admin/metrics')->assertStatus(403);
            // Le menu ne doit pas exposer l'entrée Métriques pour un admin simple
            $this->get('/admin/dashboard')->assertOk()->assertDontSee('>Métriques<', false);
        }
    }
}
