<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrateur extends Model
{
    use HasFactory;

    protected $table = 'administrateur'; // Nom de la table existante

    // L'id est hérité de la table utilisateur (assigné manuellement)
    public $incrementing = false;

    protected $fillable = [
        'id', // Assume que `user_id` est une clé étrangère
        'poste',
        // Ajoute d'autres champs si nécessaire
    ];
}
