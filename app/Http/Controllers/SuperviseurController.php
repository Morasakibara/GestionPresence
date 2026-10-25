<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Presence;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use App\Services\EvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SuperviseurController extends Controller
{
    public function Supdashboard()
    {
        $superviseur = Auth::user();
        $superviseurInfo = Superviseur::where('id', $superviseur->id)->first();

        // Rediriger les superviseurs spécialisés vers leur dashboard dédié
        if ($superviseurInfo && $superviseurInfo->type_superviseur && $superviseurInfo->type_superviseur !== 'superviseur_a') {
            return match ($superviseurInfo->type_superviseur) {
                'directrice' => redirect()->route('directrice.dashboard'),
                'secretaire' => redirect()->route('secretaire.dashboard'),
                'gestionnaire_stock' => redirect()->route('gestionnaire.dashboard'),
                default => redirect()->route('directrice.dashboard'),
            };
        }

        $equipe = $superviseurInfo ? $superviseurInfo->equipe : 'Non définie';
        $employerIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();

        $today = now()->toDateString();

        $teamMemberCount = count($employerIds);

        $presentToday = Presence::whereIn('employerID', $employerIds)
            ->where('date', $today)
            ->where('status', 'présent')
            ->count();

        $absentToday = Presence::whereIn('employerID', $employerIds)
            ->where('date', $today)
            ->where('status', 'Absent')
            ->count();

        $lateToday = Presence::whereIn('employerID', $employerIds)
            ->where('date', $today)
            ->whereRaw('(HOUR(heureArrivee) > 8 OR (HOUR(heureArrivee) = 8 AND MINUTE(heureArrivee) > 0))')
            ->count();

        // Listes détaillées pour les modals au survol
        $presentsAujourdhui = Presence::whereIn('employerID', $employerIds)
            ->where('date', $today)
            ->where('status', 'present')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->select('utilisateur.nom', 'presence.heureArrivee', 'presence.heureDepart')
            ->orderBy('presence.heureArrivee')
            ->get();

        $absentsAujourdhui = DB::table('utilisateur')
            ->whereIn('id', $employerIds)
            ->whereNotExists(function ($query) use ($today) {
                $query->select(DB::raw(1))
                    ->from('presence')
                    ->whereColumn('presence.employerID', 'utilisateur.id')
                    ->whereDate('presence.date', $today);
            })
            ->select('nom')
            ->orderBy('nom')
            ->get();

        $arrivesAujourdhui = Presence::whereIn('employerID', $employerIds)
            ->where('date', $today)
            ->whereNotNull('heureArrivee')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->select('utilisateur.nom', 'presence.heureArrivee')
            ->orderBy('presence.heureArrivee')
            ->get();

        $partisAujourdhui = Presence::whereIn('employerID', $employerIds)
            ->where('date', $today)
            ->whereNotNull('heureDepart')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->select('utilisateur.nom', 'presence.heureDepart')
            ->orderBy('presence.heureDepart')
            ->get();

        $unreadNotifications = $superviseur->unreadNotifications->count();

        $cacheKey = 'sup_evolution_'.$superviseur->id.'_'.now()->format('Y-m-d-H');
        $evolutionEvaluations = Cache::remember($cacheKey.'_evo', 300, fn () => EvaluationService::evolutionMensuelle($employerIds, 6));
        $statsEvolution = Cache::remember($cacheKey.'_stats', 300, fn () => EvaluationService::statsEvolution($evolutionEvaluations));
        $notesParEmploye = Cache::remember($cacheKey.'_notes', 300, fn () => EvaluationService::notesParEmploye($employerIds, 6));

        return view('superviseur.supdashboard', compact(
            'equipe',
            'teamMemberCount',
            'presentToday',
            'absentToday',
            'lateToday',
            'unreadNotifications',
            'evolutionEvaluations',
            'statsEvolution',
            'notesParEmploye',
            'presentsAujourdhui',
            'absentsAujourdhui',
            'arrivesAujourdhui',
            'partisAujourdhui'
        ));
    }

    public function showFollowPresence()
    {
        $this->authorize('viewAny', Presence::class);
        $superviseur = Auth::user();
        $superviseurInfo = Superviseur::where('id', $superviseur->id)->first();

        if (! $superviseurInfo) {
            return view('superviseur.followPresence', ['utilisateurs' => collect([])]);
        }

        $equipe = $superviseurInfo->equipe;
        $employerIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();
        $utilisateurs = Utilisateur::whereIn('id', $employerIds)->get();

        return view('superviseur.followPresence', compact('utilisateurs'));
    }

    public function getUserDetails($id)
    {
        $utilisateur = Utilisateur::find($id);

        if (! $utilisateur) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $viewMoreUrl = route('viewUser', ['id' => $utilisateur->id]);

        $safeNom = e($utilisateur->nom);
        $safeEmail = e($utilisateur->email);
        $avatarUrl = asset('storage/avatars/'.($utilisateur->avatar ?: 'default.png'));

        $detailsHtml = "
            <p><strong>Nom:</strong> {$safeNom}</p>
            <p><strong>Email:</strong> {$safeEmail}</p>
            <img src='".e($avatarUrl)."' alt='{$safeNom}' width='100'>
        ";

        return response()->json([
            'detailsHtml' => $detailsHtml,
            'viewMoreUrl' => $viewMoreUrl,
        ]);
    }

    public function viewUser($id)
    {
        $utilisateur = Utilisateur::find($id);

        if (! $utilisateur) {
            abort(404);
        }

        $totalPresences = Presence::where('employerID', $utilisateur->id)
            ->where('status', 'présent')
            ->count();

        $currentMonth = now()->month;
        $presenceStats = Presence::where('employerID', $utilisateur->id)
            ->where('status', 'présent')
            ->whereMonth('date', $currentMonth)
            ->get();

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, now()->year);
        $labels = [];
        $data = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $labels[] = $day;
            $data[] = $presenceStats->where('date', now()->year.'-'.$currentMonth.'-'.str_pad($day, 2, '0', STR_PAD_LEFT))->count();
        }

        return view('superviseur.viewUser', [
            'utilisateur' => $utilisateur,
            'totalPresences' => $totalPresences,
            'presenceStats' => [
                'labels' => $labels,
                'data' => $data,
            ],
        ]);
    }

    // ─── TEAM MANAGEMENT ─────────────────────────────────────

    public function showAddMember()
    {
        $superviseur = Auth::user();
        $superviseurData = Superviseur::where('id', $superviseur->id)->first();

        if (! $superviseurData) {
            return redirect()->back()->with('error', 'Erreur : informations du superviseur non trouvées.');
        }

        $equipe = $superviseurData->equipe;
        $employers = Utilisateur::where('role', 'Employer')->get();
        $teamMemberIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();

        return view('superviseur.addMember', compact('employers', 'teamMemberIds'));
    }

    public function addMemberToTeam(Request $request, $employerId)
    {
        $superviseur = Auth::user();

        if (! $superviseur || $superviseur->role !== 'Superviseur') {
            return redirect()->back()->with('error', 'Vous devez être un superviseur pour ajouter des membres à une équipe.');
        }

        $superviseurData = Superviseur::where('id', $superviseur->id)->first();

        if (empty($superviseurData->equipe)) {
            return redirect()->back()->with('error', 'Erreur : votre équipe n\'est pas renseignée.');
        }

        $utilisateur = Utilisateur::where('id', $employerId)->where('role', 'Employer')->first();

        if (! $utilisateur) {
            return redirect()->back()->with('error', 'Utilisateur non trouvé ou n\'est pas un employé.');
        }

        $employer = Employer::where('id', $employerId)->first();

        if (! $employer) {
            $employer = new Employer;
            $employer->id = $employerId;
            $employer->Sup_id = $superviseur->id;
            $employer->poste = 'Employer';
        }

        $employer->equipe = $superviseurData->equipe;
        $employer->Sup_id = $superviseur->id;
        $employer->save();

        return redirect()->back()->with('success', 'Employé ajouté avec succès à votre équipe.');
    }

    public function removeMemberFromTeam(Request $request, $employerId)
    {
        $superviseur = Auth::user();

        if (! $superviseur || $superviseur->role !== 'Superviseur') {
            return redirect()->back()->with('error', 'Vous devez être un superviseur pour retirer des membres de votre équipe.');
        }

        $superviseurData = Superviseur::where('id', $superviseur->id)->first();

        if (! $superviseurData) {
            return redirect()->back()->with('error', 'Erreur : informations du superviseur non trouvées.');
        }

        $employer = Employer::where('id', $employerId)->first();

        if (! $employer) {
            return redirect()->back()->with('error', 'Employé non trouvé.');
        }

        if ($employer->equipe !== $superviseurData->equipe) {
            return redirect()->back()->with('error', 'Cet employé ne fait pas partie de votre équipe.');
        }

        $employer->equipe = null;
        $employer->save();

        return redirect()->back()->with('success', 'Employé retiré avec succès de votre équipe.');
    }

    public function showAddMemberForm(Request $request)
    {
        $search = $request->input('search');
        $superviseur = Auth::user();
        $superviseurData = Superviseur::where('id', $superviseur->id)->first();

        if (! $superviseurData) {
            return redirect()->back()->with('error', 'Erreur : informations du superviseur non trouvées.');
        }

        $equipe = $superviseurData->equipe;
        $teamMemberIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();

        $query = Utilisateur::where('role', 'Employer');

        if ($search) {
            $query->where('nom', 'LIKE', '%'.$search.'%');
        }

        $employers = $query->get();

        return view('superviseur.addMember', compact('employers', 'search', 'teamMemberIds'));
    }
}
