<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    protected $table = 'utilisateur';

    protected $fillable = [
        'nom', 'email', 'motDePasse', 'role', 'avatar',
        'pin_code', 'pin_required',
    ];

    protected $hidden = [
        'motDePasse', 'remember_token', 'pin_code',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->motDePasse;
    }

    /**
     * Set the password using the correct hashing method.
     *
     * @param string $password
     * @return void
     */
    public function setMotDePasseAttribute($value)
    {
        // Vérifier si le mot de passe est déjà hashé
        if (strlen($value) < 60 || !preg_match('/^\$2y\$/', $value)) {
            $this->attributes['motDePasse'] = Hash::make($value);
        } else {
            $this->attributes['motDePasse'] = $value;
        }
    }
}
