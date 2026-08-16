<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Utilisateur;
use App\Notifications\RetardNotification;
use App\Notifications\AbsenceNotification;
use App\Models\WorkplaceLocation;

class PreController extends Controller
{
    public function index()
    {
        // Vérifier si nous sommes en week-end (samedi=6, dimanche=0)
        $today = Carbon::now();
        $isWeekend = $today->dayOfWeek === 0 || $today->dayOfWeek === 6;

        // Afficher les options de marquage (arrivée et départ)
        return view('presence.index', ['isWeekend' => $isWeekend]);
    }

    public function markArrival(Request $request)
{
    $now = now();

    // Vérifier si nous sommes en week-end (samedi=6, dimanche=0)
    if ($now->dayOfWeek === 0 || $now->dayOfWeek === 6) {
        return redirect()->back()->withErrors('Le marquage de présence n\'est pas disponible pendant le week-end.');
    }

    if ($now->hour < 7 || $now->hour > 10 || ($now->hour == 10 && $now->minute > 0)) {
        return redirect()->back()->withErrors('Vous ne pouvez marquer l\'arrivée qu\'entre 7h00 et 10h00.');
    }

    // Vérifier la géolocalisation
    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');

    if (!$latitude || !$longitude) {
        return redirect()->back()->withErrors('La géolocalisation est requise pour marquer votre présence.');
    }

    // Trouver un lieu de travail valide
    $validLocation = false;
    $workplaceLocationId = null;

    $workplaceLocations = WorkplaceLocation::where('actif', true)->get();

    foreach ($workplaceLocations as $location) {
        if ($location->isWithinRadius($latitude, $longitude)) {
            $validLocation = true;
            $workplaceLocationId = $location->id;
            break;
        }
    }

    if (!$validLocation) {
        return redirect()->back()->withErrors('Vous devez être physiquement présent sur votre lieu de travail pour marquer votre présence.');
    }

    $user = Auth::user();
    $superviseurId = null;
    $equipe = null;

    // Récupérer l'ID du superviseur et l'équipe de l'employé
    $employerInfo = DB::table('employer')->where('id', $user->id)->first();
    if ($employerInfo) {
        $superviseurId = $employerInfo->Sup_id;
        $equipe = $employerInfo->equipe;
    }

    // Créer l'enregistrement de présence
    $presence = Presence::create([
        'employerID' => $user->id,
        'heureArrivee' => $now,
        'date' => $now->toDateString(),
        'status' => 'en attente',
        'Sup_id' => $superviseurId,
        'latitude_arrivee' => $latitude,
        'longitude_arrivee' => $longitude,
        'localisation_validee_arrivee' => true,
        'workplace_location_id' => $workplaceLocationId
    ]);

    // Vérifier si l'employé est en retard (après 8h)
    $isRetard = $now->hour > 8 || ($now->hour == 8 && $now->minute > 0);

    if ($isRetard) {
        // Notifier le superviseur direct si disponible
        if ($superviseurId) {
            $superviseur = Utilisateur::find($superviseurId);
            if ($superviseur) {
                $superviseur->notify(new RetardNotification($user, $presence));
            }
        }

        // Notifier uniquement l'administrateur principal (au lieu de tous les admins)
        $adminPrincipal = $this->primaryAdmin();
        if ($adminPrincipal) {
            $adminPrincipal->notify(new RetardNotification($user, $presence));
        }
    }

    return redirect()->back()->with('success', 'Heure d\'arrivée marquée avec succès.');
}

public function markDeparture(Request $request)
{
    $now = now();

    // Vérifier si nous sommes en week-end (samedi=6, dimanche=0)
    if ($now->dayOfWeek === 0 || $now->dayOfWeek === 6) {
        return redirect()->back()->withErrors('Le marquage de présence n\'est pas disponible pendant le week-end.');
    }

    if ($now->hour < 17 || $now->hour > 18 || ($now->hour == 18 && $now->minute > 50)) {
        return redirect()->back()->withErrors('Vous ne pouvez marquer le départ qu\'entre 17h00 et 18h30.');
    }

    // Vérifier la géolocalisation
    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');

    if (!$latitude || !$longitude) {
        return redirect()->back()->withErrors('La géolocalisation est requise pour marquer votre départ.');
    }

    // Trouver un lieu de travail valide
    $validLocation = false;

    $workplaceLocations = WorkplaceLocation::where('actif', true)->get();

    foreach ($workplaceLocations as $location) {
        if ($location->isWithinRadius($latitude, $longitude)) {
            $validLocation = true;
            break;
        }
    }

    if (!$validLocation) {
        return redirect()->back()->withErrors('Vous devez être physiquement présent sur votre lieu de travail pour marquer votre départ.');
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
        'status' => 'présent',
        'latitude_depart' => $latitude,
        'longitude_depart' => $longitude,
        'localisation_validee_depart' => true
    ]);

    return redirect()->back()->with('success', 'Heure de départ marquée avec succès.');
}

    public function handleAutoAbsences()
    {
        $today = now()->toDateString();

        // Mise à jour des présences avec arrivée mais sans départ
        $presencesUpdated = DB::table('presence')
            ->whereDate('date', $today)
            ->whereNotNull('heureArrivee')
            ->whereNull('heureDepart')
            ->update(['status' => 'Absent']);

        \Log::info("Présences mises à jour (arrivée sans départ): {$presencesUpdated}");

        // Notifier pour les présences avec arrivée mais sans départ
        $employesArriveesSansDepart = DB::table('presence')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->leftJoin('employer', 'utilisateur.id', '=', 'employer.id')
            ->whereDate('presence.date', $today)
            ->whereNotNull('presence.heureArrivee')
            ->whereNull('presence.heureDepart')
            ->where('presence.status', 'Absent')
            ->select('utilisateur.*', 'presence.id as presence_id', 'employer.Sup_id', 'employer.equipe')
            ->get();

        foreach ($employesArriveesSansDepart as $employe) {
            // Récupérer l'utilisateur pour pouvoir utiliser le trait Notifiable
            $employeUser = Utilisateur::find($employe->id);

            // Notifier le superviseur de cet employé
            if ($employe->Sup_id) {
                $superviseur = Utilisateur::find($employe->Sup_id);
                if ($superviseur) {
                    $superviseur->notify(new AbsenceNotification($employeUser, $today));
                }
            }

            // Notifier uniquement l'administrateur principal
            $adminPrincipal = $this->primaryAdmin();
            if ($adminPrincipal) {
                $adminPrincipal->notify(new AbsenceNotification($employeUser, $today));
            }
        }

        // Création d'enregistrements pour les employés sans aucune présence aujourd'hui
        $employesSansPresence = DB::table('utilisateur')
            ->join('employer', 'utilisateur.id', '=', 'employer.id')
            ->leftJoin('presence', function ($join) use ($today) {
                $join->on('utilisateur.id', '=', 'presence.employerID')
                     ->whereDate('presence.date', $today);
            })
            ->whereNull('presence.id')
            ->select('utilisateur.id', 'utilisateur.nom', 'utilisateur.email', 'employer.Sup_id', 'employer.equipe')
            ->get();

        $absencesCreated = 0;
        foreach ($employesSansPresence as $employe) {
            DB::table('presence')->insert([
                'employerID' => $employe->id,
                'Sup_id' => $employe->Sup_id,
                'date' => $today,
                'status' => 'Absent',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $absencesCreated++;

            // Récupérer l'utilisateur pour pouvoir utiliser le trait Notifiable
            $employeUser = Utilisateur::find($employe->id);

            // Notifier le superviseur direct de cet employé uniquement
            if ($employe->Sup_id) {
                $superviseur = Utilisateur::find($employe->Sup_id);
                if ($superviseur) {
                    $superviseur->notify(new AbsenceNotification($employeUser, $today));
                }
            }

            // Notifier uniquement l'administrateur principal
            $adminPrincipal = $this->primaryAdmin();
            if ($adminPrincipal) {
                $adminPrincipal->notify(new AbsenceNotification($employeUser, $today));
            }
        }

        \Log::info("Absences créées pour employés sans présence: {$absencesCreated}");

        return "Absences traitées avec succès. Mises à jour: {$presencesUpdated}, Créées: {$absencesCreated}";
    }

    /**
     * Retourne l'administrateur principal (le plus ancien) pour les notifications.
     */
    private function primaryAdmin(): ?Utilisateur
    {
        return Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
    }
}
