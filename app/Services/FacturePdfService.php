<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Facture;
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
        $montantTotal   = (float) $commande->montant;
        $montantAcompte = min((float) $commande->montant_paye, $montantTotal);
        $solde          = max(0.0, $montantTotal - $montantAcompte);

        $data = [
            'facture' => [
                'numero'          => $numero,
                'phase'           => $phase,
                'type'            => $commande->type,
                'client_nom'      => $commande->client_nom,
                'client_whatsapp' => $commande->numero_whatsapp,
                'montant_total'   => $montantTotal,
                'montant_acompte' => $montantAcompte,
                'solde'           => $solde,
                'details'         => $commande->details,
                'date'            => now(),
            ],
            'entreprise' => [
                'nom'     => config('facture.societe.nom', 'LE PHARAON'),
                'adresse' => config('facture.societe.adresse'),
                'phone'   => config('facture.societe.phone'),
                'devise'  => config('facture.devise', 'FCFA'),
            ],
            'logoBase64' => self::logoBase64(),
        ];

        $html = view('pdf.facture', $data)->render();

        $pdf = Pdf::loadHtml($html)
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

        return Facture::create([
            'commande_id'      => $commande->id,
            'numero'           => $numero,
            'phase'            => $phase,
            'type'             => $commande->type,
            'client_nom'       => $commande->client_nom,
            'client_whatsapp'  => $commande->numero_whatsapp,
            'montant_total'    => $montantTotal,
            'montant_acompte'  => $montantAcompte,
            'solde'            => $solde,
            'hash'             => $hash,
            'pdf_path'         => "factures/{$nomFichier}",
        ]);
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
