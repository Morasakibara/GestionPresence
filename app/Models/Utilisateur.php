<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
            // Si ce n'est pas un hash bcrypt, alors hasher le mot de passe
            Log::info('Hachage du mot de passe', ['valueLength' => strlen($value)]);
            $this->attributes['motDePasse'] = Hash::make($value);
        } else {
            // Si c'est déjà un hash, le stocker tel quel
            Log::info('Mot de passe déjà haché, stockage direct', ['valueLength' => strlen($value)]);
            $this->attributes['motDePasse'] = $value;
        }
    }
}
