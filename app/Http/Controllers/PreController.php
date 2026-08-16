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
use App\Services\GeolocationVerificationService;

class PreController extends Controller
{
    protected $geoService;

    public function __construct(GeolocationVerificationService $geoService)
    {
        $this->geoService = $geoService;
    }

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

    // Blocage des récidivistes : trop de présences suspectes non justifiées
    $blocageMax = (int) config('geolocation.blocage_suspects_max', 3);
    $blocageJours = (int) config('geolocation.blocage_periode_jours', 30);
    if ($blocageMax > 0) {
        $suspectsNonJustifies = Presence::where('employerID', Auth::id())
            ->where('suspect', true)
            ->where('statut_traitement', '!=', 'justifié')
            ->whereDate('date', '>=', now()->subDays($blocageJours))
            ->count();

        if ($suspectsNonJustifies >= $blocageMax) {
            $this->notifySuperviseurMembresBloques();

            return redirect()->back()->withErrors(
                'Votre pointage est bloqué : ' . $suspectsNonJustifies
                . ' de vos présences sont marquées suspectes sans justification. '
                . 'Contactez l\'administrateur pour les faire examiner.'
            );
        }
    }

    // Vérifier la géolocalisation
    $latitude = (float) $request->input('latitude');
    $longitude = (float) $request->input('longitude');

    if (!$latitude || !$longitude) {
        return redirect()->back()->withErrors('La géolocalisation est requise pour marquer votre présence.');
    }

    // Anti-triche : vérifier la signature délivrée par /user/check-location
    $signature = $request->input('signature');
    if (!$signature) {
        return redirect()->back()->withErrors('Veuillez valider votre position avant de marquer votre présence.');
    }

    $verification = $this->geoService->verifySignature(
        $signature,
        $latitude,
        $longitude,
        $request->input('client_timestamp') ? (int) $request->input('client_timestamp') : null
    );

    if (!$verification['valid']) {
        return redirect()->back()->withErrors($verification['reason']);
    }

    // Trouver un lieu de travail valide
    $validLocation = false;
    $workplaceLocationId = null;
    $matchedLocation = null;

    $workplaceLocations = WorkplaceLocation::where('actif', true)->get();

    foreach ($workplaceLocations as $location) {
        if ($location->isWithinRadius($latitude, $longitude)) {
            $validLocation = true;
            $workplaceLocationId = $location->id;
            $matchedLocation = $location;
            break;
        }
    }

    if (!$validLocation) {
        return redirect()->back()->withErrors('Vous devez être physiquement présent sur votre lieu de travail pour marquer votre présence.');
    }

    // Vérification croisée : précision GPS déclarée par le navigateur
    $accuracy = $request->input('accuracy');
    $suspect = false;
    $motifs = [];

    if ($accuracy !== null && (float) $accuracy > (int) config('geolocation.max_accuracy_m', 300)) {
        $suspect = true;
        $motifs[] = 'Précision GPS trop faible (' . round((float) $accuracy) . ' m).';
    }

    // Empêcher un double pointage d'arrivée le même jour
    $existing = Presence::where('employerID', Auth::id())
                       ->whereDate('date', $now->toDateString())
                       ->whereNotNull('heureArrivee')
                       ->first();

    if ($existing) {
        return redirect()->back()->withErrors('Vous avez déjà pointé votre arrivée aujourd\'hui.');
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
        'workplace_location_id' => $workplaceLocationId,
        'accuracy_arrivee' => $accuracy !== null ? (float) $accuracy : null,
        'client_timestamp_arrivee' => $request->input('client_timestamp') ? (int) $request->input('client_timestamp') : null,
        'suspect' => $suspect,
        'motif_suspicion' => $suspect ? implode(' ', $motifs) : null,
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

    // Alerter l'admin si la présence est marquée suspecte (anti-triche)
    $this->notifyAdminSuspect($user, $presence);

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
    $latitude = (float) $request->input('latitude');
    $longitude = (float) $request->input('longitude');

    if (!$latitude || !$longitude) {
        return redirect()->back()->withErrors('La géolocalisation est requise pour marquer votre départ.');
    }

    // Anti-triche : vérifier la signature délivrée par /user/check-location
    $signature = $request->input('signature');
    if (!$signature) {
        return redirect()->back()->withErrors('Veuillez valider votre position avant de marquer votre départ.');
    }

    $verification = $this->geoService->verifySignature(
        $signature,
        $latitude,
        $longitude,
        $request->input('client_timestamp') ? (int) $request->input('client_timestamp') : null
    );

    if (!$verification['valid']) {
        return redirect()->back()->withErrors($verification['reason']);
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

    // Vérification croisée : distance et vitesse entre le point d'arrivée et de départ
    $suspect = (bool) $presence->suspect;
    $motifs = $presence->motif_suspicion ? explode(' ', $presence->motif_suspicion) : [];

    $accuracy = $request->input('accuracy');
    if ($accuracy !== null && (float) $accuracy > (int) config('geolocation.max_accuracy_m', 300)) {
        $suspect = true;
        $motifs[] = 'Précision GPS trop faible (' . round((float) $accuracy) . ' m).';
    }

    $distanceKm = null;
    $speedKmh = null;
    if ($presence->latitude_arrivee && $presence->longitude_arrivee) {
        $distanceKm = $this->geoService->haversineKm(
            (float) $presence->latitude_arrivee,
            (float) $presence->longitude_arrivee,
            $latitude,
            $longitude
        );

        $secondsElapsed = abs($now->diffInSeconds($presence->heureArrivee));
        $speedKmh = $this->geoService->speedKmh($distanceKm, $secondsElapsed);

        // Une vitesse de déplacement irréaliste (ex. Paris -> Lyon le même jour)
        // indique une position falsifiée sur l'un des deux pointages.
        if ($speedKmh > (int) config('geolocation.max_speed_kmh', 40)) {
            $suspect = true;
            $motifs[] = 'Vitesse de déplacement irréaliste (' . round($speedKmh, 1) . ' km/h).';
        }
    }

    $presence->update([
        'heureDepart' => $now,
        'status' => 'présent',
        'latitude_depart' => $latitude,
        'longitude_depart' => $longitude,
        'localisation_validee_depart' => true,
        'workplace_location_id' => $workplaceLocationId,
        'accuracy_depart' => $accuracy !== null ? (float) $accuracy : null,
        'client_timestamp_depart' => $request->input('client_timestamp') ? (int) $request->input('client_timestamp') : null,
        'distance_km' => $distanceKm !== null ? round($distanceKm, 3) : null,
        'vitesse_kmh' => $speedKmh !== null ? round($speedKmh, 2) : null,
        'suspect' => $suspect,
        'motif_suspicion' => $suspect ? implode(' ', $motifs) : null,
    ]);

    // Alerter l'admin si la présence est devenue suspecte (ex. vitesse irréaliste)
    $this->notifyAdminSuspect($user, $presence);

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

    /**
     * Notifie le superviseur quand plusieurs membres de son équipe sont bloqués
     * (dépassement du seuil de présences suspectes non justifiées).
     */
    private function notifySuperviseurMembresBloques(): void
    {
        $employeId = Auth::id();
        $blocageMax = (int) config('geolocation.blocage_suspects_max', 3);
        $blocageJours = (int) config('geolocation.blocage_periode_jours', 30);

        // Trouver le superviseur de l'employé
        $employerInfo = DB::table('employer')->where('id', $employeId)->first();
        if (!$employerInfo || !$employerInfo->Sup_id) {
            return;
        }

        $superviseurUser = Utilisateur::find($employerInfo->Sup_id);
        if (!$superviseurUser || $superviseurUser->role !== 'Superviseur') {
            return;
        }

        // Équipe du superviseur
        $superviseurInfo = DB::table('Superviseur')->where('id', $superviseurUser->id)->first();
        if (!$superviseurInfo || !$superviseurInfo->equipe) {
            return;
        }

        $membresEquipe = DB::table('employer')->where('equipe', $superviseurInfo->equipe)->pluck('id')->toArray();
        if (empty($membresEquipe)) {
            return;
        }

        // Compter les membres bloqués de l'équipe
        $membresBloques = [];
        foreach ($membresEquipe as $membreId) {
            $count = Presence::where('employerID', $membreId)
                ->where('suspect', true)
                ->where('statut_traitement', '!=', 'justifié')
                ->whereDate('date', '>=', now()->subDays($blocageJours))
                ->count();

            if ($count >= $blocageMax) {
                $nom = DB::table('utilisateur')->where('id', $membreId)->value('nom') ?? 'Employé #' . $membreId;
                $membresBloques[] = ['nom' => $nom, 'suspects' => $count];
            }
        }

        if (empty($membresBloques)) {
            return;
        }

        // Notifier le superviseur avec la liste complète des membres bloqués de son équipe
        $superviseurUser->notify(new \App\Notifications\MembresBloquesNotification($membresBloques));
    }

    /**
     * Notifie l'administrateur principal quand une présence est marquée suspecte.
     */
    private function notifyAdminSuspect(Utilisateur $employeUser, Presence $presence): void
    {
        if (!$presence->suspect) {
            return;
        }

        $adminPrincipal = $this->primaryAdmin();
        if ($adminPrincipal) {
            $adminPrincipal->notify(new \App\Notifications\SuspectPresenceNotification($employeUser, $presence));
        }
    }
}
