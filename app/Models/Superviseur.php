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
        'id', 'equipe', 'type_superviseur',
    ];

    // L'id est hérité de la table utilisateur (assigné manuellement)
    public $incrementing = false;

    public $timestamps = false;

    public function employers()
    {
        return $this->hasMany(Employer::class, 'Sup_id');
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'superviseur_id');
    }

    public function servicesFournis()
    {
        return $this->hasMany(ServiceFourni::class, 'superviseur_id');
    }

    public function retraits()
    {
        return $this->hasMany(Retrait::class, 'superviseur_id');
    }

    public function stockTshirts()
    {
        return $this->hasMany(StockTshirt::class, 'superviseur_id');
    }

    public function stockPapier()
    {
        return $this->hasMany(StockPapier::class, 'superviseur_id');
    }

    // Constantes pour les types
    const TYPE_DIRECTRICE = 'directrice';
    const TYPE_SECRETAIRE = 'secretaire';
    const TYPE_GESTIONNAIRE_STOCK = 'gestionnaire_stock';
    const TYPE_SUPERVISEUR_A = 'superviseur_a';
    const TYPE_CLASSIQUE = null;

    public function estDirectrice(): bool
    {
        return $this->type_superviseur === self::TYPE_DIRECTRICE;
    }

    public function estSecretaire(): bool
    {
        return $this->type_superviseur === self::TYPE_SECRETAIRE;
    }

    public function estGestionnaireStock(): bool
    {
        return $this->type_superviseur === self::TYPE_GESTIONNAIRE_STOCK;
    }

    public function estSuperviseurA(): bool
    {
        return $this->type_superviseur === self::TYPE_SUPERVISEUR_A;
    }

    public function estClassique(): bool
    {
        return $this->type_superviseur === null;
    }
}
