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
        'motDePasse', 'remember_token', 'pin_code', 'session_token',
    ];

    /**
     * Session unique : émet un jeton aléatoire, n'en stocke que l'empreinte SHA-256
     * et place le jeton en clair dans la session courante. La connexion précédente
     * (autre appareil) porte désormais une empreinte différente → invalidée.
     *
     * @return string le jeton brut à placer en session
     */
    public function emettreJetonSession(): string
    {
        $jeton = (string) \Illuminate\Support\Str::uuid();
        $this->forceFill(['session_token' => hash('sha256', $jeton)])->save();
        session(['session_token' => $jeton]);

        return $jeton;
    }

    /**
     * La session courante porte-t-elle le jeton actuellement actif en base ?
     */
    public function jetonSessionValide(?string $jetonBrut): bool
    {
        if (!$jetonBrut || !$this->session_token) {
            return false;
        }

        return hash_equals((string) $this->session_token, hash('sha256', $jetonBrut));
    }

    /**
     * Super administrateur (développeur) : accès aux métriques système.
     */
    public function isSuperAdmin(): bool
    {
        if ($this->role !== 'Administrateur') {
            return false;
        }

        $emails = array_map('strtolower', (array) config('app.super_admins', []));

        return in_array(strtolower((string) $this->email), $emails, true);
    }

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
