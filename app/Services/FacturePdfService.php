<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Facture;
use App\Models\ServiceFourni;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Étape 3.1 — Génération du PDF sécurisé.
 *
 * - Modèle visuel : logo, nom de l'entreprise, date, numéro séquentiel unique.
 * - Mentions dynamiques :
 *     solde > 0 → "ACOMPTE REÇU — SOLDE À PAYER : X"
 *     solde = 0 → "FACTURE PAYÉE — SOLDE : 0"
 * - Verrou : le PDF est écrit une fois, son empreinte SHA-256 est conservée
 *   dans la table `factures` ; il est ensuite servi en lecture seule.
 */
class FacturePdfService
{
    /** Dossier des PDF figés (disk local = storage/app). */
    public const DISK = 'factures';

    public static function disque(): string
    {
        return self::DISK;
    }

    /**
     * Génère (fige) le PDF d'une facture à partir des données de la commande.
     */
    public static function figer(Commande $commande, string $numero, string $phase): Facture
    {
        return self::genererFacture([
            'commande_id'     => $commande->id,
            'type'            => $commande->type,
            'client_nom'      => $commande->client_nom,
            'client_whatsapp' => $commande->numero_whatsapp,
            'montant'         => $commande->montant,
            'montant_paye'    => $commande->montant_paye,
            'details'         => $commande->details,
        ], $numero, $phase);
    }

    /**
     * Génère (fige) le PDF d'une facture d'un service fourni (Étape 2 services).
     */
    public static function figerService(ServiceFourni $service, string $numero, string $phase): Facture
    {
        return self::genererFacture([
            'service_id'      => $service->id,
            'type'            => $service->type,
            'client_nom'      => $service->client_nom,
            'client_whatsapp' => $service->numero_whatsapp,
            'montant'         => $service->montant,
            'montant_paye'    => $service->montant_paye,
            'details'         => $service->details,
        ], $numero, $phase);
    }

    /**
     * Cœur commun : rend le modèle visuel, calcule acompte/solde, écrit le PDF
     * et crée le snapshot immuable (hash SHA-256).
     *
     * @param array{commande_id?: int, service_id?: int, type: string, client_nom?: ?string,
     *     client_whatsapp: string, montant: float|string, montant_paye: float|string,
     *     details?: ?string} $titulaire
     */
    private static function genererFacture(array $titulaire, string $numero, string $phase): Facture
    {
        $montantTotal   = (float) $titulaire['montant'];
        $montantAcompte = min((float) $titulaire['montant_paye'], $montantTotal);
        $solde          = max(0.0, $montantTotal - $montantAcompte);

        $factureDonnees = [
            'numero'          => $numero,
            'phase'           => $phase,
            'type'            => $titulaire['type'],
            'client_nom'      => $titulaire['client_nom'],
            'client_whatsapp' => $titulaire['client_whatsapp'],
            'montant_total'   => $montantTotal,
            'montant_acompte' => $montantAcompte,
            'solde'           => $solde,
            'details'         => $titulaire['details'],
            'date'            => now(),
        ];

        $data = [
            'facture'    => $factureDonnees,
            'entreprise' => self::donneesEntreprise(),
            'logoBase64' => self::logoBase64(),
        ];

        $pdf = Pdf::loadHtml(view('pdf.facture', $data))
            ->setPaper('a5', 'landscape')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'sans-serif',
                'dpi'                  => 150,
            ]);

        $contenu = $pdf->output();
        $hash    = hash('sha256', $contenu);

        $nomFichier = "{$numero}_" . ($phase === Facture::PHASE_SOLDE ? 'SOLDE' : 'ACOMPTE') . ".pdf";
        Storage::disk('local')->put("factures/{$nomFichier}", $contenu);

        return Facture::create(array_merge($factureDonnees, [
            'commande_id' => $titulaire['commande_id'] ?? null,
            'service_id'  => $titulaire['service_id'] ?? null,
            'hash'        => $hash,
            'pdf_path'    => "factures/{$nomFichier}",
        ]));
    }

    /** Bloc « entreprise » commun au modèle visuel du PDF. */
    private static function donneesEntreprise(): array
    {
        return [
            'nom'     => config('facture.societe.nom', 'LE PHARAON'),
            'adresse' => config('facture.societe.adresse'),
            'phone'   => config('facture.societe.phone'),
            'devise'  => config('facture.devise', 'FCFA'),
        ];
    }

    /**
     * Vérifie que le PDF stocké n'a pas été falsifié (comparaison du hash).
     */
    public static function integrity(Facture $facture): bool
    {
        if (!Storage::disk('local')->exists($facture->pdf_path)) {
            return false;
        }

        $actuel = hash('sha256', Storage::disk('local')->get($facture->pdf_path));

        return hash_equals($facture->hash, $actuel);
    }

    private static function logoBase64(): ?string
    {
        $cheminLogo = public_path('storage/avatars/logo-pharaon.png');

        return file_exists($cheminLogo)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($cheminLogo))
            : null;
    }
}
