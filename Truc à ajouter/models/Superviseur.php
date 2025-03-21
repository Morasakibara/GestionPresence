<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Superviseur extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'superviseur';

    protected $fillable = [
        'id', 'equipe',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'id');
    }

    public function employers()
    {
        return $this->hasMany(Employer::class, 'Sup_id');
    }

    public function presences()
    {
        return $this->hasMany(Presence::class, 'Sup_id');
    }

    public function rapports()
    {
        return $this->hasMany(Rapport::class, 'Sup_id');
    }
}
