<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Presence;
use Carbon\Carbon;

class PresenceController extends Controller
{
    public function markArrival()
    {
        $now = Carbon::now();
        $user = Auth::user();

        if ($now->hour < 7 || $now->hour >= 10) {
            return redirect()->back()->with('error', 'Vous ne pouvez marquer votre arrivée qu\'entre 7h et 10h.');
        }

        $presence = Presence::firstOrCreate([
            'employerID' => $user->id,
            'date' => $now->toDateString(),
        ]);

        $presence->heureArrivee = $now;
        $presence->save();

        return redirect()->back()->with('success', 'Arrivée marquée avec succès.');
    }

    public function markDeparture()
    {
        $now = Carbon::now();
        $user = Auth::user();

        if ($now->hour < 17 || $now->hour >= 18 || ($now->hour == 18 && $now->minute > 30)) {
            return redirect()->back()->with('error', 'Vous ne pouvez marquer votre départ qu\'entre 17h et 18h30.');
        }

        $presence = Presence::where('employerID', $user->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        if (!$presence) {
            return redirect()->back()->with('error', 'Aucune arrivée n\'a été marquée aujourd\'hui.');
        }

        $presence->heureDepart = $now;
        $presence->save();

        return redirect()->back()->with('success', 'Départ marqué avec succès.');
    }
}