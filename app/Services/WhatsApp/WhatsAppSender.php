<?php

namespace App\Services\WhatsApp;

/**
 * Étape 4.1 — Contrat de la passerelle WhatsApp.
 *
 * Un sender envoie un PDF de facture vers un numéro et retourne un résultat
 * normalisé. Chaque implémentation est un driver configurable
 * (config('services.whatsapp.driver')). Les exceptions ne doivent pas être
 * levées : le résultat échec est renvoyé afin que l'ERP trace l'alerte.
 */
interface WhatsAppSender
{
    /**
     * Envoie le document PDF sur le numéro donné.
     *
     * @param  string  $telephone  numéro E.164 final, ex: 2376XXX... (sans « + »)
     * @param  string  $cheminPdf  chemin absolu du PDF figé
     * @param  string  $nomFichier nom du document côté WhatsApp
     * @param  string  $caption    légende du message
     * @return array{ok: bool, message_id?: string, erreur?: string}
     */
    public function sendDocument(string $telephone, string $cheminPdf, string $nomFichier, string $caption): array;

    /** Nom du driver ('meta', 'log', ...). */
    public function canal(): string;
}
