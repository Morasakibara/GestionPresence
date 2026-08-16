<?php

namespace Tests\Feature;

use App\Models\Presence;
use App\Models\Utilisateur;
use App\Models\WorkplaceLocation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeolocationAntiCheatTest extends TestCase
{
    use DatabaseTransactions;

    /** Coordonnées du siège social (seed WorkplaceLocationSeeder). */
    private const PARIS_LAT = 48.856613;
    private const PARIS_LON = 2.352222;

    private function loginEmploye(): Utilisateur
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        $this->assertNotNull($employe, 'Aucun employé seedé.');

        $this->post('/login', [
            'email' => $employe->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($employe);

        return $employe;
    }

    private function getSignature(float $lat, float $lon): string
    {
        $response = $this->postJson('/check-location', [
            'latitude' => $lat,
            'longitude' => $lon,
        ]);

        $response->assertOk()->assertJson(['valid' => true]);
        $this->assertArrayHasKey('signature', $response->json(), 'La réponse ne contient pas de signature.');

        return $response->json('signature');
    }

    public function test_pointage_complet_avec_signature_valide(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30)); // lundi 8h30
        $employe = $this->loginEmploye();

        $signature = $this->getSignature(self::PARIS_LAT, self::PARIS_LON);

        $response = $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            'signature' => $signature,
        ]);
        $response->assertSessionHas('success');

        $presence = Presence::where('employerID', $employe->id)
            ->whereDate('date', '2026-08-17')
            ->first();

        $this->assertNotNull($presence);
        $this->assertEquals('en attente', $presence->status);
        $this->assertFalse((bool) $presence->suspect, 'La présence ne devrait pas être suspecte.');
        $this->assertEquals(10, (int) $presence->accuracy_arrivee);

        // Départ le même jour à 17h30
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 17, 30));
        $signatureDepart = $this->getSignature(self::PARIS_LAT, self::PARIS_LON);

        $response = $this->post('/mark-departure', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'accuracy' => 12,
            'client_timestamp' => now()->timestamp,
            'signature' => $signatureDepart,
        ]);
        $response->assertSessionHas('success');

        $presence->refresh();
        $this->assertEquals('présent', $presence->status);
        $this->assertFalse((bool) $presence->suspect);
    }

    public function test_pointage_sans_signature_est_refuse(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $this->loginEmploye();

        $response = $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            // pas de signature
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('presence', [
            'date' => '2026-08-17',
            'employerID' => auth()->id(),
        ]);
    }

    public function test_signature_falsifiee_est_refusee(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        // Récupérer une signature valide mais la modifier
        $signature = $this->getSignature(self::PARIS_LAT, self::PARIS_LON);
        $tampered = $signature . 'ff';

        $response = $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            'signature' => $tampered,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('presence', [
            'date' => '2026-08-17',
            'employerID' => $employe->id,
        ]);
    }

    public function test_coordonnees_differentes_de_la_signature_sont_refusees(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        // Signature obtenue pour Paris, mais on soumet des coordonnées différentes
        $signature = $this->getSignature(self::PARIS_LAT, self::PARIS_LON);

        $response = $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT + 0.01, // décalage
            'longitude' => self::PARIS_LON,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            'signature' => $signature,
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('presence', [
            'date' => '2026-08-17',
            'employerID' => $employe->id,
        ]);
    }

    public function test_double_arrivee_est_refusee(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        // Première arrivée
        $signature = $this->getSignature(self::PARIS_LAT, self::PARIS_LON);
        $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            'signature' => $signature,
        ])->assertSessionHas('success');

        // Deuxième arrivée (nouvelle signature, mais déjà pointé aujourd'hui)
        $signature2 = $this->getSignature(self::PARIS_LAT, self::PARIS_LON);
        $response = $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            'signature' => $signature2,
        ]);

        $response->assertSessionHasErrors();

        $count = Presence::where('employerID', $employe->id)
            ->whereDate('date', '2026-08-17')
            ->count();
        $this->assertEquals(1, $count, 'Une seule arrivée doit exister.');
    }

    public function test_page_admin_suspectes_affiche_les_presences(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        // Créer une présence marquée suspecte (avec le vrai superviseur de l'employé)
        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Vitesse de déplacement irréaliste (43.5 km/h).',
            'vitesse_kmh' => 43.5,
            'distance_km' => 391.49,
        ]);

        // Se connecter en admin et vérifier la page
        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $response = $this->get('/admin/suspect-presences');
        $response->assertOk();
        $response->assertSee('Vitesse de déplacement irréaliste');
        $response->assertSee($employe->nom);

        // Filtre par recherche
        $response = $this->get('/admin/suspect-presences?search=' . urlencode($employe->nom));
        $response->assertOk();
        $response->assertSee($employe->nom);
    }

    public function test_workflow_traitement_presence_suspecte(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        // Créer une présence suspecte
        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Vitesse de déplacement irréaliste (43.5 km/h).',
        ]);

        // Se connecter en admin et traiter la présence
        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $response = $this->post("/admin/suspect-presences/{$presence->id}/update", [
            'statut_traitement' => 'justifié',
            'commentaire' => 'Déplacement professionnel confirmé.',
        ]);
        $response->assertRedirect(route('admin.suspectPresences'));
        $response->assertSessionHas('success');

        // Vérifier la mise à jour + l'historique
        $presence->refresh();
        $this->assertEquals('justifié', $presence->statut_traitement);
        $this->assertEquals('Déplacement professionnel confirmé.', $presence->commentaire_traitement);
        $this->assertEquals($admin->id, $presence->traite_par);
        $this->assertNotNull($presence->traite_le);

        $this->assertDatabaseHas('presence_traitements', [
            'presence_id' => $presence->id,
            'statut_avant' => 'nouveau',
            'statut_apres' => 'justifié',
        ]);

        // Statut invalide -> erreur de validation
        $response = $this->post("/admin/suspect-presences/{$presence->id}/update", [
            'statut_traitement' => 'inexistant',
        ]);
        $response->assertSessionHasErrors('statut_traitement');
    }

    public function test_superviseur_voit_ses_presences_suspectes(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Précision GPS insuffisante (320 m).',
        ]);

        // Se connecter en superviseur (celui de l'équipe de l'employé)
        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        $this->post('/login', ['email' => $superviseur->email, 'password' => 'password']);
        // Choisir le rôle Superviseur après login
        $this->post('/select-role', ['role' => 'Superviseur']);

        $response = $this->get('/superviseur/suspect-presences');
        $response->assertOk();
        $response->assertSee('Précision GPS insuffisante');
        $response->assertSee($employe->nom);
    }

    public function test_badge_suspect_non_traitees_dans_sidebar_admin(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Précision GPS insuffisante.',
            'statut_traitement' => 'nouveau',
        ]);

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        // La page suspectes utilise layouts.app (sidebar admin) où se trouve le badge
        $response = $this->get('/admin/suspect-presences');
        $response->assertOk();
        $response->assertSee('Présences suspectes');
        // Le badge doit apparaître dans la sidebar (compteur non traitées) — classe unique du badge
        $response->assertSee('bg-red-500 text-white');

        // Après traitement, le badge disparaît
        $this->post("/admin/suspect-presences/{$presence->id}/update", [
            'statut_traitement' => 'examiné',
        ])->assertSessionHas('success');
        $response = $this->get('/admin/suspect-presences');
        $response->assertOk();
        $response->assertDontSee('bg-red-500 text-white');
    }

    public function test_export_csv_suspect_presences(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Vitesse de déplacement irréaliste (43.5 km/h).',
            'distance_km' => 391.49,
            'vitesse_kmh' => 43.5,
            'statut_traitement' => 'nouveau',
        ]);

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $response = $this->get('/admin/suspect-presences/export?format=csv');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->getContent();
        $this->assertStringContainsString($employe->nom, $content);
        $this->assertStringContainsString('Motif de suspicion', $content);
        $this->assertStringContainsString('391,49', $content);
    }

    public function test_export_pdf_suspect_presences(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Vitesse de déplacement irréaliste (43.5 km/h).',
        ]);

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $response = $this->get('/admin/suspect-presences/export?format=pdf');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('%PDF', substr($response->getContent(), 0, 20));
    }

    public function test_superviseur_notifie_quand_presence_traitee(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Précision GPS insuffisante (320 m).',
            'statut_traitement' => 'nouveau',
        ]);

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        // Traiter la présence -> le superviseur de l'équipe doit être notifié
        $this->post("/admin/suspect-presences/{$presence->id}/update", [
            'statut_traitement' => 'justifié',
            'commentaire' => 'Déplacement validé.',
        ])->assertSessionHas('success');

        $superviseurUser = Utilisateur::find($presence->Sup_id);
        $this->assertNotNull($superviseurUser);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $superviseurUser->id,
            'type' => \App\Notifications\PresenceTraiteeNotification::class,
        ]);
    }

    public function test_employe_notifie_quand_sa_presence_est_traitee(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Précision GPS insuffisante (320 m).',
            'statut_traitement' => 'nouveau',
        ]);

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $this->post("/admin/suspect-presences/{$presence->id}/update", [
            'statut_traitement' => 'justifié',
            'commentaire' => 'Déplacement validé.',
        ])->assertSessionHas('success');

        // L'employé concerné doit aussi être notifié
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $employe->id,
            'type' => \App\Notifications\PresenceTraiteeNotification::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $presence->Sup_id,
            'type' => \App\Notifications\PresenceTraiteeNotification::class,
        ]);
    }

    public function test_filtre_par_statut_sur_page_suspectes(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        // Deux présences suspectes : une 'nouveau', une 'justifié'
        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Motif A (nouveau).',
            'statut_traitement' => 'nouveau',
        ]);
        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-16',
            'heureArrivee' => '2026-08-16 08:05:00',
            'heureDepart' => '2026-08-16 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Motif B (justifié).',
            'statut_traitement' => 'justifié',
        ]);

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        // Sans filtre : les deux motifs visibles
        $response = $this->get('/admin/suspect-presences');
        $response->assertOk();
        $response->assertSee('Motif A (nouveau)');
        $response->assertSee('Motif B (justifié)');

        // Filtre statut=justifié : seul le motif B reste
        $response = $this->get('/admin/suspect-presences?statut=justifié');
        $response->assertOk();
        $response->assertSee('Motif B (justifié)');
        $response->assertDontSee('Motif A (nouveau)');

        // Le select conserve la sélection
        $response->assertSee('value="justifié" selected', false);
    }

    public function test_commande_rappel_suspectes_non_traitees(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();

        // Présence suspecte ancienne (créée il y a 10 jours) non traitée
        // created_at n'est pas fillable -> on le fixe après création
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-01',
            'heureArrivee' => '2026-08-01 08:05:00',
            'heureDepart' => '2026-08-01 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Ancien motif non traité.',
            'statut_traitement' => 'nouveau',
        ]);
        \Illuminate\Support\Facades\DB::table('presence')
            ->where('id', $presence->id)
            ->update(['created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)]);

        // La commande doit notifier l'admin
        $this->artisan('presence:rappel-suspectes', ['--days' => 7])
            ->expectsOutputToContain('Rappel envoyé');

        $admin = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => \App\Notifications\SuspectRappelNotification::class,
        ]);

        // Après traitement, plus de rappel
        $presence->update(['statut_traitement' => 'examiné']);
        $this->artisan('presence:rappel-suspectes', ['--days' => 7])
            ->expectsOutputToContain('Aucune présence suspecte');
    }

    public function test_employe_conteste_sa_presence_suspecte(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Vitesse de déplacement irréaliste (43.5 km/h).',
            'statut_traitement' => 'nouveau',
        ]);

        // L'employé voit sa présence suspecte sur son rapport
        $response = $this->get('/user/presence-report');
        $response->assertOk();
        $response->assertSee('Présences suspectes');
        $response->assertSee('Vitesse de déplacement irréaliste');

        // Il la conteste
        $response = $this->post("/user/contester-presence/{$presence->id}", [
            'commentaire' => 'La géolocalisation a échoué ce jour-là, j\'étais présent.',
        ]);
        $response->assertSessionHas('success');

        $presence->refresh();
        $this->assertNotNull($presence->commentaire_contestation);
        $this->assertNotNull($presence->conteste_le);

        // L'admin est notifié
        $admin = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => \App\Notifications\PresenceContesteeNotification::class,
        ]);

        // La page admin affiche la contestation
        $this->post('/logout');
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);
        $response = $this->get('/admin/suspect-presences');
        $response->assertOk();
        $response->assertSee('Contesté');
        $response->assertSee('La géolocalisation a échoué');
    }

    public function test_employe_ne_peut_contester_que_sa_propre_presence(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        // Présence suspecte d'un AUTRE employé
        $autre = Utilisateur::where('role', 'Employer')->where('id', '!=', $employe->id)->first();
        $autreInfo = \App\Models\Employer::where('id', $autre->id)->first();
        $presenceAutre = Presence::create([
            'employerID' => $autre->id,
            'Sup_id' => $autreInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Précision GPS insuffisante.',
        ]);

        $response = $this->post("/user/contester-presence/{$presenceAutre->id}", [
            'commentaire' => 'Tentative de contester la présence d\'un collègue.',
        ]);
        $response->assertSessionHas('error');

        $presenceAutre->refresh();
        $this->assertNull($presenceAutre->commentaire_contestation);
    }

    public function test_bilan_hebdo_commande_notifie_admin(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        // Deux suspectes la semaine précédente (10-16 août 2026, fenêtre calculée par la commande)
        // created_at n'est pas fillable -> on le fixe après création via DB
        $p1 = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-12',
            'heureArrivee' => '2026-08-12 08:05:00',
            'heureDepart' => '2026-08-12 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Bilan nouveau.',
            'statut_traitement' => 'nouveau',
        ]);
        $p2 = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-13',
            'heureArrivee' => '2026-08-13 08:05:00',
            'heureDepart' => '2026-08-13 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Bilan justifié.',
            'statut_traitement' => 'justifié',
        ]);
        DB::table('presence')->whereIn('id', [$p1->id, $p2->id])
            ->update(['created_at' => '2026-08-13 10:00:00', 'updated_at' => '2026-08-13 10:00:00']);

        $this->artisan('presence:bilan-hebdo')
            ->expectsOutputToContain('Bilan hebdomadaire envoyé');

        $admin = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => \App\Notifications\BilanHebdoNotification::class,
        ]);

        $notification = DB::table('notifications')
            ->where('notifiable_id', $admin->id)
            ->where('type', \App\Notifications\BilanHebdoNotification::class)
            ->first();
        $data = json_decode($notification->data, true);
        $this->assertEquals(2, $data['total']);
        $this->assertEquals(1, $data['nouveau']);
        $this->assertEquals(1, $data['justifie']);
    }

    public function test_admin_repond_a_la_contestation(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Vitesse irréaliste.',
            'statut_traitement' => 'nouveau',
            'commentaire_contestation' => 'La géolocalisation a échoué.',
            'conteste_le' => now(),
        ]);

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        // Accord de la contestation -> statut justifié + notif employé
        $response = $this->post("/admin/suspect-presences/{$presence->id}/repondre-contestation", [
            'reponse' => 'accordé',
            'commentaire' => 'Preuve fournie par l\'employé.',
        ]);
        $response->assertSessionHas('success');

        $presence->refresh();
        $this->assertEquals('justifié', $presence->statut_traitement);
        $this->assertEquals('accordé', $presence->reponse_contestation);
        $this->assertNotNull($presence->reponse_contestation_le);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $employe->id,
            'type' => \App\Notifications\ContestationReponseNotification::class,
        ]);

        // La page admin affiche la réponse
        $response = $this->get('/admin/suspect-presences');
        $response->assertOk();
        $response->assertSee('Contestation accordé');
    }

    public function test_refus_contestation_rejette_la_presence(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Vitesse irréaliste.',
            'statut_traitement' => 'examiné',
            'commentaire_contestation' => 'Je conteste.',
            'conteste_le' => now(),
        ]);

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $response = $this->post("/admin/suspect-presences/{$presence->id}/repondre-contestation", [
            'reponse' => 'refusé',
            'commentaire' => 'Incohérence avec les logs.',
        ]);
        $response->assertSessionHas('success');

        $presence->refresh();
        $this->assertEquals('rejeté', $presence->statut_traitement);
        $this->assertEquals('refusé', $presence->reponse_contestation);

        // Historique journalisé
        $this->assertDatabaseHas('presence_traitements', [
            'presence_id' => $presence->id,
            'statut_avant' => 'examiné',
            'statut_apres' => 'rejeté',
        ]);
    }

    public function test_pointage_bloque_pour_recidiviste(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();

        // Créer 3 présences suspectes non justifiées (seuil par défaut)
        foreach (['2026-07-20', '2026-07-21', '2026-07-22'] as $date) {
            Presence::create([
                'employerID' => $employe->id,
                'Sup_id' => $employerInfo->Sup_id,
                'date' => $date,
                'heureArrivee' => $date . ' 08:05:00',
                'heureDepart' => $date . ' 17:30:00',
                'status' => 'présent',
                'suspect' => true,
                'motif_suspicion' => 'Précision GPS insuffisante.',
                'statut_traitement' => 'nouveau',
            ]);
        }

        $signature = $this->getSignature(self::PARIS_LAT, self::PARIS_LON);

        $response = $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            'signature' => $signature,
        ]);

        $response->assertSessionHasErrors();
        $this->assertStringContainsString('bloqué', session('errors')->first());

        // Aucune nouvelle présence créée
        $this->assertDatabaseMissing('presence', [
            'employerID' => $employe->id,
            'date' => '2026-08-17',
        ]);
    }

    public function test_pointage_autorise_avec_peu_de_suspectes(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();

        // Une seule suspecte non justifiée (< seuil)
        Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-07-20',
            'heureArrivee' => '2026-07-20 08:05:00',
            'heureDepart' => '2026-07-20 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Précision GPS insuffisante.',
            'statut_traitement' => 'nouveau',
        ]);

        $signature = $this->getSignature(self::PARIS_LAT, self::PARIS_LON);

        $response = $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            'signature' => $signature,
        ]);
        $response->assertSessionHas('success');
    }

    public function test_export_csv_inclut_contestation_et_reponse(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Vitesse irréaliste.',
            'statut_traitement' => 'justifié',
            'commentaire_contestation' => 'GPS défaillant ce jour-là',
            'conteste_le' => now(),
            'reponse_contestation' => 'accordé',
            'commentaire_reponse_contestation' => 'Preuve fournie',
            'reponse_contestation_le' => now(),
        ]);

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $response = $this->get('/admin/suspect-presences/export?format=csv');
        $response->assertOk();
        $content = $response->getContent();

        // En-têtes des nouvelles colonnes
        $this->assertStringContainsString('Contestation;Réponse admin', $content);
        // Contenu : contestation + réponse
        $this->assertStringContainsString('Contesté le', $content);
        $this->assertStringContainsString('GPS défaillant', $content);
        $this->assertStringContainsString('Accordé', $content);
        $this->assertStringContainsString('Preuve fournie', $content);
    }

    public function test_employe_voit_historique_complet_de_sa_presence(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Précision GPS insuffisante.',
            'statut_traitement' => 'justifié',
            'commentaire_contestation' => 'Je conteste ce pointage.',
            'conteste_le' => now(),
            'reponse_contestation' => 'accordé',
            'commentaire_reponse_contestation' => 'Validé par l\'admin.',
            'reponse_contestation_le' => now(),
        ]);

        // Un changement de statut dans l'historique
        \App\Models\PresenceTraitement::create([
            'presence_id' => $presence->id,
            'statut_avant' => 'nouveau',
            'statut_apres' => 'justifié',
            'commentaire' => 'Contestation acceptée',
            'traite_par' => Utilisateur::where('role', 'Administrateur')->value('id'),
        ]);

        $response = $this->get("/user/presence-history/{$presence->id}");
        $response->assertOk();
        $response->assertSee('Historique de la présence');
        $response->assertSee('Arrivée pointée');
        $response->assertSee('⚠️ Présence marquée suspecte');
        $response->assertSee('Contestation envoyée');
        $response->assertSee('✅ Contestation acceptée');
        $response->assertSee('Nouveau → Justifié');
    }

    public function test_employe_ne_voit_pas_historique_des_autres(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $this->loginEmploye();

        $autre = Utilisateur::where('role', 'Employer')->where('id', '!=', auth()->id())->first();
        $autreInfo = \App\Models\Employer::where('id', $autre->id)->first();
        $presenceAutre = Presence::create([
            'employerID' => $autre->id,
            'Sup_id' => $autreInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
        ]);

        $response = $this->get("/user/presence-history/{$presenceAutre->id}");
        $response->assertSessionHas('error');
    }

    public function test_superviseur_notifie_quand_membre_bloque(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();

        // Créer 3 présences suspectes non justifiées (seuil de blocage)
        foreach (['2026-07-20', '2026-07-21', '2026-07-22'] as $date) {
            Presence::create([
                'employerID' => $employe->id,
                'Sup_id' => $employerInfo->Sup_id,
                'date' => $date,
                'heureArrivee' => $date . ' 08:05:00',
                'heureDepart' => $date . ' 17:30:00',
                'status' => 'présent',
                'suspect' => true,
                'motif_suspicion' => 'Précision GPS insuffisante.',
                'statut_traitement' => 'nouveau',
            ]);
        }

        // Tentative de pointage bloquée
        $signature = $this->getSignature(self::PARIS_LAT, self::PARIS_LON);
        $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            'signature' => $signature,
        ])->assertSessionHasErrors();

        // Le superviseur de l'employé doit avoir reçu la notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $employerInfo->Sup_id,
            'type' => \App\Notifications\MembresBloquesNotification::class,
        ]);
    }

    public function test_admin_voit_timeline_depuis_page_suspectes(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Précision GPS insuffisante.',
        ]);

        // L'admin accède à la timeline depuis la page suspectes
        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $response = $this->get('/admin/suspect-presences');
        $response->assertOk();
        $response->assertSee("Voir l'historique", false);

        $response = $this->get("/admin/presence-history/{$presence->id}");
        $response->assertOk();
        $response->assertSee('Historique de la présence');
        $response->assertSee($employe->nom); // nom de l'employé affiché pour l'admin
    }

    public function test_superviseur_voit_timeline_membre_equipe_mais_pas_autre_equipe(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Vitesse irréaliste.',
        ]);

        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        $this->post('/login', ['email' => $superviseur->email, 'password' => 'password']);
        $this->post('/select-role', ['role' => 'Superviseur']);

        // Timeline d'un membre de SON équipe -> OK
        $response = $this->get("/superviseur/presence-history/{$presence->id}");
        $response->assertOk();
        $response->assertSee('Historique de la présence');

        // Présence d'un employé d'une AUTRE équipe -> refus
        $autreSuperviseur = Utilisateur::where('role', 'Superviseur')->where('id', '!=', $superviseur->id)->first();
        $membreAutreEquipe = \App\Models\Employer::where('Sup_id', $autreSuperviseur->id)->first();
        $presenceAutre = Presence::create([
            'employerID' => $membreAutreEquipe->id,
            'Sup_id' => $autreSuperviseur->id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Test.',
        ]);

        $response = $this->get("/superviseur/presence-history/{$presenceAutre->id}");
        $response->assertSessionHas('error');
    }

    public function test_export_pdf_timeline_presence(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();
        $presence = Presence::create([
            'employerID' => $employe->id,
            'Sup_id' => $employerInfo->Sup_id,
            'date' => '2026-08-17',
            'heureArrivee' => '2026-08-17 08:05:00',
            'heureDepart' => '2026-08-17 17:30:00',
            'status' => 'présent',
            'suspect' => true,
            'motif_suspicion' => 'Vitesse irréaliste (43.5 km/h).',
            'commentaire_contestation' => 'GPS en panne',
            'conteste_le' => now(),
        ]);

        $response = $this->get("/user/presence-history/{$presence->id}/pdf");
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('%PDF', substr($response->getContent(), 0, 20));
    }

    public function test_rappel_quotidien_membres_bloques(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();

        // Créer 3 présences suspectes non justifiées (blocage)
        foreach (['2026-07-20', '2026-07-21', '2026-07-22'] as $date) {
            Presence::create([
                'employerID' => $employe->id,
                'Sup_id' => $employerInfo->Sup_id,
                'date' => $date,
                'heureArrivee' => $date . ' 08:05:00',
                'heureDepart' => $date . ' 17:30:00',
                'status' => 'présent',
                'suspect' => true,
                'motif_suspicion' => 'Précision GPS insuffisante.',
                'statut_traitement' => 'nouveau',
            ]);
        }

        // La commande de rappel quotidien notifie le superviseur
        $this->artisan('presence:rappel-blocages')
            ->expectsOutputToContain('notifié');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $employerInfo->Sup_id,
            'type' => \App\Notifications\MembresBloquesNotification::class,
        ]);

        // Une fois les présences traitées, plus de notification
        Presence::where('employerID', $employe->id)->update(['statut_traitement' => 'justifié']);
        $this->artisan('presence:rappel-blocages')
            ->expectsOutputToContain('Aucun membre bloqué');
    }

    public function test_vitesse_irrealiste_marque_suspect(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();

        // Arrivée au siège Paris
        $signature = $this->getSignature(self::PARIS_LAT, self::PARIS_LON);
        $this->post('/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            'signature' => $signature,
        ])->assertSessionHas('success');

        // Départ à Lyon le même jour -> vitesse irréaliste
        $lyonLat = 45.764043;
        $lyonLon = 4.835659;
        $workplaceLyon = WorkplaceLocation::where('nom', 'Agence Lyon')->first();
        $this->assertNotNull($workplaceLyon);

        Carbon::setTestNow(Carbon::create(2026, 8, 17, 17, 30));
        $signatureDepart = $this->getSignature($lyonLat, $lyonLon);
        $this->post('/mark-departure', [
            'latitude' => $lyonLat,
            'longitude' => $lyonLon,
            'accuracy' => 10,
            'client_timestamp' => now()->timestamp,
            'signature' => $signatureDepart,
        ])->assertSessionHas('success');

        $presence = Presence::where('employerID', $employe->id)
            ->whereDate('date', '2026-08-17')
            ->first();

        $this->assertNotNull($presence);
        $this->assertTrue((bool) $presence->suspect, 'La présence devrait être marquée suspecte (Paris -> Lyon en 9h).');
        $this->assertNotNull($presence->vitesse_kmh);
        $this->assertGreaterThan(0, $presence->vitesse_kmh);
        $this->assertStringContainsString('Vitesse', $presence->motif_suspicion);
    }

    public function test_stats_admin_affiche_suspectes_et_bloques(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();
        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();

        // 3 présences suspectes non justifiées -> employé bloqué
        foreach (['2026-08-01', '2026-08-03', '2026-08-05'] as $date) {
            Presence::create([
                'employerID' => $employe->id,
                'Sup_id' => $employerInfo->Sup_id,
                'date' => $date,
                'heureArrivee' => $date . ' 08:05:00',
                'heureDepart' => $date . ' 17:30:00',
                'status' => 'présent',
                'suspect' => true,
                'motif_suspicion' => 'Précision GPS insuffisante.',
                'statut_traitement' => 'nouveau',
            ]);
        }

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $response = $this->get('/admin/stats-suspects');
        $response->assertOk();
        $response->assertSee('Statistiques des présences suspectes', false);
        $response->assertSee($employe->nom); // employé bloqué listé
    }

    public function test_stats_admin_refuse_pour_employe(): void
    {
        $employe = $this->loginEmploye();

        $response = $this->get('/admin/stats-suspects');
        $response->assertRedirect(); // l'employé n'accède pas à l'espace admin
    }

    public function test_deblocage_manuel_employe_par_admin(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));
        $employe = $this->loginEmploye();
        $employerInfo = \App\Models\Employer::where('id', $employe->id)->first();

        // 3 suspectes non justifiées -> bloqué
        $ids = [];
        foreach (['2026-08-01', '2026-08-03', '2026-08-05'] as $date) {
            $ids[] = Presence::create([
                'employerID' => $employe->id,
                'Sup_id' => $employerInfo->Sup_id,
                'date' => $date,
                'heureArrivee' => $date . ' 08:05:00',
                'heureDepart' => $date . ' 17:30:00',
                'status' => 'présent',
                'suspect' => true,
                'motif_suspicion' => 'Précision GPS insuffisante.',
                'statut_traitement' => 'nouveau',
            ])->id;
        }

        $admin = Utilisateur::where('role', 'Administrateur')->first();
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);

        $response = $this->post("/admin/unblock-employe/{$employe->id}", [
            'commentaire' => 'Vérification manuelle effectuée.',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Toutes les suspectes passent en justifié
        foreach ($ids as $id) {
            $this->assertDatabaseHas('presence', [
                'id' => $id,
                'statut_traitement' => 'justifié',
                'commentaire_traitement' => 'Vérification manuelle effectuée.',
            ]);
        }

        // Journalisé dans l'historique
        $this->assertDatabaseHas('presence_traitements', [
            'presence_id' => $ids[0],
            'statut_avant' => 'nouveau',
            'statut_apres' => 'justifié',
            'traite_par' => $admin->id,
        ]);
    }

    public function test_deblocage_refuse_pour_employe_non_admin(): void
    {
        $employe = $this->loginEmploye();

        // L'employé n'a pas le droit de débloquer : le middleware isAdmin le redirige vers /
        $response = $this->post("/admin/unblock-employe/{$employe->id}", [
            'commentaire' => 'tentative',
        ]);

        $response->assertRedirect('/');
    }

    public function test_stats_superviseur_affiche_suspectes_de_son_equipe(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 17, 8, 30));

        $superviseur = Utilisateur::where('role', 'Superviseur')->first();
        $this->assertNotNull($superviseur);
        $this->post('/login', ['email' => $superviseur->email, 'password' => 'password']);
        $this->post('/select-role', ['role' => 'Superviseur']);

        // Membres de l'équipe du superviseur
        $superviseurInfo = \App\Models\Superviseur::where('id', $superviseur->id)->first();
        $employerIds = \App\Models\Employer::where('equipe', $superviseurInfo->equipe)->pluck('id')->toArray();
        $this->assertNotEmpty($employerIds);

        // 2 suspectes pour le premier membre de l'équipe + 1 pour un employé hors équipe
        $membre = Utilisateur::find($employerIds[0]);
        foreach (['2026-08-01', '2026-08-03'] as $date) {
            Presence::create([
                'employerID' => $membre->id,
                'Sup_id' => $superviseur->id,
                'date' => $date,
                'heureArrivee' => $date . ' 08:05:00',
                'heureDepart' => $date . ' 17:30:00',
                'status' => 'présent',
                'suspect' => true,
                'motif_suspicion' => 'Vitesse irréaliste (43.5 km/h).',
                'statut_traitement' => 'nouveau',
            ]);
        }

        // Employé d'une autre équipe
        $autreEmploye = Utilisateur::where('role', 'Employer')->whereNotIn('id', $employerIds)->first();
        if ($autreEmploye) {
            Presence::create([
                'employerID' => $autreEmploye->id,
                'Sup_id' => 5,
                'date' => '2026-08-05',
                'heureArrivee' => '2026-08-05 08:05:00',
                'heureDepart' => '2026-08-05 17:30:00',
                'status' => 'présent',
                'suspect' => true,
                'motif_suspicion' => 'Précision GPS insuffisante.',
                'statut_traitement' => 'nouveau',
            ]);
        }

        $response = $this->get('/superviseur/stats-suspects');
        $response->assertOk();
        $response->assertSee('Statistiques des suspicions', false);
        $response->assertSee('2', false); // total suspectes = équipe uniquement

        // L'employé hors équipe ne doit pas apparaître dans les stats de ce superviseur
        if ($autreEmploye) {
            $response->assertDontSee($autreEmploye->nom);
        }
    }

    public function test_stats_superviseur_refuse_pour_employe(): void
    {
        $this->loginEmploye();

        $response = $this->get('/superviseur/stats-suspects');
        $response->assertStatus(302);
    }
}
