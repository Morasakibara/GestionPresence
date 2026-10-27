<?php

namespace Tests\Feature;

use App\Models\Commande;
use App\Models\Facture;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FactureAcompteTest extends TestCase
{
    use DatabaseTransactions;

    private function loginDirectrice(): Utilisateur
    {
        $directrice = Utilisateur::where('role', 'Superviseur')->first();
        $this->actingAs($directrice);
        session(['current_role' => 'Superviseur']);

        return $directrice;
    }

    /** Enregistrement d'une commande via le formulaire acompte (avec WhatsApp) */
    public function test_enregistrer_commande_acompte_avec_whatsapp(): void
    {
        $directrice = $this->loginDirectrice();

        $response = $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 10000,
            'acompte' => 4000,
            'client_nom' => 'Mme Bela',
            'numero_whatsapp' => '+237690123456',
            'details' => 'Flyers A5',
        ]);

        $response->assertRedirect('/directrice/commandes');

        $commande = Commande::where('superviseur_id', $directrice->id)
            ->where('numero_whatsapp', '+237690123456')
            ->latest('id')
            ->first();

        $this->assertNotNull($commande, 'Commande non créée');
        $this->assertEquals(10000, $commande->montant);
        $this->assertEquals(4000, $commande->montant_paye);
        $this->assertEquals('en_attente_solde', $commande->statut_paiement);
        $this->assertNotNull($commande->numero_facture);

        // La facture d'acompte + son PDF doivent exister
        $facture = Facture::where('commande_id', $commande->id)->first();
        $this->assertNotNull($facture, 'Facture non créée');
        $this->assertEquals(Facture::PHASE_ACOMPTE, $facture->phase);
        $this->assertNotEmpty($facture->hash, 'Empreinte SHA-256 manquante');
        $this->assertTrue(
            \Storage::disk('local')->exists($facture->pdf_path),
            'PDF non généré'
        );
    }

    /** Commande payée comptant → facture en phase solde */
    public function test_enregistrer_commande_comptant_whatsapp(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/commandes', [
            'type' => 'photocopie',
            'montant' => 5000,
            'acompte' => 5000,
            'numero_whatsapp' => '+237690123456',
        ])->assertRedirect('/directrice/commandes');

        $facture = Facture::latest('id')->first();
        $this->assertNotNull($facture);
        $this->assertEquals(Facture::PHASE_SOLDE, $facture->phase);
        $this->assertEquals(0, (float) $facture->solde);
        $this->assertEquals('+237690123456', $facture->client_whatsapp);
    }

    /** WhatsApp sans indicatif international → +237 ajouté par défaut puis accepté */
    public function test_whatsapp_sans_indicatif_normalise(): void
    {
        $directrice = $this->loginDirectrice();

        // Règle métier : le préfixe +237 est ajouté par défaut avant validation,
        // un numéro saisi sans indicatif est donc accepté et normalisé.
        $this->post('/directrice/commandes', [
            'type' => 'photocopie',
            'montant' => 5000,
            'acompte' => 5000,
            'numero_whatsapp' => '690123456',
        ])->assertSessionMissing('errors');

        $this->assertDatabaseHas('commandes', [
            'numero_whatsapp' => '+237690123456',
        ]);
    }

    /** WhatsApp invalide → refusé */
    public function test_whatsapp_invalide_refuse(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 10000,
            'acompte' => 4000,
            'numero_whatsapp' => 'abc',
        ])->assertSessionHasErrors('numero_whatsapp');
    }

    /** Acompte supérieur au montant total → refusé avec message clair */
    public function test_acompte_depasse_montant_refuse(): void
    {
        $directrice = $this->loginDirectrice();

        $avant = Commande::where('superviseur_id', $directrice->id)->count();

        $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 5000,
            'acompte' => 8000,
            'numero_whatsapp' => '+237690123456',
        ])->assertSessionHasErrors('acompte');
        // (aucune commande ne doit être créée)

        $this->assertStringContainsString(
            'ne peut pas dépasser le montant total',
            session('errors')->first('acompte')
        );

        $this->assertEquals($avant, Commande::where('superviseur_id', $directrice->id)->count(), 'Aucune commande ne doit être créée');
    }

    /** Montant <= 1 000 FCFA → commande enregistrée sans facture PDF */
    public function test_commande_sous_1000_sans_facture(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/commandes', [
            'type' => 'photocopie',
            'montant' => 700,
            'acompte' => 700,
            'numero_whatsapp' => '+237690123456',
        ])->assertRedirect('/directrice/commandes');

        $commande = Commande::where('superviseur_id', $directrice->id)
            ->where('montant', 700)->latest('id')->first();

        $this->assertNotNull($commande, 'Commande non créée');
        $this->assertEquals('paye', $commande->statut_paiement);
        $this->assertNull($commande->numero_facture, 'Aucune facture ne doit être générée sous 1 000 FCFA');
        $this->assertEquals(0, Facture::where('commande_id', $commande->id)->count(), 'Aucune facture PDF attendue');
    }

    /** L'ancien formulaire (sans WhatsApp) continue de fonctionner */
    public function test_ancien_formulaire_sans_whatsapp(): void
    {
        $directrice = $this->loginDirectrice();

        $this->post('/directrice/commandes', [
            'type' => 'impression',
            'montant' => 7000,
            'montant_paye' => 7000,
            'statut_paiement' => 'paye',
        ])->assertRedirect('/directrice/commandes');

        $commande = Commande::where('superviseur_id', $directrice->id)
            ->where('montant', 7000)->latest('id')->first();
        $this->assertNotNull($commande);
        $this->assertNull($commande->numero_facture);
    }
}
