<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPapier extends Model
{
    use HasFactory;

    protected $table = 'stock_papier';

    protected $fillable = [
        'superviseur_id',
        'imprimante',
        'metres_restants',
        'metres_total',
        'seuil_alerte',
    ];

    protected $casts = [
        'metres_restants' => 'decimal:2',
        'metres_total' => 'decimal:2',
        'seuil_alerte' => 'integer',
    ];

    public function superviseur()
    {
        return $this->belongsTo(Superviseur::class, 'superviseur_id');
    }

    public function enAlerte(): bool
    {
        return $this->metres_restants <= $this->seuil_alerte;
    }

    public function pourcentageRestant(): float
    {
        return $this->metres_total > 0
            ? round(($this->metres_restants / $this->metres_total) * 100, 1)
            : 0;
    }
}
