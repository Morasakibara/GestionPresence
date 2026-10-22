<?php

namespace App\Jobs;

use App\Models\Facture;
use App\Models\WhatsappEnvoi;
use App\Services\FacturePdfService;
use App\Services\FactureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Étape 4.2 — Déclencheur : envoyer le PDF via la passerelle.
 *
 * Le job est mis en file dès la confirmation du paiement (dispatch, pas d'action
 * manuelle à l'écran). Tout échec est tracé dans `whatsapp_envois` (statut 'echec')
 * afin d'afficher une alerte visible pour le caissier, avec re-correctif possible.
 */
class EnvoyerFactureWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public array $backoff = [30, 120];

    public function __construct(public readonly int $factureId) {}

    public function handle(): void
    {
        $facture = Facture::find($this->factureId);
        if (!$facture) {
            return;
        }

        // Le dernier envoi en attente pour cette facture est celui en cours
        $envoi = WhatsappEnvoi::where('facture_id', $facture->id)
            ->where('statut', WhatsappEnvoi::STATUT_EN_ATTENTE)
            ->latest()
            ->first();

        if (!$envoi) {
            return;
        }

        try {
            $manager = \App\Services\WhatsApp\WhatsAppDI::sender();

            $cheminPdf = Storage::disk('local')->path($facture->pdf_path);
            if (!is_file($cheminPdf)) {
                throw new \RuntimeException('PDF de facture introuvable sur le serveur.');
            }

            $nomFichier = "Facture_{$facture->numero}_" . ($facture->phase === Facture::PHASE_SOLDE ? 'SOLDE' : 'ACOMPTE') . '.pdf';
            $caption = $facture->estPayee()
                ? "Facture {$facture->numero} — LE PHARAON : payée intégralement, merci de votre confiance."
                : "Facture {$facture->numero} — LE PHARAON : acompte reçu, solde restant " .
                    number_format((float) $facture->solde, 0, ',', ' ') . ' FCFA.';

            $resultat = $manager->sendDocument(
                FactureService::telephonePasserelle($envoi->telephone),
                $cheminPdf,
                $nomFichier,
                $caption
            );

            $envoi->update([
                'canal'      => $manager->canal(),
                'statut'     => $resultat['ok'] ? WhatsappEnvoi::STATUT_ENVOYE : WhatsappEnvoi::STATUT_ECHEC,
                'message_id' => $resultat['message_id'] ?? null,
                'erreur'     => $resultat['erreur'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $envoi->update([
                'statut'     => WhatsappEnvoi::STATUT_ECHEC,
                'erreur'     => mb_substr($e->getMessage(), 0, 500),
            ]);
        }
    }
}
