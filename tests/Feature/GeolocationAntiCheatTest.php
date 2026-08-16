<?php

namespace Tests\Feature;

use App\Models\Presence;
use App\Models\Utilisateur;
use App\Models\WorkplaceLocation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
}
