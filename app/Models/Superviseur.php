<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Superviseur extends Model
{
    use HasFactory;
    use Notifiable;
    protected $table = 'Superviseur';

    protected $fillable = [
        'id', 'equipe',
    ];

    public $timestamps = false;

    public function employers()
    {
        return $this->hasMany(Employer::class, 'Sup_id');
    }
}
