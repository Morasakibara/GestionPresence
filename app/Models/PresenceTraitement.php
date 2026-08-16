<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresenceTraitement extends Model
{
    use HasFactory;

    protected $table = 'presence_traitements';

    protected $fillable = [
        'presence_id',
        'statut_avant',
        'statut_apres',
        'commentaire',
        'traite_par',
    ];

    public function presence()
    {
        return $this->belongsTo(Presence::class, 'presence_id');
    }
}
