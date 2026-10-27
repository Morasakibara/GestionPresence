<?php

namespace Tests\Feature;

use App\Models\Facture;
use App\Models\ServiceFourni;
use App\Models\Utilisateur;
use App\Models\WhatsappEnvoi;
use App\Services\FacturePdfService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Services — flux de facturation adapté : AUCUN acompte sur les services.
 * Un service est payé en totalité dès la saisie → PDF figé en phase « solde »
 * (hash SHA-256) → WhatsApp automatique. La clôture par encaissement du solde
 * ne subsiste que pour les services historiques en attente.
 */
class ServiceFacturationTest extends TestCase
{
    use DatabaseTransactions;

    private function loginDirectrice(): Utilisateur
    {
        $directrice = Utilisateur::where('role', 'Superviseur')->first();
        $this->actingAs($directrice);
        session(['current_role' => 'Superviseur']);

        return $directrice;
    }

    /** Enregistrement d'un service payé intégralement (avec WhatsApp) */
    public function test_enregistrer_service_paye_integralement_avec_whatsapp(): void
    {
        $directrice = $this->loginDirectrice();

        $response = $this->post('/directrice/services', [
            'type' => 'impression',
            'montant' => 15000,
            'acompte' => 6000, // ignoré : aucun acompte sur les services
            'client_nom' => 'Mme Bela',
            'numero_whatsapp' => '+237690123456',
            'details' => 'Affiches A3',
        ]);

        $response->assertRedirect('/directrice/services');

        $service = ServiceFourni::where('superviseur_id', $directrice->id)
            ->where('numero_whatsapp', '+237690123456')
            ->latest('id')
            ->first();

        $this->assertNotNull($service, 'Service non créé');
        $this->assertEquals(15000, $service->montant);
        $this->assertEquals(15000, $service->montant_paye, 'Le service doit être payé en totalité dès la saisie');
        $this->assertEquals('paye', $service->statut_paiement);
        $this->assertNotNull($service->numero_facture);
        $this->assertStringStartsWith('SVC-', $service->numero_facture);
        $this->assertTrue($service->estVerrouillee());

        // La facture + son PDF doivent exister en phase solde, rattachés au service
        $facture = Facture::where('service_id', $service->id)->first();
        $this->assertNotNull($facture, 'Facture non créée');
        $this->assertEquals(Facture::PHASE_SOLDE, $facture->phase);
        $this->assertNotEmpty($facture->hash, 'Empreinte SHA-256 manquante');
        $this->assertNull($facture->commande_id, 'La facture ne doit pas être rattachée à une commande');
        $this->assertTrue(Storage::disk('local')->exists($facture->pdf_path), 'PDF non généré');

        // Étape 4.2 — un envoi WhatsApp doit être programmé (en file d'attente ou déjà parti)
        $envoi = WhatsappEnvoi::where('service_id', $service->id)->first();
        $this->assertNotNull($envoi, 'Envoi WhatsApp non programmé');
        $this->assertNotEquals(WhatsappEnvoi::STATUT_ECHEC, $envoi->statut, 'Envoi WhatsApp en échec');
        $this->assertEquals('+237690123456', $envoi->telephone);
    }

    /** Service payé comptant → facture en phase solde, déjà payée */
    public function test_enregistrer_service_comptant_whatsapp(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/services', [
            'type' => 'photocopie',
            'montant' => 5000,
            'acompte' => 5000,
            'numero_whatsapp' => '+237690123456',
        ])->assertRedirect('/directrice/services');

        $service = ServiceFourni::where('superviseur_id', $directrice->id)
            ->where('montant', 5000)->latest('id')->first();

        $this->assertNotNull($service);
        $this->assertEquals('paye', $service->statut_paiement);

        $facture = Facture::where('service_id', $service->id)->first();
        $this->assertEquals(Facture::PHASE_SOLDE, $facture->phase);
        $this->assertEquals(0, (float) $facture->solde);
    }

    /** Un « acompte » transmis sur un service est ignoré : paiement intégral quand même */
    public function test_acompte_ignored_service_toujours_paye_en_totalite(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/services', [
            'type' => 'impression',
            'montant' => 5000,
            'acompte' => 8000,
            'numero_whatsapp' => '+237690123456',
        ])->assertRedirect('/directrice/services');

        $service = ServiceFourni::where('superviseur_id', $directrice->id)
            ->where('montant', 5000)->latest('id')->first();

        $this->assertNotNull($service, 'Le service doit être créé');
        $this->assertEquals('paye', $service->statut_paiement);
        $this->assertEquals(5000, (float) $service->montant_paye, 'Le service est payé en totalité, jamais plus');
    }

    /** Clôture exacte du solde d'un service historique → statut payé + facture solde figée */
    public function test_encaisser_solde_service(): void
    {
        $directrice = $this->loginDirectrice();

        // Service « historique » créé avant la règle sans acompte : part en attente de solde.
        $numero = 'SVC-'.now()->format('Ymd').'-TEST'.uniqid();
        $service = ServiceFourni::create([
            'superviseur_id' => $directrice->id,
            'type' => 'impression',
            'montant' => 12000,
            'montant_paye' => 5000,
            'statut_paiement' => ServiceFourni::STATUT_EN_ATTENTE_SOLDE,
            'date' => now()->toDateString(),
            'numero_whatsapp' => '+237690123456',
            'numero_facture' => $numero,
            'prix_verrouille' => true,
        ]);
        FacturePdfService::figerService($service, $numero, Facture::PHASE_ACOMPTE);

        $response = $this->post("/directrice/services/{$service->id}/encaisser-solde");
        $response->assertRedirect('/directrice/services');

        $service->refresh();
        $this->assertEquals('paye', $service->statut_paiement);
        $this->assertEquals(12000, $service->montant_paye);

        // Deux factures figées : acompte (historique) + solde
        $factures = Facture::where('service_id', $service->id)->get();
        $this->assertEquals(2, $factures->count());
        $factureSolde = $factures->firstWhere('phase', Facture::PHASE_SOLDE);
        $this->assertNotNull($factureSolde, 'Facture solde non créée');
        $this->assertEquals(0, (float) $factureSolde->solde);
        $this->assertTrue(FacturePdfService::integrity($factureSolde), 'PDF altéré');
    }

    /** Re-clôture d'un service déjà payé → erreur explicite */
    public function test_encaisser_solde_du_payer_refuse(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/services', [
            'type' => 'scan',
            'montant' => 8000,
            'acompte' => 3000,
            'numero_whatsapp' => '+237690123456',
        ]);

        $service = ServiceFourni::where('superviseur_id', $directrice->id)
            ->where('numero_facture', 'like', 'SVC-%')->latest('id')->firstOrFail();

        $this->post("/directrice/services/{$service->id}/encaisser-solde")->assertRedirect();
        $this->post("/directrice/services/{$service->id}/encaisser-solde")
            ->assertRedirect('/directrice/services')
            ->assertSessionHas('error');

        $this->assertStringContainsString('déjà payé', session('error'));
    }

    /** Montant <= 1 000 FCFA → service enregistré sans facture PDF ni WhatsApp */
    public function test_service_sous_1000_sans_facture(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/services', [
            'type' => 'photocopie',
            'montant' => 800,
            'acompte' => 800,
            'client_nom' => 'Passage rapide',
            'numero_whatsapp' => '+237690123456', // fourni mais ignoré (pas de facture)
        ])->assertRedirect('/directrice/services');

        $service = ServiceFourni::where('superviseur_id', $directrice->id)
            ->where('montant', 800)->latest('id')->first();

        $this->assertNotNull($service, 'Service non créé');
        $this->assertEquals('paye', $service->statut_paiement);
        $this->assertNull($service->numero_facture, 'Aucune facture ne doit être générée sous 1 000 FCFA');

        $this->assertEquals(0, Facture::where('service_id', $service->id)->count(), 'Aucune facture PDF attendue');
        $this->assertEquals(0, WhatsappEnvoi::where('service_id', $service->id)->count(), 'Aucun envoi WhatsApp attendu');
    }

    /** Petit montant historique : solde encaissable mais sans génération de facture */
    public function test_petit_service_solde_sans_facture(): void
    {
        $directrice = $this->loginDirectrice();

        // Service « historique » sous le seuil de facturation, en attente de solde.
        $service = ServiceFourni::create([
            'superviseur_id' => $directrice->id,
            'type' => 'scan',
            'montant' => 900,
            'montant_paye' => 400,
            'statut_paiement' => ServiceFourni::STATUT_EN_ATTENTE_SOLDE,
            'date' => now()->toDateString(),
        ]);

        $this->assertNull($service->numero_facture);
        $this->assertEquals(ServiceFourni::STATUT_EN_ATTENTE_SOLDE, $service->statut_paiement);

        $this->post("/directrice/services/{$service->id}/encaisser-solde")
            ->assertRedirect('/directrice/services');

        $service->refresh();
        $this->assertEquals('paye', $service->statut_paiement);
        $this->assertEquals(900, (float) $service->montant_paye);
        $this->assertEquals(0, Facture::where('service_id', $service->id)->count(), 'Aucune facture solde attendue');
    }

    /** Service facturable sans numéro WhatsApp → refusé (facture impossible) */
    public function test_service_facturable_sans_whatsapp_refuse(): void
    {
        $directrice = $this->loginDirectrice();

        $avant = ServiceFourni::where('superviseur_id', $directrice->id)->count();

        $this->post('/directrice/services', [
            'type' => 'impression',
            'montant' => 7000,
        ])->assertSessionHasErrors('numero_whatsapp');

        $this->assertEquals($avant, ServiceFourni::where('superviseur_id', $directrice->id)->count(),
            'Aucun service facturable ne doit être créé sans numéro client');
    }
}
