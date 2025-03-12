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
use Illuminate\Support\Facades\Storage;

class SuperviseurController extends Controller
{
    public function Supdashboard(){
        // Récupérer le superviseur connecté
        $superviseur = Auth::user();

        // Récupérer les informations d'équipe du superviseur
        $superviseurInfo = Superviseur::where('id', $superviseur->id)->first();

        // Obtenir le nom de l'équipe
        $equipe = $superviseurInfo ? $superviseurInfo->equipe : 'Non définie';

        // Récupérer les IDs des employés de cette équipe
        $employerIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();

        // Statistiques de l'équipe
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $today = now()->toDateString();

        // Nombre total des membres de l'équipe
        $teamMemberCount = count($employerIds);

        // Compter les présences du jour pour l'équipe
        $presentToday = Presence::whereIn('employerID', $employerIds)
                               ->whereDate('date', $today)
                               ->where('status', 'présent')
                               ->count();

        // Compter les absences du jour
        $absentToday = Presence::whereIn('employerID', $employerIds)
                              ->whereDate('date', $today)
                              ->where('status', 'Absent')
                              ->count();

        // Récupérer les retards du jour
        $lateToday = Presence::whereIn('employerID', $employerIds)
                            ->whereDate('date', $today)
                            ->whereRaw('HOUR(heureArrivee) > 8 OR (HOUR(heureArrivee) = 8 AND MINUTE(heureArrivee) > 0)')
                            ->count();

        // Récupérer le nombre de notifications non lues
        $unreadNotifications = $superviseur->unreadNotifications->count();

        return view('superviseur.supdashboard', compact(
            'equipe',
            'teamMemberCount',
            'presentToday',
            'absentToday',
            'lateToday',
            'unreadNotifications'
        ));
    }

    public function showFollowPresence()
    {
        // Récupérer le superviseur connecté
        $superviseur = Auth::user();

        // Récupérer les informations d'équipe du superviseur
        $superviseurInfo = Superviseur::where('id', $superviseur->id)->first();

        if (!$superviseurInfo) {
            // Si le superviseur n'a pas d'infos d'équipe, retourner une liste vide
            return view('superviseur.followPresence', ['utilisateurs' => collect([])]);
        }

        // Récupérer le nom de l'équipe
        $equipe = $superviseurInfo->equipe;

        // Récupérer les IDs des employés appartenant à cette équipe
        $employerIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();

        // Récupérer les utilisateurs correspondant à ces IDs
        $utilisateurs = Utilisateur::whereIn('id', $employerIds)->get();

        return view('superviseur.followPresence', compact('utilisateurs'));
    }

    public function followPresence()
    {
        // Cette méthode semble être un doublon de showFollowPresence
        // On va la rediriger vers la méthode showFollowPresence pour maintenir la cohérence
        return $this->showFollowPresence();
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
        // Récupérer le superviseur connecté
        $superviseur = auth()->user();

        // Vérifier si le superviseur existe
        if (!$superviseur || $superviseur->role !== 'Superviseur') {
            return redirect()->back()->with('error', 'Vous devez être un superviseur pour générer un rapport.');
        }

        // Récupérer les informations d'équipe du superviseur
        $superviseurInfo = Superviseur::where('id', $superviseur->id)->first();

        if (!$superviseurInfo) {
            return redirect()->back()->with('error', 'Informations du superviseur non trouvées.');
        }

        $equipe = $superviseurInfo->equipe;

        // Récupérer les employés de la même équipe
        $employers = Employer::where('equipe', $equipe)->pluck('id')->toArray();

        // Calculer le total de présences pour chaque employé pour le mois en cours
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $reports = [];

        // Récupérer les utilisateurs correspondant à ces IDs d'employé
        $users = Utilisateur::whereIn('id', $employers)->get();

        foreach ($users as $user) {
            $totalPresences = Presence::where('employerID', $user->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'present')
                ->count();

            $reports[] = [
                'name' => $user->nom,
                'totalPresences' => $totalPresences,
            ];
        }

        // Générer le PDF
        $pdf = PDF::loadView('superviseur.generateReportPDF', [
            'reports' => $reports,
            'equipe' => $equipe,
            'date' => now()->format('d/m/Y'),
            'superviseur' => $superviseur->nom
        ]);

        // Définir la période (mois et année en cours)
        $periode = now()->format('Y-m-d H:i:s');

        // Enregistrer le PDF dans le stockage
        $filename = 'rapport_equipe_' . $equipe . '_' . now()->format('Y_m_d_His') . '.pdf';
        $pdfPath = 'rapports/' . $filename;
        Storage::disk('public')->put($pdfPath, $pdf->output());

        // Créer un nouvel enregistrement dans la table rapport
        $rapport = new \App\Models\Rapport();
        $rapport->Sup_id = $superviseur->id;
        $rapport->periode = $periode;
        $rapport->contenu = $pdfPath; // Chemin vers le PDF stocké
        $rapport->created_at = now();
        $rapport->updated_at = now();

        // Puisque la colonne Adm_id est obligatoire dans la structure de la BDD,
        // nous devons récupérer un administrateur existant
        $admin = DB::table('administrateur')->first();
        if ($admin) {
            $rapport->Adm_id = $admin->id;
        } else {
            // Si aucun administrateur n'existe, créer un message d'erreur
            return redirect()->back()->with('error', 'Aucun administrateur trouvé dans le système. Impossible d\'enregistrer le rapport.');
        }

        // Sauvegarder le rapport
        $rapport->save();

        // Télécharger le PDF
        return $pdf->download($filename);
    }

    public function addMember()
    {
        return view('superviseur.addMember');
    }

    // Afficher les employés avec le rôle 'employer' pour les ajouter à l'équipe
    public function showAddMember()
    {
        // Récupérer le superviseur connecté
        $superviseur = auth()->user();

        // Récupérer les informations d'équipe du superviseur
        $superviseurData = Superviseur::where('id', $superviseur->id)->first();

        if (!$superviseurData) {
            return redirect()->back()->with('error', 'Erreur : informations du superviseur non trouvées.');
        }

        // Récupérer l'équipe du superviseur
        $equipe = $superviseurData->equipe;

        // Récupérer les utilisateurs ayant le rôle 'employer'
        $employers = Utilisateur::where('role', 'Employer')->get();

        // Récupérer les IDs des employés appartenant déjà à l'équipe du superviseur
        $teamMemberIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();

        return view('superviseur.addMember', compact('employers', 'teamMemberIds'));
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

        // D'abord, vérifiez si l'utilisateur existe
        $utilisateur = Utilisateur::where('id', $employerId)->where('role', 'Employer')->first();

        if (!$utilisateur) {
            return redirect()->back()->with('error', 'Utilisateur non trouvé ou n\'est pas un employé.');
        }

        // Ensuite, vérifiez si une entrée correspondante existe dans la table employer
        $employer = Employer::where('id', $employerId)->first();

        if (!$employer) {
            // Si l'entrée n'existe pas dans la table employer, créez-la
            $employer = new Employer();
            $employer->id = $employerId;
            $employer->Sup_id = $superviseur->id;  // Définir le superviseur ID
            $employer->poste = 'Employer';
        }

        // Mettre à jour le champ "equipe" de l'employé avec l'équipe du superviseur
        $employer->equipe = $superviseurData->equipe;
        $employer->save();

        return redirect()->back()->with('success', 'Employé ajouté avec succès à votre équipe.');
    }

    // Retirer un employé de l'équipe du superviseur
    public function removeMemberFromTeam(Request $request, $employerId)
    {
        // Récupérer le superviseur connecté
        $superviseur = auth()->user();

        // Vérifier si le superviseur est bien connecté
        if (!$superviseur || $superviseur->role !== 'Superviseur') {
            return redirect()->back()->with('error', 'Vous devez être un superviseur pour retirer des membres de votre équipe.');
        }

        // Récupérer l'équipe du superviseur
        $superviseurData = Superviseur::where('id', $superviseur->id)->first();

        if (!$superviseurData) {
            return redirect()->back()->with('error', 'Erreur : informations du superviseur non trouvées.');
        }

        // Récupérer l'employé
        $employer = Employer::where('id', $employerId)->first();

        if (!$employer) {
            return redirect()->back()->with('error', 'Employé non trouvé.');
        }

        // Vérifier que l'employé fait bien partie de l'équipe du superviseur
        if ($employer->equipe !== $superviseurData->equipe) {
            return redirect()->back()->with('error', 'Cet employé ne fait pas partie de votre équipe.');
        }

        // Vider le champ équipe (plutôt que de supprimer l'enregistrement)
        $employer->equipe = 'rienuzg9u7h'; // Valeur par défaut comme dans la structure de la base de données
        $employer->save();

        return redirect()->back()->with('success', 'Employé retiré avec succès de votre équipe.');
    }

    public function showAddMemberForm(Request $request)
    {
        $search = $request->input('search'); // Récupérer le terme de recherche

        // Récupérer le superviseur connecté
        $superviseur = auth()->user();

        // Récupérer les informations d'équipe du superviseur
        $superviseurData = Superviseur::where('id', $superviseur->id)->first();

        if (!$superviseurData) {
            return redirect()->back()->with('error', 'Erreur : informations du superviseur non trouvées.');
        }

        // Récupérer l'équipe du superviseur
        $equipe = $superviseurData->equipe;

        // Récupérer les IDs des employés appartenant déjà à l'équipe du superviseur
        $teamMemberIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();

        // Récupérer les utilisateurs ayant le rôle 'employer' et qui correspondent à la recherche
        $query = Utilisateur::where('role', 'Employer');

        if ($search) {
            $query->where('nom', 'LIKE', '%' . $search . '%'); // Filtrer par nom si la recherche est présente
        }

        $employers = $query->get(); // Exécuter la requête

        return view('superviseur.addMember', compact('employers', 'search', 'teamMemberIds'));
    }
}
