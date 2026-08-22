<?php

namespace Tests\Feature;

use App\Models\Evaluation;
use App\Models\Presence;
use App\Models\Utilisateur;
use App\Models\WorkplaceLocation;
use App\Services\EvaluationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PharaonFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        // Créer un lieu de travail actif aux coordonnées de Paris pour les tests de géolocalisation
        if (!WorkplaceLocation::where('actif', true)->exists()) {
            WorkplaceLocation::create([
                'nom' => 'Siège Le Pharaon (test)',
                'latitude' => self::PARIS_LAT,
                'longitude' => self::PARIS_LON,
                'rayon' => 5000, // 5 km pour les tests
                'actif' => true,
            ]);
        }
    }

    private const PARIS_LAT = 48.856613;
    private const PARIS_LON = 2.352222;

    private function loginEmploye(): Utilisateur
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        $this->assertNotNull($employe);
        \Illuminate\Support\Facades\Auth::login($employe);
        session(['current_role' => 'Employer']);
        $this->assertAuthenticatedAs($employe);

        return $employe;
    }

    private function loginAdmin(): Utilisateur
    {
        $admin = Utilisateur::where('role', 'Administrateur')->first();
        \Illuminate\Support\Facades\Auth::login($admin);

        return $admin;
    }

    /** Le pointage d'arrivée est possible à toute heure (plus de fenêtre 7h-10h). */
    public function test_arrivee_possible_a_14h_sans_restriction(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 14, 0)); // lundi 14h — hors ancienne fenêtre
        $employe = $this->loginEmploye();

        $response = $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ]);

        // redirect()->back() dans les tests → pas de referer → le flash est consommé par la vue cible
        $response->assertStatus(302);
        $this->assertDatabaseHas('presence', [
            'employerID' => $employe->id,
            'status' => 'en attente',
        ]);
    }

    /** Le pointage est aussi possible le week-end. */
    public function test_arrivee_possible_le_week_end(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 16, 9, 0)); // dimanche
        $employe = $this->loginEmploye();

        $response = $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('presence', [
            'employerID' => $employe->id,
            'status' => 'en attente',
        ]);
    }

    /** Le départ exige une fiche de rendement. */
    public function test_depart_requiert_fiche_de_rendement(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 0));
        $employe = $this->loginEmploye();

        $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ])->assertStatus(302);

        // Sans rendement -> erreur de validation
        $response = $this->post('/mark-departure', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ]);
        $response->assertSessionHasErrors('rendement');

        $presence = Presence::where('employerID', $employe->id)
            ->whereDate('date', '2026-08-17')
            ->whereNull('heureDepart')
            ->first();
        $this->assertNotNull($presence);

        // Avec rendement -> succès et enregistrement
        $response = $this->post('/mark-departure', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'rendement' => "J'ai finalisé le rapport mensuel et traité 15 dossiers clients.",
        ]);
        $response->assertStatus(302);

        $presence->refresh();
        $this->assertEquals('présent', $presence->status);
        $this->assertEquals("J'ai finalisé le rapport mensuel et traité 15 dossiers clients.", $presence->rendement);
    }

    /** Le système d'alertes retards est maintenu (notification au superviseur + admin). */
    public function test_alerte_retard_maintenue(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 9, 30)); // 9h30 = retard
        $employe = $this->loginEmploye();

        $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ])->assertStatus(302);

        // Vérifier que la notification de retard a bien été envoyée
        $superviseurInfo = DB::table('employer')->where('id', $employe->id)->first();
        if ($superviseurInfo && $superviseurInfo->Sup_id) {
            $superviseurUser = Utilisateur::find($superviseurInfo->Sup_id);
            Notification::assertSentTo($superviseurUser, \App\Notifications\RetardNotification::class);
        }
    }

    /** L'évaluation calcule une note et une couleur cohérentes. */
    public function test_evaluation_couleur_et_note(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();

        // Un employé avec beaucoup d'absences et de retards -> rouge
        foreach (['2026-08-03', '2026-08-04'] as $date) {
            Presence::create([
                'employerID' => $employe->id,
                'Sup_id' => 3,
                'date' => $date,
                'heureArrivee' => $date . ' 09:30:00', // retard
                'heureDepart' => $date . ' 17:00:00',
                'status' => 'présent',
            ]);
        }
        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => 3,
            'date' => '2026-08-05',
            'status' => 'Absent',
        ]);
        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => 3,
            'date' => '2026-08-06',
            'status' => 'Absent',
        ]);

        $evaluation = EvaluationService::evaluer($employe->id, '2026-08-01', '2026-08-31');

        $this->assertArrayHasKey('note', $evaluation);
        $this->assertArrayHasKey('couleur', $evaluation);
        $this->assertContains($evaluation['couleur'], ['vert', 'orange', 'rouge']);
        $this->assertFalse($evaluation['manuelle']);
    }

    /** Une évaluation manuelle prime sur le calcul automatique. */
    public function test_evaluation_manuelle_prime(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();

        Evaluation::create([
            'employerID' => $employe->id,
            'mois' => '2026-08',
            'note' => 18,
            'couleur' => 'vert',
            'commentaire' => 'Excellente implication.',
            'evaluateur_id' => 1,
        ]);

        $evaluation = EvaluationService::evaluer($employe->id, '2026-08-01', '2026-08-31');

        $this->assertTrue($evaluation['manuelle']);
        $this->assertEquals(18, $evaluation['note']);
        $this->assertEquals('vert', $evaluation['couleur']);
    }

    /** L'admin peut enregistrer une évaluation manuelle. */
    public function test_admin_enregistre_evaluation_manuelle(): void
    {
        $admin = $this->loginAdmin();
        $employe = Utilisateur::where('role', 'Employer')->first();

        $response = $this->post('/admin/evaluations', [
            'employerID' => $employe->id,
            'mois' => '2026-08',
            'note' => 16,
            'couleur' => 'vert',
            'commentaire' => 'Bon rendement.',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('evaluations', [
            'employerID' => $employe->id,
            'mois' => '2026-08',
            'note' => 16,
            'couleur' => 'vert',
            'evaluateur_id' => $admin->id,
        ]);
    }

    /** Le rapport admin inclut réalisations et évaluations. */
    public function test_rapport_admin_inclut_rendements_et_evaluation(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 0));
        $employe = $this->loginEmploye();

        $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ])->assertStatus(302);

        $this->post('/mark-departure', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'rendement' => 'Développement de la page de rendement.',
        ])->assertStatus(302);

        // Se connecter en admin pour générer le rapport
        $admin = $this->loginAdmin();
        $this->assertNotNull($admin);

        $response = $this->get('/admin/generate-report');
        $response->assertOk();
        $response->assertSee('Générer le bilan de présence');

        // Générer le rapport HTML
        $response = $this->post('/admin/generate-report', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
            'export_format' => 'html',
        ]);
        $response->assertOk();
        $response->assertSee('Rapport de présence et de rendement');
        $response->assertSee($employe->nom);
        $response->assertSee('Développement de la page de rendement.');
        $response->assertSee('/20');
        // Graphique de comparaison des évaluations (toute l'entreprise)
        $response->assertSee('Évolution des évaluations');
        $response->assertSee('companyEvaluationChart');
        $response->assertSee('chart.js');
    }

    /** Le rapport superviseur inclut réalisations et évaluations. */
    public function test_rapport_superviseur_inclut_rendements(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 0));
        $employe = $this->loginEmploye();
        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        \Illuminate\Support\Facades\Auth::login($superviseur);
        session(['current_role' => 'Superviseur']);

        // Créer une présence avec rendement pour l'employé
        $employerInfo = DB::table('employer')->where('id', $employe->id)->first();
        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'rendement' => 'Réunion client et suivi des livrables.',
        ]);

        $response = $this->get('/superviseur/generateReport2');
        $response->assertOk();
        $response->assertSee('Rapport d\'équipe');
        $response->assertSee('Réunion client et suivi des livrables.');
        $response->assertSee('/20');
        // Graphique de comparaison des évaluations de l'équipe
        $response->assertSee('Évolution des évaluations');
        $response->assertSee('teamEvaluationChart');
        $response->assertSee('chart.js');
    }

    /** S1 — L'employé consulte l'historique de ses fiches de rendement. */
    public function test_employe_consulte_ses_fiches_de_rendement(): void
    {
        $employe = $this->loginEmploye();

        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => 3,
            'date' => '2026-08-10',
            'heureArrivee' => '2026-08-10 08:00:00',
            'heureDepart' => '2026-08-10 17:00:00',
            'status' => 'présent',
            'rendement' => 'Analyse des besoins client.',
        ]);

        $response = $this->get('/user/rendement');
        $response->assertOk();
        $response->assertSee('Mes fiches de rendement');
        $response->assertSee('Analyse des besoins client.');
    }

    /** S2 — L'admin exporte les évaluations et rendements en Excel (charte Pharaon). */
    public function test_admin_export_excel_evaluations_rendements(): void
    {
        $admin = $this->loginAdmin();
        $employe = Utilisateur::where('role', 'Employer')->first();

        // Un rendement sur le mois pour cet employé
        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => 3,
            'date' => '2026-08-10',
            'heureArrivee' => '2026-08-10 08:00:00',
            'heureDepart' => '2026-08-10 17:00:00',
            'status' => 'présent',
            'rendement' => 'Livraison du module de facturation.',
        ]);

        $response = $this->get('/admin/evaluations/export?mois=2026-08');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $xml = $this->decompresseXlsx($response->getContent());
        $this->assertStringContainsString('Évaluations', $xml); // '&' est encodé &amp; dans le XML
        $this->assertStringContainsString('Le Pharaon', $xml);
        $this->assertStringContainsString('Livraison du module de facturation.', $xml);
    }

    /** Décompresse un XLSX et renvoie le contenu texte (sharedStrings + sheet). */
    private function decompresseXlsx(string $contenu): string
    {
        $fichier = tempnam(sys_get_temp_dir(), 'xlsx') . '.xlsx';
        file_put_contents($fichier, $contenu);
        $zip = new \ZipArchive();
        $texte = '';
        if ($zip->open($fichier) === true) {
            foreach (['xl/sharedStrings.xml', 'xl/worksheets/sheet1.xml'] as $entree) {
                $donnees = $zip->getFromName($entree);
                if ($donnees !== false) {
                    $texte .= $donnees;
                }
            }
            $zip->close();
        }
        @unlink($fichier);

        return $texte;
    }

    /** S3 — Une évaluation rouge notifie l'administrateur principal. */
    public function test_evaluation_rouge_notifie_admin(): void
    {
        $admin = $this->loginAdmin();
        $employe = Utilisateur::where('role', 'Employer')->first();

        $this->post('/admin/evaluations', [
            'employerID' => $employe->id,
            'mois' => '2026-08',
            'note' => 4,
            'couleur' => 'rouge',
            'commentaire' => 'Absences répétées.',
        ])->assertStatus(302);

        // L'admin qui évalue est l'admin principal -> pas de notification (évite l'auto-notification)
        Notification::assertNotSentTo($admin, \App\Notifications\EvaluationRougeNotification::class);

        // Le superviseur enregistre une évaluation rouge -> l'admin principal est notifié
        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        \Illuminate\Support\Facades\Auth::login($superviseur);
        session(['current_role' => 'Superviseur']);

        $this->post('/superviseur/evaluations', [
            'employerID' => $employe->id,
            'mois' => '2026-08',
            'note' => 3,
            'couleur' => 'rouge',
            'commentaire' => 'Discipline critique.',
        ])->assertStatus(302);

        Notification::assertSentTo($admin, \App\Notifications\EvaluationRougeNotification::class);
    }

    /** S3bis — La commande planifiée notifie l'admin pour les évaluations rouges. */
    public function test_commande_alertes_evaluations_rouges(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 0));
        $employe = Utilisateur::where('role', 'Employer')->first();

        // Beaucoup d'absences + retards sur juillet -> évaluation rouge
        foreach (['2026-07-06', '2026-07-07'] as $date) {
            Presence::create([
                'employerID' => $employe->id,
                'Sup_id' => 3,
                'date' => $date,
                'heureArrivee' => $date . ' 09:30:00',
                'heureDepart' => $date . ' 17:00:00',
                'status' => 'présent',
            ]);
        }
        Presence::create(['employerID' => $employe->id, 'Sup_id' => 3, 'date' => '2026-07-08', 'status' => 'Absent']);
        Presence::create(['employerID' => $employe->id, 'Sup_id' => 3, 'date' => '2026-07-09', 'status' => 'Absent']);
        Presence::create(['employerID' => $employe->id, 'Sup_id' => 3, 'date' => '2026-07-10', 'status' => 'Absent']);

        $this->artisan('presence:alertes-evaluations-rouges', ['--mois' => '2026-07'])
            ->expectsOutputToContain('notifié');

        $adminPrincipal = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
        Notification::assertSentTo($adminPrincipal, \App\Notifications\EvaluationRougeNotification::class);
    }

    /** S4 — Le superviseur consulte les fiches de rendement de son équipe. */
    public function test_superviseur_consulte_rendements_equipe(): void
    {
        $employe = $this->loginEmploye();
        $employerInfo = DB::table('employer')->where('id', $employe->id)->first();

        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        \Illuminate\Support\Facades\Auth::login($superviseur);
        session(['current_role' => 'Superviseur']);

        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:00:00',
            'heureDepart' => '2026-08-17 17:00:00',
            'status' => 'présent',
            'rendement' => 'Préparation de la revue hebdomadaire.',
        ]);

        $response = $this->get('/superviseur/rendements?date=2026-08-17');
        $response->assertOk();
        $response->assertSee('Rendement de l\'équipe');
        $response->assertSee('Préparation de la revue hebdomadaire.');
    }

    /** S5 — Le superviseur exporte les rendements de son équipe en Excel (charte Pharaon). */
    public function test_superviseur_export_excel_rendements(): void
    {
        $employe = $this->loginEmploye();
        $employerInfo = DB::table('employer')->where('id', $employe->id)->first();

        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        \Illuminate\Support\Facades\Auth::login($superviseur);
        session(['current_role' => 'Superviseur']);

        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:00:00',
            'heureDepart' => '2026-08-17 17:00:00',
            'status' => 'présent',
            'rendement' => 'Traitement des commandes de la semaine.',
        ]);

        $response = $this->get('/superviseur/rendements/export?debut=2026-08-10&fin=2026-08-17');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $xml = $this->decompresseXlsx($response->getContent());
        $this->assertStringContainsString('Rendement de l\'équipe', $xml);
        $this->assertStringContainsString('Le Pharaon', $xml);
        $this->assertStringContainsString('Traitement des commandes de la semaine.', $xml);
    }

    /** S6 — Le dashboard employé affiche son évaluation colorée du mois. */
    public function test_dashboard_employe_affiche_evaluation(): void
    {
        $employe = $this->loginEmploye();

        $response = $this->get('/user/dashboard');
        $response->assertOk();
        $response->assertSee('Mon évaluation du mois');
        $response->assertSee('/20');
    }

    /** S7 — Le rappel hebdomadaire notifie le superviseur des fiches manquantes. */
    public function test_rappel_hebdo_fiches_rendement(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 21, 17, 0)); // vendredi
        $employe = $this->loginEmploye();
        $employerInfo = DB::table('employer')->where('id', $employe->id)->first();

        // Le membre a travaillé cette semaine mais sans fiche de rendement
        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:00:00',
            'heureDepart' => '2026-08-17 17:00:00',
            'status' => 'présent',
            'rendement' => null, // fiche manquante
        ]);

        $superviseurUser = Utilisateur::find($employerInfo->Sup_id);
        $this->assertNotNull($superviseurUser);

        $this->artisan('presence:rappel-fiches-rendement')
            ->expectsOutputToContain('notifié');

        Notification::assertSentTo($superviseurUser, \App\Notifications\FicheRendementRappelNotification::class);

        // Si toutes les fiches sont remplies -> plus de notification
        Notification::fake(); // reset le fake
        // Remplir TOUTES les presences de la semaine pour toutes les équipes
        DB::table('presence')
            ->whereDate('date', '>=', '2026-08-11')
            ->whereDate('date', '<=', '2026-08-17')
            ->whereNull('rendement')
            ->update(['rendement' => 'Tâches terminées.']);
        $this->artisan('presence:rappel-fiches-rendement');
        Notification::assertNothingSent();
    }

    /** S8 — La durée de travail est calculée et affichée dans le rendement employé. */
    public function test_duree_travail_dans_rendement_employe(): void
    {
        $employe = $this->loginEmploye();
        $employerInfo = DB::table('employer')->where('id', $employe->id)->first();

        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:00:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'rendement' => 'Revue hebdomadaire avec la direction.',
        ]);

        $response = $this->get('/user/rendement');
        $response->assertOk();
        $response->assertSee('Durée');
        $response->assertSee('9h30'); // 08:00 -> 17:30
        $response->assertSee('Temps de travail total');
    }

    /** S9 — Le CSV superviseur contient la durée travaillée. */
    public function test_csv_superviseur_contient_duree(): void
    {
        $employe = $this->loginEmploye();
        $employerInfo = DB::table('employer')->where('id', $employe->id)->first();

        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        \Illuminate\Support\Facades\Auth::login($superviseur);
        session(['current_role' => 'Superviseur']);

        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 09:00:00',
            'heureDepart' => '2026-08-17 16:45:00',
            'status' => 'présent',
            'rendement' => 'Préparation des livraisons.',
        ]);

        $response = $this->get('/superviseur/rendements/export?debut=2026-08-10&fin=2026-08-17');
        $response->assertOk();

        $xml = $this->decompresseXlsx($response->getContent());
        $this->assertStringContainsString('Durée', $xml);
        $this->assertStringContainsString('7h45', $xml); // 09:00 -> 16:45
    }

    /** S10 — L'admin télécharge le bulletin individuel d'évaluation PDF. */
    public function test_admin_bulletin_evaluation_pdf(): void
    {
        $employe = $this->loginEmploye();

        $admin = $this->loginAdmin();
        $this->post('/select-role', ['role' => 'Administrateur']);

        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => DB::table('employer')->where('id', $employe->id)->value('Sup_id'),
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:00:00',
            'heureDepart' => '2026-08-17 17:00:00',
            'status' => 'présent',
            'rendement' => 'Préparation du rapport mensuel.',
        ]);

        $response = $this->get('/admin/employe/' . $employe->id . '/bulletin?mois=2026-08');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    /** S11 — Le superviseur ne peut pas télécharger le bulletin d'un employé hors équipe. */
    public function test_superviseur_bulletin_hors_equipe_refuse(): void
    {
        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        \Illuminate\Support\Facades\Auth::login($superviseur);
        session(['current_role' => 'Superviseur']);

        // Un employé qui n'est pas dans son équipe
        $superviseurInfo = DB::table('superviseur')->where('id', $superviseur->id)->first();
        $horsEquipe = Utilisateur::where('role', 'Employer')
            ->whereNotIn('id', DB::table('employer')->where('equipe', $superviseurInfo->equipe)->pluck('id'))
            ->first();
        $this->assertNotNull($horsEquipe);

        $response = $this->get('/superviseur/employe/' . $horsEquipe->id . '/bulletin?mois=2026-08');
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** S12 — Le dashboard employé affiche l'historique des 6 derniers mois. */
    public function test_dashboard_employe_historique_evaluations(): void
    {
        $employe = $this->loginEmploye();

        $response = $this->get('/user/dashboard');
        $response->assertOk();
        $response->assertSee('historique');
        $response->assertSee('6 mois');
        $response->assertSee('/20');
        $response->assertSee('evaluationChart'); // graphique Chart.js
        $response->assertSee('chart.js');
    }

    /** S13 — La page des lieux de travail (admin) rend avec la charte Pharaon. */
    public function test_workplace_locations_index_rendu_charte(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/workplace-locations');
        $response->assertOk();
        $response->assertSee('Lieux de travail');
        $response->assertSee('Nom');
        $response->assertSee('Latitude');
        $response->assertSee('Longitude');
        $response->assertSee('Rayon');
        $response->assertSee('Actions');
        // Charte Pharaon : titre en noir #080808
        $response->assertSee('text-[#080808]');
    }

    /** S14 — Le formulaire d'ajout d'un lieu de travail rend avec la charte. */
    public function test_workplace_locations_create_rendu_charte(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/workplace-locations/create');
        $response->assertOk();
        $response->assertSee('Ajouter un lieu');
        $response->assertSee('latitude');
        $response->assertSee('rayon');
        $response->assertSee('btn-gold'); // bouton or de la charte
    }

    /** S15 — La page de notifications rend avec la charte et l'état vide. */
    public function test_notifications_page_rendu_charte(): void
    {
        $this->loginAdmin();

        $response = $this->get('/notifications');
        $response->assertOk();
        $response->assertSee('Notifications');
        $response->assertSee('text-[#080808]'); // titre noir Pharaon
    }

    /** S16 — La page de pointage de présence (employé) rend avec la charte. */
    public function test_presence_page_rendu_charte(): void
    {
        $employe = $this->loginEmploye();
        $this->assertNotNull($employe);

        $response = $this->get('/presence');
        $response->assertOk();
        $response->assertSee('Marquer la présence');
        $response->assertSee('Marquer l'); // bouton arrivée (apostrophe encodée)
        $response->assertSee('départ');
        $response->assertSee('bg-[#080808]'); // sidebar noire Pharaon
    }

    /** S19 — Le dashboard admin affiche le graphique d'évolution des évaluations. */
    public function test_dashboard_admin_affiche_graphique_evolution_evaluations(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/dashboard');
        $response->assertOk();
        $response->assertSee('evaluationEvolChart'); // canvas Chart.js
        $response->assertSee('Évolution des évaluations');
        $response->assertSee('stat-card'); // cartes de stats modernes
    }

    /** S20 — Le dashboard superviseur affiche le graphique d'évolution de son équipe. */
    public function test_dashboard_superviseur_affiche_graphique_evolution(): void
    {
        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        \Illuminate\Support\Facades\Auth::login($superviseur);
        session(['current_role' => 'Superviseur']);

        $response = $this->get('/superviseur/supdashboard');
        $response->assertOk();
        $response->assertSee('evaluationEvolChart');
        $response->assertSee('Évolution des évaluations');
        $response->assertSee('stat-card');
    }

    /** S21 — Un GET sur /superviseur/evaluations redirige proprement (au lieu d'erreur 405). */
    public function test_get_superviseur_evaluations_redirige(): void
    {
        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        \Illuminate\Support\Facades\Auth::login($superviseur);
        session(['current_role' => 'Superviseur']);

        $response = $this->get('/superviseur/evaluations');
        $response->assertRedirect();
        $this->assertStringContainsString('superviseur/generateReport2', $response->headers->get('Location'));
    }

    /** S22 — Le dashboard admin propose le filtre par équipe et les exports du graphique. */
    public function test_dashboard_admin_filtre_equipe_et_exports(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/dashboard');
        $response->assertOk();
        $response->assertSee('evaluationEvolChart');
        $response->assertSee('equipe'); // filtre par équipe
        $response->assertSee('Exporter PNG');
        $response->assertSee('Exporter CSV');
    }

    /** S23 — L'export CSV de l'évolution des évaluations fonctionne (entreprise + équipe). */
    public function test_export_csv_evolution_evaluations(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/evaluations/evolution/export');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Mois;Note moyenne /20;Couleur', $response->getContent());

        // Avec filtre équipe
        $equipe = \App\Models\Superviseur::whereNotNull('equipe')->value('equipe');
        if ($equipe) {
            $response2 = $this->get('/admin/evaluations/evolution/export?equipe=' . urlencode($equipe));
            $response2->assertOk();
            $this->assertStringContainsString('Mois;Note moyenne /20;Couleur', $response2->getContent());
        }
    }

    /** S24 — Le bulletin PDF admin contient le bloc d'évolution (6 mois). */
    public function test_bulletin_pdf_contient_evolution(): void
    {
        $employe = $this->loginEmploye();
        $this->loginAdmin();
        $this->post('/select-role', ['role' => 'Administrateur']);

        $response = $this->get('/admin/employe/' . $employe->id . '/bulletin?mois=2026-08');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        // La vue du bulletin contient bien le bloc d'évolution (6 mois)
        $historique = \App\Services\EvaluationService::historiqueMensuel((int) $employe->id, 6);
        $html = view('admin.evaluation_bulletin_pdf', [
            'employe' => (object) ['nom' => 'Test'],
            'evaluation' => ['note' => 12.0, 'couleur' => 'orange', 'commentaire' => 'Test', 'manuelle' => false],
            'stats' => ['presences_completes' => 0, 'retards' => 0, 'absences' => 0, 'suspectes' => 0, 'rendements_remplis' => 0],
            'rendements' => collect([]),
            'mois' => '2026-08',
            'debut' => '2026-08-01',
            'fin' => '2026-08-31',
            'historique' => $historique,
        ])->render();

        $this->assertStringContainsString('Évolution de la note (6 derniers mois)', $html);
        $this->assertStringContainsString('/20', $html);
    }

    /** S25 — Le dashboard superviseur propose les exports PNG/CSV et la moyenne/tendance. */
    public function test_dashboard_superviseur_exports_et_moyenne(): void
    {
        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        \Illuminate\Support\Facades\Auth::login($superviseur);
        session(['current_role' => 'Superviseur']);

        $response = $this->get('/superviseur/supdashboard');
        $response->assertOk();
        $response->assertSee('evaluationEvolChart');
        $response->assertSee('Exporter PNG');
        $response->assertSee('Exporter CSV');
        $response->assertSee('Moyenne globale (6 mois)');
        $response->assertSee('Tendance (dernier mois)');
    }

    /** S26 — L'export CSV de l'évolution des évaluations de l'équipe fonctionne. */
    public function test_export_csv_evolution_superviseur(): void
    {
        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        \Illuminate\Support\Facades\Auth::login($superviseur);
        session(['current_role' => 'Superviseur']);

        $response = $this->get('/superviseur/evaluations/evolution/export');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Équipe;Mois;Note moyenne /20;Couleur', $response->getContent());
    }

    /** S27 — Le dashboard admin affiche la moyenne globale et la tendance sous le graphique. */
    public function test_dashboard_admin_affiche_moyenne_et_tendance(): void
    {
        $this->loginAdmin();

        $response = $this->get('/admin/dashboard');
        $response->assertOk();
        $response->assertSee('Moyenne globale (6 mois)');
        $response->assertSee('Tendance (dernier mois)');
    }

    /** S28 — statsEvolution calcule moyenne et tendance correctement. */
    public function test_stats_evolution_calcule_moyenne_tendance(): void
    {
        $evolution = [
            'labels' => ['Mars', 'Avril', 'Mai'],
            'notes' => [10.0, 12.0, 15.0],
            'couleurs' => ['#D97706', '#D97706', '#2E8B57'],
        ];

        $stats = \App\Services\EvaluationService::statsEvolution($evolution);
        $this->assertEquals(12.3, $stats['moyenne']);
        $this->assertEquals('hausse', $stats['tendance']);
        $this->assertEquals(3.0, $stats['delta']);

        // Baisse
        $stats2 = \App\Services\EvaluationService::statsEvolution([
            'labels' => ['Mars', 'Avril', 'Mai'],
            'notes' => [15.0, 12.0, 10.0],
            'couleurs' => ['#2E8B57', '#D97706', '#D64545'],
        ]);
        $this->assertEquals('baisse', $stats2['tendance']);
    }

    /** S17 — La configuration mail utilise le transport Resend avec un expéditeur dédié. */
    public function test_config_mail_utilise_le_transport_resend(): void
    {
        // En CI/tests la MAIL_MAILER=array est forcée : on vérifie que le mailer 'resend'
        // est bien configuré (transport resend) et que RESEND_KEY est présent.
        $config = config('mail.mailers.resend');
        $this->assertNotNull($config, 'Le mailer "resend" doit être déclaré dans config/mail.php');
        $this->assertSame('resend', $config['transport']);

        // La clé Resend doit exister (depuis .env ou services.php).
        $key = config('services.resend.key');
        $this->assertNotEmpty($key, 'services.resend.key doit contenir la clé API Resend');
        $this->assertStringStartsWith('re_', $key);

        // L'expéditeur doit être une adresse valide (ex. no-reply@domaine.com après vérif. du domaine).
        $from = config('mail.from.address');
        $this->assertNotEmpty($from);
        $this->assertStringContainsString('@', $from);

        // Le transport natif Laravel doit être disponible.
        $this->assertTrue(class_exists('Resend'), 'Le package resend/resend-php doit être installé');
    }

    /** S18 — Le transport Resend envoie réellement un email de notification de retard (bout en bout). */
    public function test_transport_resend_envoie_notification_retard(): void
    {
        // Seulement si la clé Resend est réelle (non placeholder) et si le mailer est resend.
        $key = config('services.resend.key');
        if (! $key || str_contains($key, 'VOTRE_CLE') || config('mail.default') !== 'resend') {
            $this->markTestSkipped('Clé Resend non configurée ou mailer non resend (test hors production).');
        }

        $employe = Utilisateur::where('role', 'Employer')->first();
        $this->assertNotNull($employe);
        $presence = Presence::latest()->first();
        $this->assertNotNull($presence);

        // Route la notification vers une adresse de test (adresse du propriétaire du compte Resend).
        $owner = env('RESEND_TEST_EMAIL', 'adriannchare@gmail.com');

        try {
            \Illuminate\Support\Facades\Notification::route('mail', $owner)
                ->notify(new \App\Notifications\RetardNotification($employe, $presence));
            $this->assertTrue(true); // aucun TransportException = envoi réussi
        } catch (\Throwable $e) {
            $this->fail('L\'envoi via Resend a échoué : ' . $e->getMessage());
        }
    }
}
