<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceFourni extends Model
{
    use HasFactory;

    protected $table = 'services_fournis';

    protected $fillable = [
        'superviseur_id',
        'type',
        'montant',
        'details',
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
