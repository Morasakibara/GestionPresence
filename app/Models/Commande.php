<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commande extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'superviseur_id',
        'type',
        'montant',
        'montant_paye',
        'statut_paiement',
        'details',
        'date',
        'numero_whatsapp',
        'client_nom',
        'numero_facture',
        'prix_verrouille',
    ];

    /** Statuts du flux facturation (Étapes 2.1 / 2.2). */
    public const STATUT_EN_ATTENTE_SOLDE = 'en_attente_solde';

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Montant restant à payer.
     */
    public function getResteAttribute(): float
    {
        return (float) $this->montant - (float) $this->montant_paye;
    }

    /**
     * Vérifie si la commande est entièrement payée.
     */
    public function estPayee(): bool
    {
        return $this->statut_paiement === 'paye';
    }

    /**
     * Vérifie si la commande a un paiement partiel.
     */
    public function estPartielle(): bool
    {
        return $this->statut_paiement === 'partiel';
    }

    /**
     * Vérifie si la commande doit être payée plus tard.
     */
    public function estAPayer(): bool
    {
        return $this->statut_paiement === 'a_payer';
    }

    public function estAnnulee(): bool
    {
        return $this->statut_paiement === 'annule';
    }

    /**
     * Montant restant à payer (solde).
     */
    public function getSoldeAttribute(): float
    {
        return max(0.0, (float) $this->montant - (float) $this->montant_paye);
    }

    /**
     * Le montant du prix est verrouillé (Étape 2.2 — pas de modification entre
     * l'acompte et le solde). Dès qu'un premier versement existe, seuls le
     * mot de passe administrateur (ManagerAuthService) peut déverrouiller.
     */
    public function estVerrouillee(): bool
    {
        return (bool) $this->prix_verrouille || (float) $this->montant_paye > 0;
    }

    /** La commande est-elle en attente de solde (acompte versé) ? */
    public function estEnAttenteSolde(): bool
    {
        return in_array($this->statut_paiement, [self::STATUT_EN_ATTENTE_SOLDE, 'partiel'], true);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function superviseur()
    {
        return $this->belongsTo(Superviseur::class, 'superviseur_id');
    }
}
