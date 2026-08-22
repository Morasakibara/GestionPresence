<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\Utilisateur;
use App\Models\WorkplaceLocation;
use App\Notifications\RetardNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresenceController extends Controller
{
    /**
     * POST /api/mark-arrival — marquer l'arrivée (mobile).
     */
    public function markArrival(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $now = now();

        // Vérifier la géolocalisation
        $validLocation = false;
        $workplaceLocationId = null;

        $workplaceLocations = WorkplaceLocation::where('actif', true)->get();
        foreach ($workplaceLocations as $location) {
            if ($location->isWithinRadius($request->latitude, $request->longitude)) {
                $validLocation = true;
                $workplaceLocationId = $location->id;
                break;
            }
        }

        if (!$validLocation) {
            return response()->json([
                'message' => 'Vous devez être physiquement présent sur votre lieu de travail.',
            ], 422);
        }

        $user = $request->user();
        $employerInfo = DB::table('employer')->where('id', $user->id)->first();

        $presence = Presence::create([
            'employerID' => $user->id,
            'heureArrivee' => $now,
            'date' => $now->toDateString(),
            'status' => 'en attente',
            'Sup_id' => $employerInfo->Sup_id ?? null,
            'latitude_arrivee' => $request->latitude,
            'longitude_arrivee' => $request->longitude,
            'localisation_validee_arrivee' => true,
            'workplace_location_id' => $workplaceLocationId,
        ]);

        // Vérifier retard
        $isRetard = $now->hour > 8 || ($now->hour == 8 && $now->minute > 0);
        if ($isRetard && $employerInfo && $employerInfo->Sup_id) {
            $superviseur = Utilisateur::find($employerInfo->Sup_id);
            if ($superviseur) {
                $superviseur->notify(new RetardNotification($user, $presence));
            }
            $adminPrincipal = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
            if ($adminPrincipal) {
                $adminPrincipal->notify(new RetardNotification($user, $presence));
            }
        }

        return response()->json([
            'message' => "Heure d'arrivée marquée avec succès.",
            'presence' => [
                'id' => $presence->id,
                'date' => $presence->date,
                'heure_arrivee' => $presence->heureArrivee,
                'status' => $presence->status,
            ],
        ]);
    }

    /**
     * POST /api/mark-departure — marquer le départ avec fiche de rendement (mobile).
     */
    public function markDeparture(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'rendement' => 'required|string|max:5000',
        ]);

        $now = now();

        // Vérifier la géolocalisation
        $validLocation = false;
        foreach (WorkplaceLocation::where('actif', true)->get() as $location) {
            if ($location->isWithinRadius($request->latitude, $request->longitude)) {
                $validLocation = true;
                break;
            }
        }

        if (!$validLocation) {
            return response()->json([
                'message' => 'Vous devez être physiquement présent sur votre lieu de travail.',
            ], 422);
        }

        $user = $request->user();
        $presence = Presence::where('employerID', $user->id)
            ->whereDate('heureArrivee', $now->toDateString())
            ->whereNull('heureDepart')
            ->first();

        if (!$presence) {
            return response()->json([
                'message' => "Aucune arrivée correspondante trouvée pour aujourd'hui ou le départ a déjà été marqué.",
            ], 404);
        }

        $presence->update([
            'heureDepart' => $now,
            'status' => 'présent',
            'latitude_depart' => $request->latitude,
            'longitude_depart' => $request->longitude,
            'localisation_validee_depart' => true,
            'rendement' => $request->rendement,
        ]);

        return response()->json([
            'message' => 'Heure de départ marquée avec succès.',
            'presence' => [
                'id' => $presence->id,
                'date' => $presence->date,
                'heure_arrivee' => $presence->heureArrivee,
                'heure_depart' => $presence->heureDepart,
                'status' => $presence->status,
                'rendement' => $presence->rendement,
            ],
        ]);
    }

    /**
     * GET /api/presences — historique des présences de l'utilisateur.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $presences = Presence::where('employerID', $user->id)
            ->orderByDesc('date')
            ->limit(50)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'date' => $p->date,
                'heure_arrivee' => $p->heureArrivee,
                'heure_depart' => $p->heureDepart,
                'status' => $p->status,
                'rendement' => $p->rendement,
                'duree' => $p->heureArrivee && $p->heureDepart
                    ? Carbon::parse($p->heureArrivee)->diffInMinutes(Carbon::parse($p->heureDepart))
                    : null,
            ]);

        return response()->json(['presences' => $presences]);
    }

    /**
     * GET /api/presences/today — présence du jour.
     */
    public function today(Request $request): JsonResponse
    {
        $user = $request->user();

        $presence = Presence::where('employerID', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        return response()->json([
            'presence' => $presence ? [
                'id' => $presence->id,
                'date' => $presence->date,
                'heure_arrivee' => $presence->heureArrivee,
                'heure_depart' => $presence->heureDepart,
                'status' => $presence->status,
                'rendement' => $presence->rendement,
            ] : null,
        ]);
    }
}
