<?php

namespace App\Services\WhatsApp;

/**
 * Étape 4.1 — Choix et instanciation du driver de passerelle.
 *
 * config('services.whatsapp.driver') :
 *   'meta' → MetaCloudApiSender (API officielle)
 *   'log'  → LogSender (simulation dev, défaut)
 */
class WhatsAppDI
{
    public static function sender(): WhatsAppSender
    {
        return match (config('services.whatsapp.driver', 'log')) {
            'meta' => new MetaCloudApiSender(),
            default => new LogSender(),
        };
    }
}
