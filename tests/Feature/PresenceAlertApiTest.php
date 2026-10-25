<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Presence;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * L'API d'alertes de présence alimente les toasts et le son des consoles.
 * Elle ne doit renvoyer que les événements postérieurs au curseur `since` :
 * sinon chaque changement de page rejoue les alertes de la journée, avec le son.
 */
class PresenceAlertApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Enregistre une arrivée il y a $minutesAvant minutes, aujourd'hui.
     */
    private function creerArrivee(int $minutesAvant): Presence
    {
        return Presence::create([
            'Sup_id' => Superviseur::firstOrFail()->id,
            'employerID' => Employer::firstOrFail()->id,
            'heureArrivee' => now()->subMinutes($minutesAvant),
            'date' => now()->toDateString(),
            'status' => 'présent',
        ]);
    }

    public function test_les_alertes_anterieures_au_curseur_ne_sont_pas_rejouees(): void
    {
        $admin = Utilisateur::where('role', 'Administrateur')->firstOrFail();
        $this->creerArrivee(120);

        $response = $this->actingAs($admin)->getJson(
            '/api/presence-alerts?since='.now()->format('Y-m-d H:i:s')
        );

        // Une navigation ne doit jamais rejouer les arrivées déjà affichées.
        $response->assertOk()
            ->assertJsonCount(0, 'arrivals')
            ->assertJsonCount(0, 'departures');
    }

    public function test_une_arrivee_posterieure_au_curseur_est_renvoyee(): void
    {
        $admin = Utilisateur::where('role', 'Administrateur')->firstOrFail();
        $this->creerArrivee(1);

        $response = $this->actingAs($admin)->getJson(
            '/api/presence-alerts?since='.now()->subMinutes(10)->format('Y-m-d H:i:s')
        );

        $response->assertOk();
        $this->assertNotEmpty($response->json('arrivals'));
    }
}
