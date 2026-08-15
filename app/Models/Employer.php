<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Superviseur;
use App\Models\Utilisateur;

class Employer extends Model
{
    use HasFactory;

    protected $table = 'Employer';

    protected $fillable = [
        'id', 'Sup_id', 'poste','equipe',
    ];

    // L'id est hérité de la table utilisateur (assigné manuellement)
    public $incrementing = false;

    public $timestamps = false;

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id');
    }

    public function superviseur()
    {
        return $this->belongsTo(Superviseur::class, 'Sup_id');
    }
}
