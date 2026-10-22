<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;

/**
 * Étape 4.1 — Driver de simulation (mode développement).
 *
 * Utilisé tant que les clés Meta ne sont pas configurées (WHATSAPP_DRIVER=log) :
 * l'envoi est simulé (journalisé) afin que tout le flux reste testable
 * sans compte Meta Business.
 */
class LogSender implements WhatsAppSender
{
    public function canal(): string
    {
        return 'log';
    }

    public function sendDocument(string $telephone, string $cheminPdf, string $nomFichier, string $caption): array
    {
        Log::info("[WhatsApp-FACTURE] envoi simulé → +{$telephone} ({$nomFichier}) : {$caption}");

        return ['ok' => true, 'message_id' => 'simule-' . uniqid()];
    }
}
