<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conge extends Model
{
    protected $table = 'conges';

    protected $fillable = [
        'employer_id',
        'date_debut',
        'date_fin',
        'type',
        'motif',
        'statut',
        'commentaire_admin',
        'traite_par',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'employer_id');
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'traite_par');
    }

    public function superviseur(): BelongsTo
    {
        return $this->belongsTo(Superviseur::class, 'employer_id', 'id');
    }

    /**
     * Nombre de jours ouvrés entre date_debut et date_fin.
     */
    public function dureeJours(): int
    {
        $debut = $this->date_debut instanceof \Carbon\Carbon ? $this->date_debut : \Carbon\Carbon::parse($this->date_debut);
        $fin = $this->date_fin instanceof \Carbon\Carbon ? $this->date_fin : \Carbon\Carbon::parse($this->date_fin);
        return (int) $debut->diffInDays($fin) + 1;
    }

    /**
     * Badge HTML selon le statut.
     */
    public function statutBadge(): string
    {
        return match ($this->statut) {
            'approuve' => '<span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">✅ Approuvé</span>',
            'refuse' => '<span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">❌ Refusé</span>',
            default => '<span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">⏳ En attente</span>',
        };
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'conge' => '🏖️ Congé',
            'absence_justifiee' => '📋 Absence justifiée',
            'absence_injustifiee' => '⚠️ Absence injustifiée',
            default => $this->type,
        };
    }
}
