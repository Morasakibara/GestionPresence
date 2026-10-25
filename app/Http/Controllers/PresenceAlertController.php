<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Superviseur;
use App\Models\Employer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * API légère pour les alertes de présence en temps réel.
 * Les dashboards admin/superviseur pollent cet endpoint toutes les 10s
 * pour détecter les nouvelles arrivées et départs.
 */
class PresenceAlertController extends Controller
{
    /**
     * Retourne les nouvelles présences depuis un timestamp donné.
     * GET /api/presence-alerts?since=2026-08-25 08:00:00
     */
    public function check(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $since = request()->query('since');
        $today = now()->toDateString();

        // `heureArrivee` / `heureDepart` sont des colonnes TIME : on compare
        // uniquement l'heure (jamais un datetime complet), sinon l'API rejoue
        // toutes les alertes de la journée à chaque chargement.
        try {
            $sinceDate = $since ? \Carbon\Carbon::parse($since) : now()->subMinutes(5);
        } catch (\Throwable $e) {
            $sinceDate = now()->subMinutes(5);
        }
        $sinceTime = ($sinceDate->toDateString() === $today)
            ? $sinceDate->format('H:i:s')
            : '00:00:00';

        if ($user->role === 'Administrateur') {
            $equipeFilter = null;
        } else {
            $supInfo = Superviseur::where('id', $user->id)->first();
            $equipe = $supInfo ? $supInfo->equipe : null;
            if (!$equipe) {
                return response()->json(['arrivals' => [], 'departures' => [], 'counts' => ['present' => 0, 'absent' => 0]]);
            }
            $employerIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();
        }

        // Nouvelles arrivées depuis $sinceDate
        $arrivalsQuery = Presence::whereDate('date', $today)
            ->whereNotNull('heureArrivee')
            ->where('heureArrivee', '>', $sinceTime)
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->select(
                'presence.employerID',
                'utilisateur.nom',
                'presence.heureArrivee',
                'presence.status'
            )
            ->orderBy('presence.heureArrivee', 'desc');

        if (isset($employerIds)) {
            $arrivalsQuery->whereIn('presence.employerID', $employerIds);
        }

        $arrivals = $arrivalsQuery->get()->map(fn($a) => [
            'id' => $a->employerID,
            'nom' => $a->nom,
            'heure' => \Carbon\Carbon::parse($a->heureArrivee)->format('H:i'),
            'retard' => \Carbon\Carbon::parse($a->heureArrivee)->format('H:i') > '08:00',
        ]);

        // Nouveaux départs depuis $sinceDate
        $departuresQuery = Presence::whereDate('date', $today)
            ->whereNotNull('heureDepart')
            ->where('heureDepart', '>', $sinceTime)
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->select(
                'presence.employerID',
                'utilisateur.nom',
                'presence.heureDepart'
            )
            ->orderBy('presence.heureDepart', 'desc');

        if (isset($employerIds)) {
            $departuresQuery->whereIn('presence.employerID', $employerIds);
        }

        $departures = $departuresQuery->get()->map(fn($d) => [
            'id' => $d->employerID,
            'nom' => $d->nom,
            'heure' => \Carbon\Carbon::parse($d->heureDepart)->format('H:i'),
        ]);

        // Compteurs actuels
        $presentQuery = Presence::whereDate('date', $today)->where('status', 'présent');
        $absentQuery = DB::table('utilisateur')->where('role', 'Employer')
            ->whereNotIn('id', Presence::whereDate('date', $today)->pluck('employerID'));

        if (isset($employerIds)) {
            $presentQuery->whereIn('employerID', $employerIds);
            $absentQuery->whereIn('id', $employerIds);
        }

        return response()->json([
            'arrivals' => $arrivals,
            'departures' => $departures,
            'counts' => [
                'present' => $presentQuery->count(),
                'absent' => $absentQuery->count(),
            ],
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
