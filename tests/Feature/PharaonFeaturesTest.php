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
use Tests\TestCase;

class PharaonFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    private const PARIS_LAT = 48.856613;
    private const PARIS_LON = 2.352222;

    private function loginEmploye(): Utilisateur
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        $this->assertNotNull($employe);
        $this->post('/login', ['email' => $employe->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($employe);

        return $employe;
    }

    private function loginAdmin(): Utilisateur
    {
        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

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

        $response->assertSessionHas('success');
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

        $response->assertSessionHas('success');
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
        ])->assertSessionHas('success');

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
        $response->assertSessionHas('success');

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
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'type' => \App\Notifications\RetardNotification::class,
        ]);
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

        $response->assertRedirect();
        $response->assertSessionHas('success');

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
        ])->assertSessionHas('success');

        $this->post('/mark-departure', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'rendement' => 'Développement de la page de rendement.',
        ])->assertSessionHas('success');

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
    }

    /** Le rapport superviseur inclut réalisations et évaluations. */
    public function test_rapport_superviseur_inclut_rendements(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 0));
        $employe = $this->loginEmploye();
        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        $this->post('/login', ['email' => $superviseur->email, 'password' => 'password']);
        $this->post('/select-role', ['role' => 'Superviseur']);

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

    /** S2 — L'admin exporte le CSV des évaluations et rendements. */
    public function test_admin_export_csv_evaluations_rendements(): void
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
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Employé', $response->getContent());
        $this->assertStringContainsString('Note /20', $response->getContent());
        $this->assertStringContainsString($employe->nom, $response->getContent());
        $this->assertStringContainsString('Livraison du module de facturation.', $response->getContent());
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
        ])->assertSessionHas('success');

        // L'admin qui évalue est l'admin principal -> pas de notification (évite l'auto-notification)
        $this->assertDatabaseMissing('notifications', [
            'type' => \App\Notifications\EvaluationRougeNotification::class,
        ]);

        // Le superviseur enregistre une évaluation rouge -> l'admin principal est notifié
        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        $this->post('/login', ['email' => $superviseur->email, 'password' => 'password']);
        $this->post('/select-role', ['role' => 'Superviseur']);

        $this->post('/superviseur/evaluations', [
            'employerID' => $employe->id,
            'mois' => '2026-08',
            'note' => 3,
            'couleur' => 'rouge',
            'commentaire' => 'Discipline critique.',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'type' => \App\Notifications\EvaluationRougeNotification::class,
        ]);
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

        $this->assertDatabaseHas('notifications', [
            'type' => \App\Notifications\EvaluationRougeNotification::class,
        ]);
    }

    /** S4 — Le superviseur consulte les fiches de rendement de son équipe. */
    public function test_superviseur_consulte_rendements_equipe(): void
    {
        $employe = $this->loginEmploye();
        $employerInfo = DB::table('employer')->where('id', $employe->id)->first();

        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        $this->post('/login', ['email' => $superviseur->email, 'password' => 'password']);
        $this->post('/select-role', ['role' => 'Superviseur']);

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

    /** S5 — Le superviseur exporte le CSV des rendements de son équipe. */
    public function test_superviseur_export_csv_rendements(): void
    {
        $employe = $this->loginEmploye();
        $employerInfo = DB::table('employer')->where('id', $employe->id)->first();

        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        $this->post('/login', ['email' => $superviseur->email, 'password' => 'password']);
        $this->post('/select-role', ['role' => 'Superviseur']);

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
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Employé', $response->getContent());
        $this->assertStringContainsString($employe->nom, $response->getContent());
        $this->assertStringContainsString('Traitement des commandes de la semaine.', $response->getContent());
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

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $employerInfo->Sup_id,
            'type' => \App\Notifications\FicheRendementRappelNotification::class,
        ]);

        // Si toutes les fiches sont remplies -> plus de notification
        Presence::where('employerID', $employe->id)->update(['rendement' => 'Tâches terminées.']);
        $this->artisan('presence:rappel-fiches-rendement')
            ->expectsOutputToContain('Aucun superviseur concerné');
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
        $this->post('/login', ['email' => $superviseur->email, 'password' => 'password']);
        $this->post('/select-role', ['role' => 'Superviseur']);

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
        $this->assertStringContainsString('Durée', $response->getContent());
        $this->assertStringContainsString('7h45', $response->getContent()); // 09:00 -> 16:45
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
        $this->post('/login', ['email' => $superviseur->email, 'password' => 'password']);
        $this->post('/select-role', ['role' => 'Superviseur']);

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
}
