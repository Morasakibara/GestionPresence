<?php

namespace Tests\Feature;

use App\Models\Facture;
use App\Models\ServiceFourni;
use App\Models\Utilisateur;
use App\Models\WhatsappEnvoi;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Étape 2 services — même flux de facturation que les commandes :
 * acompte → PDF figé (hash SHA-256) → WhatsApp automatique, puis
 * clôture par encaissement du solde exact.
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

    /** Enregistrement d'un service via le formulaire acompte (avec WhatsApp) */
    public function test_enregistrer_service_acompte_avec_whatsapp(): void
    {
        $directrice = $this->loginDirectrice();

        $response = $this->post('/directrice/services', [
            'type'            => 'impression',
            'montant'         => 15000,
            'acompte'         => 6000,
            'client_nom'      => 'Mme Bela',
            'numero_whatsapp' => '+237690123456',
            'details'         => 'Affiches A3',
        ]);

        $response->assertRedirect('/directrice/services');

        $service = ServiceFourni::where('superviseur_id', $directrice->id)
            ->where('numero_whatsapp', '+237690123456')
            ->latest('id')
            ->first();

        $this->assertNotNull($service, 'Service non créé');
        $this->assertEquals(15000, $service->montant);
        $this->assertEquals(6000, $service->montant_paye);
        $this->assertEquals(ServiceFourni::STATUT_EN_ATTENTE_SOLDE, $service->statut_paiement);
        $this->assertNotNull($service->numero_facture);
        $this->assertStringStartsWith('SVC-', $service->numero_facture);
        $this->assertTrue($service->estVerrouillee());

        // La facture d'acompte + son PDF doivent exister, rattachées au service
        $facture = Facture::where('service_id', $service->id)->first();
        $this->assertNotNull($facture, 'Facture non créée');
        $this->assertEquals(Facture::PHASE_ACOMPTE, $facture->phase);
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
            'type'            => 'photocopie',
            'montant'         => 5000,
            'acompte'         => 5000,
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

    /** Acompte supérieur au montant total → refusé avec message clair */
    public function test_acompte_depasse_montant_refuse(): void
    {
        $directrice = $this->loginDirectrice();

        $avant = ServiceFourni::where('superviseur_id', $directrice->id)->count();

        $this->post('/directrice/services', [
            'type'            => 'impression',
            'montant'         => 5000,
            'acompte'         => 8000,
            'numero_whatsapp' => '+237690123456',
        ])->assertSessionHasErrors('acompte');

        $this->assertStringContainsString(
            'ne peut pas dépasser le montant total',
            session('errors')->first('acompte')
        );

        $this->assertEquals($avant, ServiceFourni::where('superviseur_id', $directrice->id)->count(), 'Aucun service ne doit être créé');
    }

    /** Clôture exacte du solde → statut payé + facture solde figée */
    public function test_encaisser_solde_service(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/services', [
            'type'            => 'impression',
            'montant'         => 12000,
            'acompte'         => 5000,
            'numero_whatsapp' => '+237690123456',
        ])->assertRedirect('/directrice/services');

        $service = ServiceFourni::where('superviseur_id', $directrice->id)
            ->where('numero_facture', 'like', 'SVC-%')->latest('id')->firstOrFail();

        $response = $this->post("/directrice/services/{$service->id}/encaisser-solde");
        $response->assertRedirect('/directrice/services');

        $service->refresh();
        $this->assertEquals('paye', $service->statut_paiement);
        $this->assertEquals(12000, $service->montant_paye);

        // Deux factures figées : acompte + solde
        $factures = Facture::where('service_id', $service->id)->get();
        $this->assertEquals(2, $factures->count());
        $factureSolde = $factures->firstWhere('phase', Facture::PHASE_SOLDE);
        $this->assertNotNull($factureSolde, 'Facture solde non créée');
        $this->assertEquals(0, (float) $factureSolde->solde);
        $this->assertTrue(\App\Services\FacturePdfService::integrity($factureSolde), 'PDF altéré');
    }

    /** Re-clôture d'un service déjà payé → erreur explicite */
    public function test_encaisser_solde_du_payer_refuse(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/services', [
            'type'            => 'scan',
            'montant'         => 8000,
            'acompte'         => 3000,
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
            'type'            => 'photocopie',
            'montant'         => 800,
            'acompte'         => 800,
            'client_nom'      => 'Passage rapide',
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

    /** Petit montant partiel : solde encaissable mais sans génération de facture */
    public function test_petit_service_solde_sans_facture(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/services', [
            'type'    => 'scan',
            'montant' => 900,
            'acompte' => 400,
        ])->assertRedirect('/directrice/services');

        $service = ServiceFourni::where('superviseur_id', $directrice->id)
            ->where('montant', 900)->latest('id')->firstOrFail();

        $this->assertNull($service->numero_facture);
        $this->assertEquals(ServiceFourni::STATUT_EN_ATTENTE_SOLDE, $service->statut_paiement);

        $this->post("/directrice/services/{$service->id}/encaisser-solde")
            ->assertRedirect('/directrice/services');

        $service->refresh();
        $this->assertEquals('paye', $service->statut_paiement);
        $this->assertEquals(900, (float) $service->montant_paye);
        $this->assertEquals(0, Facture::where('service_id', $service->id)->count(), 'Aucune facture solde attendue');
    }

    /** L'ancien formulaire (sans WhatsApp) continue de fonctionner */
    public function test_ancien_formulaire_sans_whatsapp(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/services', [
            'type'    => 'impression',
            'montant' => 7000,
        ])->assertRedirect('/directrice/services');

        $service = ServiceFourni::where('superviseur_id', $directrice->id)
            ->where('montant', 7000)->first();

        $this->assertNotNull($service);
        $this->assertNull($service->numero_facture);
        $this->assertFalse($service->estVerrouillee());
    }
}
