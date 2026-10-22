<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Étape 4.1 — Driver Meta WhatsApp Cloud API (API officielle).
 *
 * Deux requêtes POST envoyées directement par le serveur de l'ERP :
 *  1. upload du PDF figé (multipart) sur le numéro d'entreprise → media_id
 *  2. envoi du message de type document avec ce media_id
 *
 * Aucune action manuelle de l'utilisateur n'est nécessaire.
 * Variables d'env : WHATSAPP_DRIVER=meta, WHATSAPP_TOKEN, WHATSAPP_PHONE_NUMBER_ID.
 */
class MetaCloudApiSender implements WhatsAppSender
{
    public function canal(): string
    {
        return 'meta';
    }

    public function sendDocument(string $telephone, string $cheminPdf, string $nomFichier, string $caption): array
    {
        $userNameId = config('services.whatsapp.meta.phone_number_id');
        $token      = config('services.whatsapp.meta.token');
        $version    = config('services.whatsapp.meta.version', 'v21.0');

        if (!$userNameId || !$token) {
            return ['ok' => false, 'erreur' => "WhatsApp: WHATSAPP_PHONE_NUMBER_ID / WHATSAPP_TOKEN manquants."];
        }

        $base = "https://graph.facebook.com/{$version}/{$userNameId}";

        try {
            // 1. Upload du PDF
            $reponseUpload = Http::asMultipart()
                ->withToken($token)
                ->attach('file', fopen($cheminPdf, 'r'), $nomFichier)
                ->post("{$base}/media", [
                    ['name' => 'messaging_product', 'contents' => 'whatsapp'],
                    ['name' => 'type', 'contents' => 'application/pdf'],
                ]);

            $mediaId = $reponseUpload->json('id') ?? null;

            if ($reponseUpload->failed() || !$mediaId) {
                return ['ok' => false, 'erreur' => $this->erreurApi('upload du PDF', $reponseUpload)];
            }

            // 2. Envoi du message document
            $reponse = Http::withToken($token)
                ->timeout(20)
                ->post("{$base}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $telephone,
                    'type'              => 'document',
                    'document'          => [
                        'id'       => $mediaId,
                        'filename' => $nomFichier,
                        'caption'  => $caption,
                    ],
                ]);

            if ($reponse->failed()) {
                return ['ok' => false, 'erreur' => $this->erreurApi('envoi du message', $reponse)];
            }

            return [
                'ok'         => true,
                'message_id' => $reponse->json('messages.0.id') ?? $mediaId,
            ];
        } catch (\Throwable $e) {
            Log::warning('Envoi WhatsApp (meta) en échec: ' . $e->getMessage());

            return ['ok' => false, 'erreur' => 'Réseau indisponible : ' . $e->getMessage()];
        }
    }

    private function erreurApi(string $etape, $reponse): string
    {
        $erreur = $reponse->json('error.message') ?? $reponse->body();

        return "WhatsApp ({$etape}) : HTTP {$reponse->status()} — " . mb_substr(
            is_string($erreur) ? $erreur : json_encode($erreur),
            0,
            300
        );
    }
}
