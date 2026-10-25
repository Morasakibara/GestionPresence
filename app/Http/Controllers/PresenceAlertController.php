<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Presence;
use App\Models\Superviseur;
use Carbon\Carbon;
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
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $since = request()->query('since');
        $today = now()->toDateString();

        // `heureArrivee` / `heureDepart` sont des colonnes DATETIME (pas TIME).
        // Les comparer à une chaîne « H:i:s » donnait une comparaison
        // lexicographique (« 2026-10-25 … » > « 13:12:07 ») : toutes les alertes
        // du jour étaient donc rejouées à chaque chargement de page, avec le son.
        try {
            $sinceDate = $since ? Carbon::parse($since) : now()->subMinutes(5);
        } catch (\Throwable $e) {
            $sinceDate = now()->subMinutes(5);
        }

        // Fenêtre bornée : jamais avant le début de la journée locale, jamais
        // dans le futur (une horloge de navigateur en avance ne doit rien sauter).
        $now = now();
        if ($sinceDate->lt($now->copy()->startOfDay())) {
            $sinceDate = $now->copy()->startOfDay();
        }
        if ($sinceDate->gt($now)) {
            $sinceDate = $now->copy();
        }

        if ($user->role === 'Administrateur') {
            $equipeFilter = null;
        } else {
            $supInfo = Superviseur::where('id', $user->id)->first();
            $equipe = $supInfo ? $supInfo->equipe : null;
            if (! $equipe) {
                return response()->json(['arrivals' => [], 'departures' => [], 'counts' => ['present' => 0, 'absent' => 0]]);
            }
            $employerIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();
        }

        // Nouvelles arrivées depuis $sinceDate
        $arrivalsQuery = Presence::whereDate('date', $today)
            ->whereNotNull('heureArrivee')
            ->where('heureArrivee', '>', $sinceDate)
            ->where('heureArrivee', '<=', $now)
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

        $arrivals = $arrivalsQuery->get()->map(fn ($a) => [
            'id' => $a->employerID,
            'nom' => $a->nom,
            'heure' => Carbon::parse($a->heureArrivee)->format('H:i'),
            'retard' => Carbon::parse($a->heureArrivee)->format('H:i') > '08:00',
        ]);

        // Nouveaux départs depuis $sinceDate
        $departuresQuery = Presence::whereDate('date', $today)
            ->whereNotNull('heureDepart')
            ->where('heureDepart', '>', $sinceDate)
            ->where('heureDepart', '<=', $now)
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

        $departures = $departuresQuery->get()->map(fn ($d) => [
            'id' => $d->employerID,
            'nom' => $d->nom,
            'heure' => Carbon::parse($d->heureDepart)->format('H:i'),
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
            'timestamp' => $now->toDateTimeString(),
        ]);
    }
}
