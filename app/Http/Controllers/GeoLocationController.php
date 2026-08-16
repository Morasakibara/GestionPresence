<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkplaceLocation;
use App\Services\GeolocationVerificationService;

class GeoLocationController extends Controller
{
    protected $geoService;

    public function __construct(GeolocationVerificationService $geoService)
    {
        $this->geoService = $geoService;
    }

    public function checkLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $latitude = (float) $request->input('latitude');
        $longitude = (float) $request->input('longitude');

        // Vérifier si l'utilisateur est dans un lieu de travail valide
        $locations = WorkplaceLocation::where('actif', true)->get();

        foreach ($locations as $location) {
            if ($location->isWithinRadius($latitude, $longitude)) {
                // Position validée : délivrer une signature à usage unique
                $signature = $this->geoService->createSignature($latitude, $longitude);

                return response()->json([
                    'valid' => true,
                    'location' => $location->nom,
                    'message' => 'Position validée pour le lieu: ' . $location->nom,
                    'signature' => $signature,
                    'server_timestamp' => now()->timestamp,
                ]);
            }
        }

        return response()->json([
            'valid' => false,
            'message' => 'Vous n\'êtes pas dans un lieu de travail autorisé. Veuillez être physiquement présent sur votre lieu de travail pour marquer votre présence.'
        ]);
    }
}
