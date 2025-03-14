<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkplaceLocation;

class GeoLocationController extends Controller
{
    public function checkLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Vérifier si l'utilisateur est dans un lieu de travail valide
        $locations = WorkplaceLocation::where('actif', true)->get();

        foreach ($locations as $location) {
            if ($location->isWithinRadius($latitude, $longitude)) {
                return response()->json([
                    'valid' => true,
                    'location' => $location->nom,
                    'message' => 'Position validée pour le lieu: ' . $location->nom
                ]);
            }
        }

        return response()->json([
            'valid' => false,
            'message' => 'Vous n\'êtes pas dans un lieu de travail autorisé. Veuillez être physiquement présent sur votre lieu de travail pour marquer votre présence.'
        ]);
    }
}
