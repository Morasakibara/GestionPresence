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
    ];

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

    public function superviseur()
    {
        return $this->belongsTo(Superviseur::class, 'superviseur_id');
    }
}
