<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retrait extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'superviseur_id',
        'montant',
        'motif',
        'date',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date' => 'date',
    ];

    public function superviseur()
    {
        return $this->belongsTo(Superviseur::class, 'superviseur_id');
    }
}
