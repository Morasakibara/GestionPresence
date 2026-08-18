<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTshirt extends Model
{
    use HasFactory;

    protected $fillable = [
        'superviseur_id',
        'couleur',
        'taille',
        'quantite',
        'seuil_alerte',
    ];

    protected $casts = [
        'quantite' => 'integer',
        'seuil_alerte' => 'integer',
    ];

    public function superviseur()
    {
        return $this->belongsTo(Superviseur::class, 'superviseur_id');
    }

    public function enAlerte(): bool
    {
        return $this->quantite <= $this->seuil_alerte;
    }
}
