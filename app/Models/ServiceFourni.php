<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Service fourni (directrice / secrétaire) — même flux de facturation
 * que les commandes (Étape 2) : acompte initial, verrou du prix,
 * encaissement du solde exact, facture PDF + WhatsApp.
 */
class ServiceFourni extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'services_fournis';

    protected $fillable = [
        'superviseur_id',
        'type',
        'montant',
        'montant_paye',
        'statut_paiement',
        'numero_facture',
        'numero_whatsapp',
        'client_nom',
        'prix_verrouille',
        'details',
        'date',
    ];

    /** Statut du flux facturation (Étapes 2.1 / 2.2). */
    public const STATUT_EN_ATTENTE_SOLDE = 'en_attente_solde';

    protected $casts = [
        'montant'        => 'decimal:2',
        'montant_paye'   => 'decimal:2',
        'prix_verrouille' => 'boolean',
        'date'           => 'date',
    ];

    public function superviseur()
    {
        return $this->belongsTo(Superviseur::class, 'superviseur_id');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    /* ──────── Aides du flux facturation (miroir de Commande) ──────── */

    /** Montant restant à payer. */
    public function getSoldeAttribute(): float
    {
        return max(0.0, (float) $this->montant - (float) $this->montant_paye);
    }

    public function estPayee(): bool
    {
        return $this->statut_paiement === 'paye';
    }

    public function estAnnulee(): bool
    {
        return $this->statut_paiement === 'annule';
    }

    /** Le montant est verrouillé dès qu'un premier versement existe. */
    public function estVerrouillee(): bool
    {
        return (bool) $this->prix_verrouille || (float) $this->montant_paye > 0;
    }

    /** Le service attend l'encaissement du solde. */
    public function estEnAttenteSolde(): bool
    {
        return in_array($this->statut_paiement, [self::STATUT_EN_ATTENTE_SOLDE, 'partiel'], true);
    }
}
