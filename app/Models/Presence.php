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
    ];
    protected $fillable = [
        'Sup_id',
        'employerID',
        'heureArrivee',
        'heureDepart',
        'date',
        'status'
    ];

    public function superviseur()
    {
        return $this->belongsTo(Superviseur::class, 'Sup_id');
    }
}
