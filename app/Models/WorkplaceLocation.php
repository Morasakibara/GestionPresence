<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkplaceLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'latitude',
        'longitude',
        'rayon',
        'actif'
    ];

    /**
     * Vérifie si les coordonnées données sont dans le rayon de ce lieu de travail
     */
    public function isWithinRadius($latitude, $longitude)
    {
        // Distance en mètres entre deux points géographiques (formule de Haversine)
        $earthRadius = 6371000; // Rayon de la Terre en mètres

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        $distance = $angle * $earthRadius;

        return $distance <= $this->rayon;
    }
}
