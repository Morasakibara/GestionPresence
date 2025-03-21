<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'utilisateur';

    protected $fillable = [
        'nom', 'email', 'motDePasse', 'role', 'avatar',
    ];

    protected $hidden = [
        'motDePasse', 'remember_token',
    ];

    public function setMotDePasseAttribute($password)
    {
        $this->attributes['motDePasse'] = Hash::make($password);
    }

    public function getAuthPassword()
    {
        return $this->motDePasse;
    }

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class, 'id');
    }

    public function superviseur()
    {
        return $this->hasOne(Superviseur::class, 'id');
    }

    public function employer()
    {
        return $this->hasOne(Employer::class, 'id');
    }
}
