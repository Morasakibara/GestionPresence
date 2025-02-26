<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use App\Models\Presence;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuperviseurController extends Controller
{
    public function Supdashboard(){
        return view('superviseur.supdashboard');
    }

    public function showFollowPresence()
{
    $utilisateurs = Utilisateur::whereIn('role', ['Superviseur', 'Employer'])->get();
    return view('superviseur.followPresence', compact('utilisateurs'));
}

    public function followPresence()
    {
        $utilisateurs = Utilisateur::whereIn('role',['Superviseur','employer'])->get();
        return view('superviseur/followPresence',compact('utilisateurs'));
    }
    public function getUserDetails($id)
    {
        $utilisateur = Utilisateur::find($id);

        if (!$utilisateur) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $viewMoreUrl = route('viewUser', ['id' => $utilisateur->id]);

        $detailsHtml = "
            <p><strong>Nom:</strong> {$utilisateur->nom}</p>
            <p><strong>Email:</strong> {$utilisateur->email}</p>
            <img src='".asset('storage/avatars/'.($utilisateur->avatar ?: 'default.png'))."' alt='{$utilisateur->nom}' width='100'>
        ";

        return response()->json([
            'detailsHtml' => $detailsHtml,
            'viewMoreUrl' => $viewMoreUrl
        ]);
    }

public function viewUser($id)
{
    $utilisateur = Utilisateur::find($id);

    // Calculer le total des présences où le statut est "present"
    $totalPresences = Presence::where('employerID', $utilisateur->id)
                              ->where('status', 'present')
                              ->count();

    // Récupérer les présences de l'utilisateur pour le mois en cours avec le statut "present"
    $currentMonth = now()->month;
    $presenceStats = Presence::where('employerID', $utilisateur->id)
                             ->where('status', 'present')
                             ->whereMonth('date', $currentMonth)
                             ->get();

    // Préparer les données pour le graphique
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, now()->year);
    $labels = [];
    $data = [];

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $labels[] = $day;
        $data[] = $presenceStats->where('date', now()->year . '-' . $currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT))->count();
    }

    // Vérifier les données
   /* dd([
        'labels' => $labels,
        'data' => $data,
    ]);*/

    // Passer les données à la vue
    return view('superviseur.viewUser', [
        'utilisateur' => $utilisateur,
        'totalPresences' => $totalPresences,
        'presenceStats' => [
            'labels' => $labels,
            'data' => $data,
        ],
    ]);
}




    public function showGenerateReport()
    {
        return view('superviseur.generateReport2');
    }

    public function generateReport()
{
    $superviseur = auth()->user(); // Assume que le superviseur est l'utilisateur connecté
    $equipe = $superviseur->equipe;

    // Récupérer les employés de la même équipe
    $employers = Employer::where('equipe', $equipe)->where('poste', 'employer')->get();

    // Calculer le total de présences pour chaque employé pour le mois en cours
    $currentMonth = now()->month;
    $reports = [];

    foreach ($employers as $employer) {
        $totalPresences = Presence::where('employerID', $employer->id)
            ->whereMonth('date', $currentMonth)
            ->where('status', 'present')
            ->count();

        $reports[] = [
            'name' => $employer->name,
            'totalPresences' => $totalPresences,
        ];
    }

    return view('superviseur.generateReport2', compact('reports'));
}

public function exportPDF()
{
    $superviseur = auth()->user();
    $equipe = $superviseur->equipe;

    // Récupérer les employés de la même équipe
    $employers = Employer::where('equipe', $equipe)->where('poste', 'employer')->get();

    // Calculer le total de présences pour chaque employé pour le mois en cours
    $currentMonth = now()->month;
    $reports = [];

    foreach ($employers as $employer) {
        $totalPresences = Presence::where('employerID', $employer->id)
            ->whereMonth('date', $currentMonth)
            ->where('status', 'present')
            ->count();

        $reports[] = [
            'name' => $employer->name,
            'totalPresences' => $totalPresences,
        ];
    }

    $pdf = PDF::loadView('superviseur.generateReportPDF', compact('reports'));

    return $pdf->download('rapport_equipe.pdf');
}

public function addMember()
{
    return view('superviseur.addMember');
}

// Afficher les employés avec le rôle 'employer' pour les ajouter à l'équipe
public function showAddMember()
{
    // Récupérer les utilisateurs ayant le rôle 'employer'
    $employers = Utilisateur::where('role', 'employer')->get();

    return view('superviseur.addMember', compact('employers'));
}

// Ajouter un employé à l'équipe du superviseur
public function addMemberToTeam(Request $request, $employerId)
{
    // Récupérer le superviseur connecté
    $superviseur = auth()->user();

    // Vérifier si le superviseur est bien connecté
    if (!$superviseur || $superviseur->role !== 'Superviseur') {
        return redirect()->back()->with('error', 'Vous devez être un superviseur pour ajouter des membres à une équipe.');
    }

    // Récupérer l'équipe du superviseur depuis la table 'superviseur'
    $superviseurData = Superviseur::where('id', $superviseur->id)->first();

    // Vérifier si l'équipe du superviseur est renseignée
    if (empty($superviseurData->equipe)) {
        return redirect()->back()->with('error', 'Erreur : votre équipe n\'est pas renseignée.');
    }

    // Récupérer l'employé à mettre à jour
    $employer = Employer::where('id', $employerId)->first();

    if (!$employer) {
        return redirect()->back()->with('error', 'Employé introuvable.');
    }

    // Mettre à jour le champ "equipe" de l'employé avec l'équipe du superviseur
    $employer->equipe = $superviseurData->equipe;
    $employer->save();

    // Retourner un message de succès avec un message JavaScript si l'ajout a fonctionné
    return redirect()->back()->with('success', 'Employé ajouté avec succès à votre équipe.');
}

public function showAddMemberForm(Request $request)
{
    $search = $request->input('search'); // Récupérer le terme de recherche

    // Récupérer les utilisateurs ayant le rôle 'employer' et qui correspondent à la recherche
    $query = Utilisateur::where('role', 'employer');

    if ($search) {
        $query->where('nom', 'LIKE', '%' . $search . '%'); // Filtrer par nom si la recherche est présente
    }

    $employers = $query->get(); // Exécuter la requête

    return view('superviseur.addMember', compact('employers', 'search'));
}


}
