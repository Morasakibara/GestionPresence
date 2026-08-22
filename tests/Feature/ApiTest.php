<?php

namespace Tests\Feature;

use App\Models\Commande;
use App\Models\Presence;
use App\Models\Utilisateur;
use App\Models\WorkplaceLocation;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    private const PARIS_LAT = 48.856613;
    private const PARIS_LON = 2.352222;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        if (!WorkplaceLocation::where('actif', true)->exists()) {
            WorkplaceLocation::create([
                'nom' => 'Siège Le Pharaon (test API)',
                'latitude' => self::PARIS_LAT,
                'longitude' => self::PARIS_LON,
                'rayon' => 5000,
                'actif' => true,
            ]);
        }
    }

    // ═══════════════ AUTH ═══════════════

    public function test_login_reussi(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();

        $response = $this->postJson('/api/login', [
            'email' => $employe->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['user' => ['id', 'nom', 'email', 'role'], 'token']);
    }

    public function test_login_echoue(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'inexistant@lepharaon.com',
            'password' => 'mauvais',
        ]);

        $response->assertUnprocessable();
    }

    public function test_logout(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        $token = $employe->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');
        $response->assertOk();
    }

    public function test_user_profil(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        Sanctum::actingAs($employe);

        $response = $this->getJson('/api/user');
        $response->assertOk()
            ->assertJsonFragment(['id' => $employe->id, 'nom' => $employe->nom]);
    }

    public function test_user_sans_auth(): void
    {
        $response = $this->getJson('/api/user');
        $response->assertUnauthorized();
    }

    // ═══════════════ PRÉSENCE ═══════════════

    public function test_mark_arrival(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        Sanctum::actingAs($employe);

        $response = $this->postJson('/api/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'presence']);

        $this->assertDatabaseHas('presence', [
            'employerID' => $employe->id,
            'status' => 'en attente',
        ]);
    }

    public function test_mark_arrival_hors_zone(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        Sanctum::actingAs($employe);

        $response = $this->postJson('/api/mark-arrival', [
            'latitude' => 35.6762,
            'longitude' => 139.6503,
        ]);

        $response->assertStatus(422);
    }

    public function test_mark_departure(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        Sanctum::actingAs($employe);

        $this->postJson('/api/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ]);

        $response = $this->postJson('/api/mark-departure', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
            'rendement' => 'Développement de la page mobile.',
        ]);

        $response->assertOk();
    }

    public function test_mark_departure_sans_rendement(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        Sanctum::actingAs($employe);

        $this->postJson('/api/mark-arrival', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ]);

        $response = $this->postJson('/api/mark-departure', [
            'latitude' => self::PARIS_LAT,
            'longitude' => self::PARIS_LON,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('rendement');
    }

    public function test_presences_index(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        Sanctum::actingAs($employe);

        $response = $this->getJson('/api/presences');
        $response->assertOk()
            ->assertJsonStructure(['presences']);
    }

    public function test_presences_today(): void
    {
        $employe = Utilisateur::where('role', 'Employer')->first();
        Sanctum::actingAs($employe);

        $response = $this->getJson('/api/presences/today');
        $response->assertOk()
            ->assertJsonStructure(['presence']);
    }

    // ═══════════════ COMMANDES ═══════════════

    public function test_commandes_index(): void
    {
        $sup = Utilisateur::where('role', 'Superviseur')->first();
        Sanctum::actingAs($sup);

        $response = $this->getJson('/api/commandes');
        $response->assertOk()
            ->assertJsonStructure(['commandes']);
    }

    public function test_commandes_store(): void
    {
        $sup = Utilisateur::where('role', 'Superviseur')->first();
        Sanctum::actingAs($sup);

        $response = $this->postJson('/api/commandes', [
            'type' => 'impression',
            'montant' => 5000,
            'montant_paye' => 3000,
            'statut_paiement' => 'partiel',
            'details' => '100 pages noir et blanc',
        ]);

        $response->assertCreated();
    }

    public function test_commandes_store_paye(): void
    {
        $sup = Utilisateur::where('role', 'Superviseur')->first();
        Sanctum::actingAs($sup);

        $response = $this->postJson('/api/commandes', [
            'type' => 'photocopie',
            'montant' => 2000,
            'montant_paye' => 2000,
            'statut_paiement' => 'paye',
        ]);

        $response->assertCreated();
    }

    public function test_services_index(): void
    {
        $sup = Utilisateur::where('role', 'Superviseur')->first();
        Sanctum::actingAs($sup);

        $response = $this->getJson('/api/services');
        $response->assertOk()
            ->assertJsonStructure(['services']);
    }
}
