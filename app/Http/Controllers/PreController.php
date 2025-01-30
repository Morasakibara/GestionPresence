<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PreController extends Controller
{
    public function index()
    {
        // Afficher les options de marquage (arrivée et départ)
        return view('presence.index');
    }

    public function markArrival(Request $request)
{
    $now = now();
    if ($now->hour < 7 || $now->hour > 10) {
        return redirect()->back()->withErrors('Vous ne pouvez marquer l\'arrivée qu\'entre 7h et 10h.');
    }

    $user = Auth::user();
    $superviseurId = $user->Sup_id ?? null;

    Presence::create([
        'employerID' => $user->id,
        'heureArrivee' => $now,
        'date' => $now->toDateString(),
        'status' => 'en attente',
        'Sup_id' => $superviseurId
    ]);

    return redirect()->back()->with('success', 'Heure d\'arrivée marquée avec succès.');
}

    public function markDeparture(Request $request)
{
    $now = now();
    if ($now->hour < 17 || $now->hour > 18.5) {
        return redirect()->back()->withErrors('Vous ne pouvez marquer le départ qu\'entre 17h et 18h30.');
    }

    $user = Auth::user();
    $presence = Presence::where('employerID', $user->id)
                        ->whereDate('heureArrivee', $now->toDateString())
                        ->whereNull('heureDepart')
                        ->first();

    if (!$presence) {
        return redirect()->back()->withErrors('Aucune arrivée correspondante n\'a été trouvée pour aujourd\'hui ou le départ a déjà été marqué.');
    }

    $presence->update([
        'heureDepart' => $now,
        'status' => 'présent'
    ]);

    return redirect()->back()->with('success', 'Heure de départ marquée avec succès.');
}

public function handleAutoAbsences()
{
    $today = now()->toDateString();
    
    // Mise à jour des présences avec arrivée mais sans départ
    DB::table('presence')
        ->whereDate('date', $today)
        ->whereNotNull('heureArrivee')
        ->whereNull('heureDepart')
        ->update(['status' => 'Absent']);

    // Création d'enregistrements pour les employés sans aucune présence aujourd'hui
    $employesSansPresence = DB::table('utilisateur')
        ->join('employer', 'utilisateur.id', '=', 'employer.id')
        ->leftJoin('presence', function ($join) use ($today) {
            $join->on('utilisateur.id', '=', 'presence.employerID')
                 ->whereDate('presence.date', $today);
        })
        ->whereNull('presence.id')
        ->select('utilisateur.id', 'employer.Sup_id')
        ->get();

    foreach ($employesSansPresence as $employe) {
        DB::table('presence')->insert([
            'employerID' => $employe->id,
            'Sup_id' => $employe->Sup_id,
            'date' => $today,
            'status' => 'Absent',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    return "Absences traitées avec succès.";
}
}
