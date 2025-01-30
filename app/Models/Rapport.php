<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    use HasFactory;

    protected $table = 'rapport';

    protected $fillable = [
        'Adm_id',
        'Sup_id',
        'periode',
        'contenu',
    ];

    public function administrateur()
    {
        return $this->belongsTo(Administrateur::class,'Adm_id');
    }

    public function superviseur()
    {
        return $this->belongsTo(Superviseur::class,'Sup_id');
    }
}
