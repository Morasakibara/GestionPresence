<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trace d'un envoi WhatsApp (Étape 4.2).
 *
 * statut 'en_attente' → job mis en file
 * statut 'envoye'     → envoyé par la passerelle
 * statut 'echec'      → l'alerte s'affiche à l'écran pour le caissier
 */
class WhatsappEnvoi extends Model
{
    protected $fillable = [
        'facture_id', 'commande_id', 'service_id', 'superviseur_id',
        'telephone', 'canal', 'statut',
        'message_id', 'erreur', 'tentative',
    ];

    public const STATUT_EN_ATTENTE = 'en_attente';
    public const STATUT_ENVOYE = 'envoye';
    public const STATUT_ECHEC = 'echec';

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ServiceFourni::class);
    }
}
