<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasFactory;

    protected $table = 'presence';
    protected $casts = [
        'heureArrivee' => 'datetime',
        'heureDepart' => 'datetime',
        'localisation_validee_arrivee' => 'boolean',
        'localisation_validee_depart' => 'boolean',
    ];
    protected $fillable = [
        'Sup_id',
        'employerID',
        'heureArrivee',
        'heureDepart',
        'date',
        'status',
        'latitude_arrivee',
        'longitude_arrivee',
        'latitude_depart',
        'longitude_depart',
        'localisation_validee_arrivee',
        'localisation_validee_depart',
        'workplace_location_id',
        'rendement'
    ];

    public function superviseur()
    {
        return $this->belongsTo(Superviseur::class, 'Sup_id');
    }

    public function workplaceLocation()
    {
        return $this->belongsTo(WorkplaceLocation::class, 'workplace_location_id');
    }
}
