<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Snapshot immuable d'une facture (Étape 3.1).
 *
 * phase 'acompte' : PDF généré à la validation de l'acompte.
 * phase 'solde'   : PDF final généré à l'encaissement du solde.
 * Les montants sont figés à la génération — hash SHA-256 du PDF conservé.
 */
class Facture extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'commande_id', 'numero', 'phase', 'type',
        'client_nom', 'client_whatsapp',
        'montant_total', 'montant_acompte', 'solde',
        'hash', 'pdf_path',
    ];

    protected $casts = [
        'montant_total'    => 'decimal:2',
        'montant_acompte'  => 'decimal:2',
        'solde'            => 'decimal:2',
    ];

    public const PHASE_ACOMPTE = 'acompte';
    public const PHASE_SOLDE = 'solde';

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function envois(): HasMany
    {
        return $this->hasMany(WhatsappEnvoi::class);
    }

    public function estPayee(): bool
    {
        return (float) $this->solde <= 0;
    }
}
