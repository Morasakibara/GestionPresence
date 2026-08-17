<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use Illuminate\Support\Facades\Hash;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use App\Models\Presence;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Exports\ReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class AdminController extends Controller
{
    public function dashboard()
{
    // Récupérer l'administrateur connecté
    $admin = Auth::user();

    // Calculer les statistiques globales
    $totalEmployees = Utilisateur::where('role', 'Employer')->count();
    $totalSupervisors = Utilisateur::where('role', 'Superviseur')->count();

    // Statistiques pour aujourd'hui
    $today = now()->toDateString();
    $presentToday = Presence::whereDate('date', $today)
                          ->where('status', 'présent')
                          ->count();

    $absentToday = Presence::whereDate('date', $today)
                         ->where('status', 'Absent')
                         ->count();

    // Statistiques pour le mois en cours
    $currentMonth = now()->month;
    $currentYear = now()->year;

    $monthlyPresences = Presence::whereMonth('date', $currentMonth)
                              ->whereYear('date', $currentYear)
                              ->where('status', 'présent')
                              ->count();

    $monthlyAbsences = Presence::whereMonth('date', $currentMonth)
                             ->whereYear('date', $currentYear)
                             ->where('status', 'Absent')
                             ->count();

    // Calculer les retards pour le mois en cours
    $monthlyLates = Presence::whereMonth('date', $currentMonth)
                         ->whereYear('date', $currentYear)
                         ->whereRaw('(HOUR(heureArrivee) > 8 OR (HOUR(heureArrivee) = 8 AND MINUTE(heureArrivee) > 0))')
                         ->count();

    // Évolution de la note moyenne sur 6 mois (toute l'entreprise)
    $evolutionEvaluations = \App\Services\EvaluationService::evolutionMensuelle(null, 6);

    return view('admin.dashboard', compact(
        'totalEmployees',
        'totalSupervisors',
        'presentToday',
        'absentToday',
        'monthlyPresences',
        'monthlyAbsences',
        'monthlyLates',
        'evolutionEvaluations'
    ));
}

    public function showAddEmployeeForm()
    {
        return view('admin.addEmployee');
    }

    public function storeEmployee(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return redirect()->route('admin.addEmployee')
                ->withErrors($validator)
                ->withInput();
        }

        // Créer l'utilisateur
        $user = $this->createEmployee($request->all());

       if ($user) {
        return redirect()->route('admin.addEmployee')->with('success', 'Employé ajouté avec succès');
       } else {
        return redirect()->route('admin.addEmployee')->with('error', 'Employé non ajouté');
       }

    }

    protected function validator(array $data)
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:Utilisateur',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|array|min:1',
            'role.*' => 'required|string|in:Employer,Superviseur',
        ];

        // Ajout de validation pour le champ 'equipe' seulement si le rôle est 'Superviseur'
        if (in_array('Superviseur', $data['role'])) {
            $rules['equipe'] = 'required|string|max:255';
        }

        return Validator::make($data, $rules);
    }

    protected function createEmployee(array $data)
    {
        $user = Utilisateur::create([
            'nom' => $data['nom'],
            'email' => $data['email'],
            'motDePasse' => Hash::make($data['password']),
            'role' => in_array('Superviseur', $data['role']) ? 'Superviseur' : 'Employer',
        ]);

        if (in_array('Employer', $data['role'])) {
            // Récupérer un superviseur existant (par exemple, le premier superviseur)
            $superviseur = Superviseur::first();

            // Vérifier si un superviseur existe
            if ($superviseur) {
                Employer::create([
                    'id' => $user->id,
                    'Sup_id' => $superviseur->id,
                    'poste' => 'Employer',
                ]);
            } else {
                // Gérer le cas où aucun superviseur n'existe (par exemple, renvoyer une erreur ou créer un superviseur par défaut)
                throw new \Exception('Aucun superviseur trouvé. Veuillez en créer un d\'abord.');
            }
        } elseif (in_array('Superviseur', $data['role'])) {
            Superviseur::create([
                'id' => $user->id,
                'equipe' => $data['equipe'],
            ]);
        }

        return $user;
    }

    // Cette méthode reste pour la compatibilité mais n'est plus accessible via le menu
    public function showDeleteEmployeeForm()
    {
        return view('admin.deleteEmployee');
    }

    // Cette méthode reste pour la compatibilité mais sera remplacée par deleteEmployeeFromList
    public function deleteEmployee(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|exists:Utilisateur,email',
        ]);

        // Trouver l'utilisateur par email
        $user = Utilisateur::where('email', $request->email)->first();

        if ($user) {
            if ($user->role == 'Superviseur') {
                // Supprimer les employés associés à ce superviseur
                Employer::where('Sup_id', $user->id)->delete();

                // Supprimer le superviseur
                Superviseur::where('id', $user->id)->delete();
            } elseif ($user->role == 'Employer') {
                // Supprimer l'employé
                Employer::where('id', $user->id)->delete();
            }

            // Supprimer l'utilisateur
            $user->delete();

            return redirect()->route('admin.deleteEmployee')->with('success', 'Employé ou superviseur supprimé avec succès');
        }

        return redirect()->route('admin.deleteEmployee')->with('error', 'Aucun utilisateur trouvé avec cet email');
    }

    // Nouvelle méthode pour supprimer un employé depuis la liste
    public function deleteEmployeeFromList(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|exists:Utilisateur,email',
        ]);

        // Trouver l'utilisateur par email
        $user = Utilisateur::where('email', $request->email)->first();

        if ($user) {
            if ($user->role == 'Superviseur') {
                // Supprimer les employés associés à ce superviseur
                Employer::where('Sup_id', $user->id)->delete();

                // Supprimer le superviseur
                Superviseur::where('id', $user->id)->delete();
            } elseif ($user->role == 'Employer') {
                // Supprimer l'employé
                Employer::where('id', $user->id)->delete();
            }

            // Supprimer l'utilisateur
            $user->delete();

            return redirect()->route('admin.showEmployeeList')->with('success', 'Employé ou superviseur supprimé avec succès');
        }

        return redirect()->route('admin.showEmployeeList')->with('error', 'Aucun utilisateur trouvé avec cet email');
    }

    public function showGenerateReportForm()
    {
        return view('admin.generateReport');
    }


 // Générer le rapport et exporter en PDF
public function generateReport(Request $request)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'export_format' => 'required|string',
    ]);

    $startDate = $request->start_date;
    $endDate = $request->end_date;

    // Récupérer les données de présence entre les dates spécifiées avec le nom des employés
    $presences = DB::table('presence')
        ->join('employer', 'presence.employerID', '=', 'employer.id')
        ->join('utilisateur', 'employer.id', '=', 'utilisateur.id')
        ->whereBetween('presence.date', [$startDate, $endDate])
        ->where('presence.status', 'présent')
        ->select('utilisateur.nom as employer_nom', 'presence.employerID')
        ->get();

    // Regrouper par employé avec réalisations (fiches de rendement) et évaluation
    $reportData = [];
    foreach ($presences->groupBy('employer_nom') as $nom => $group) {
        $employerID = (int) $group->first()->employerID;
        $rendements = \App\Models\Presence::where('employerID', $employerID)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('rendement')
            ->where('rendement', '!=', '')
            ->orderBy('date')
            ->pluck('rendement')
            ->toArray();

        // Temps de travail total de la période (minutes)
        $presencesPeriode = \App\Models\Presence::where('employerID', $employerID)
            ->whereBetween('date', [$startDate, $endDate])
            ->get(['heureArrivee', 'heureDepart']);
        $totalMinutes = $presencesPeriode->sum(fn ($p) => \App\Services\EvaluationService::minutesTravail($p->heureArrivee, $p->heureDepart));
        $totalHeures = \App\Services\EvaluationService::formaterDureeTotale($totalMinutes);

        $evaluation = \App\Services\EvaluationService::evaluer($employerID, $startDate, $endDate);

        // Historique d'évaluation des 6 derniers mois (pour le graphique de comparaison)
        $historique = [];
        for ($i = 5; $i >= 0; $i--) {
            $mois = now()->subMonths($i);
            $eval = \App\Services\EvaluationService::evaluer(
                $employerID,
                $mois->copy()->startOfMonth()->toDateString(),
                $mois->copy()->endOfMonth()->toDateString()
            );
            $historique[] = [
                'mois' => $mois->format('Y-m'),
                'label' => ucfirst($mois->locale('fr')->isoFormat('MMMM')),
                'note' => $eval['note'],
                'couleur' => $eval['couleur'],
            ];
        }

        $reportData[] = (object) [
            'employer_nom' => $nom,
            'employerID' => $employerID,
            'total_presence' => $group->count(),
            'total_heures' => $totalHeures,
            'rendements' => $rendements,
            'evaluation_note' => $evaluation['note'],
            'evaluation_couleur' => $evaluation['couleur'],
            'evaluation_commentaire' => $evaluation['commentaire'],
            'evaluation_manuelle' => $evaluation['manuelle'],
            'historique' => $historique,
        ];
    }

    usort($reportData, fn ($a, $b) => $b->total_presence <=> $a->total_presence);
    $reportData = collect($reportData);

    // Si l'utilisateur a choisi d'exporter en PDF
    if ($request->export_format === 'pdf') {
        return $this->exportToPDF($reportData, $startDate, $endDate);
    }

    // Sinon, afficher le rapport dans une autre page
    return view('admin.report', compact('reportData', 'startDate', 'endDate'));
}

// Exporter le rapport en PDF
public function exportToPDF($reportData, $startDate = null, $endDate = null)
{
    // Les données arrivent déjà enrichies (reportData), on les passe telles quelles au PDF.

    // Récupérer l'administrateur connecté
    $admin = auth()->user();

    // Vérifier si l'administrateur existe
    if (!$admin || $admin->role !== 'Administrateur') {
        return redirect()->back()->with('error', 'Vous devez être un administrateur pour générer un rapport.');
    }

    // Récupérer les informations d'administrateur
    $adminInfo = DB::table('administrateur')->where('id', $admin->id)->first();

    if (!$adminInfo) {
        return redirect()->back()->with('error', 'Informations de l\'administrateur non trouvées.');
    }

    // Créer le PDF
    $pdf = Pdf::loadView('admin.report_pdf', [
        'reportData' => $reportData,
        'startDate' => $startDate,
        'endDate'=> $endDate,
        'admin' => $admin->nom,
        'generatedDate' => now()->format('d/m/Y')
    ]);

    // Définir la période de rapport
    $periode = $startDate . ' au ' . $endDate;

    // Enregistrer le PDF dans le stockage
    $filename = 'rapport_presence_' . str_replace('-', '_', $startDate) . '_' . str_replace('-', '_', $endDate) . '.pdf';
    $pdfPath = 'rapports/' . $filename;
    Storage::disk('public')->put($pdfPath, $pdf->output());

    // Créer un nouvel enregistrement dans la table rapport
    $rapport = new \App\Models\Rapport();
    $rapport->Adm_id = $admin->id;
    $rapport->periode = now()->format('Y-m-d H:i:s');
    $rapport->contenu = $pdfPath; // Chemin vers le PDF stocké
    $rapport->created_at = now();
    $rapport->updated_at = now();



    // La colonne Sup_id peut être NULL selon votre structure de base de données
    // Si elle est obligatoire, nous devons récupérer un superviseur existant
    if (DB::getSchemaBuilder()->getColumnListing('rapport')[2] === 'Sup_id') {
        $superviseur = DB::table('superviseur')->first();
        if ($superviseur) {
            $rapport->Sup_id = $superviseur->id;
        } else {
            // Si aucun superviseur n'existe et que le champ est obligatoire
            return redirect()->back()->with('error', 'Aucun superviseur trouvé dans le système. Impossible d\'enregistrer le rapport.');
        }
    }

    // Sauvegarder le rapport
    $rapport->save();

    // Télécharger le PDF
    return $pdf->download($filename);
}

public function exportReport(Request $request)
{
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    $startDate = $request->start_date;
    $endDate = $request->end_date;

    // Récupérer les données de présence entre les dates spécifiées avec le nom des employés
    $presences = DB::table('presence')
        ->join('employer', 'presence.employerID', '=', 'employer.id')
        ->join('utilisateur', 'employer.id', '=', 'utilisateur.id')
        ->whereBetween('presence.date', [$startDate, $endDate])
        ->where('presence.status', 'présent')
        ->select('utilisateur.nom as employer_nom', 'presence.employerID')
        ->get();

    // Regrouper par employé avec réalisations (fiches de rendement) et évaluation
    $reportData = [];
    foreach ($presences->groupBy('employer_nom') as $nom => $group) {
        $employerID = (int) $group->first()->employerID;
        $rendements = \App\Models\Presence::where('employerID', $employerID)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('rendement')
            ->where('rendement', '!=', '')
            ->orderBy('date')
            ->pluck('rendement')
            ->toArray();

        // Temps de travail total de la période (minutes)
        $presencesPeriode = \App\Models\Presence::where('employerID', $employerID)
            ->whereBetween('date', [$startDate, $endDate])
            ->get(['heureArrivee', 'heureDepart']);
        $totalMinutes = $presencesPeriode->sum(fn ($p) => \App\Services\EvaluationService::minutesTravail($p->heureArrivee, $p->heureDepart));
        $totalHeures = \App\Services\EvaluationService::formaterDureeTotale($totalMinutes);

        $evaluation = \App\Services\EvaluationService::evaluer($employerID, $startDate, $endDate);

        $reportData[] = (object) [
            'employer_nom' => $nom,
            'employerID' => $employerID,
            'total_presence' => $group->count(),
            'total_heures' => $totalHeures,
            'rendements' => $rendements,
            'evaluation_note' => $evaluation['note'],
            'evaluation_couleur' => $evaluation['couleur'],
            'evaluation_commentaire' => $evaluation['commentaire'],
            'evaluation_manuelle' => $evaluation['manuelle'],
        ];
    }

    usort($reportData, fn ($a, $b) => $b->total_presence <=> $a->total_presence);
    $reportData = collect($reportData);

    // Exporter le rapport en PDF
    return $this->exportToPDF($reportData, $startDate, $endDate);
}


    /**
     * Export CSV des évaluations et des rendements par employé.
     */
    public function exportEvaluationsCsv(Request $request)
    {
        $mois = $request->input('mois', now()->format('Y-m'));
        $debut = $mois . '-01';
        $fin = now()->parse($debut)->endOfMonth()->toDateString();

        $employes = DB::table('employer')
            ->join('utilisateur', 'employer.id', '=', 'utilisateur.id')
            ->select('employer.id', 'utilisateur.nom')
            ->orderBy('utilisateur.nom')
            ->get();

        $lignes = [];
        $lignes[] = ['Employé', 'Mois', 'Note /20', 'Couleur', 'Commentaire', 'Heures travaillées', 'Rendements du mois'];

        foreach ($employes as $employe) {
            $evaluation = \App\Services\EvaluationService::evaluer($employe->id, $debut, $fin);

            // Temps de travail total du mois
            $presencesPeriode = \App\Models\Presence::where('employerID', $employe->id)
                ->whereBetween('date', [$debut, $fin])
                ->get(['heureArrivee', 'heureDepart']);
            $totalMinutes = $presencesPeriode->sum(fn ($p) => \App\Services\EvaluationService::minutesTravail($p->heureArrivee, $p->heureDepart));
            $totalHeures = \App\Services\EvaluationService::formaterDureeTotale($totalMinutes);

            $rendements = \App\Models\Presence::where('employerID', $employe->id)
                ->whereBetween('date', [$debut, $fin])
                ->whereNotNull('rendement')
                ->where('rendement', '!=', '')
                ->orderBy('date')
                ->get(['date', 'rendement'])
                ->map(fn ($r) => $r->date . ' : ' . $r->rendement)
                ->implode("\n");

            $lignes[] = [
                $employe->nom,
                $mois,
                number_format($evaluation['note'], 1, ',', ''),
                $evaluation['couleur'],
                $evaluation['commentaire'],
                $totalHeures,
                $rendements,
            ];
        }

        // Export Excel aux couleurs de la charte Pharaon (logo + en-tête noir/or)
        $service = \App\Services\ExcelExportService::creer(
            'Évaluations & rendements — Le Pharaon',
            'Mois de ' . ucfirst(\Carbon\Carbon::parse($debut)->locale('fr')->isoFormat('MMMM YYYY')),
            ['Employé', 'Mois', 'Note /20', 'Couleur', 'Commentaire', 'Heures travaillées', 'Rendements du mois'],
            $lignes
        );

        // Coloration des notes selon la couleur d'évaluation (vert/orange/rouge de la charte)
        $ligneEnTete = 5;
        foreach ($employes as $i => $employe) {
            $evaluation = \App\Services\EvaluationService::evaluer($employe->id, $debut, $fin);
            $couleur = match ($evaluation['couleur']) {
                'vert' => \App\Services\ExcelExportService::SUCCES,
                'orange' => \App\Services\ExcelExportService::ALERTE,
                'rouge' => \App\Services\ExcelExportService::DANGER,
                default => \App\Services\ExcelExportService::NOIR,
            };
            // Colonne 3 = Note /20
            $service->colorerCellule($ligneEnTete + 1 + $i, 3, $couleur);
            // Colonne 4 = libellé de couleur, coloré aussi
            $service->colorerCellule($ligneEnTete + 1 + $i, 4, $couleur);
        }

        $contenu = $service->contenu();

        return response($contenu, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename=evaluations_rendements_' . $mois . '.xlsx',
        ]);
    }

    /**
     * Bulletin individuel d'évaluation PDF pour un employé et un mois.
     */
    public function evaluationBulletin(Request $request, $id)
    {
        $admin = auth()->user();
        if (!$admin || $admin->role !== 'Administrateur') {
            return redirect()->back()->with('error', 'Accès réservé à l\'administrateur.');
        }

        $mois = $request->input('mois', now()->format('Y-m'));
        $debut = $mois . '-01';
        $fin = now()->parse($debut)->endOfMonth()->toDateString();

        $employe = DB::table('employer')
            ->join('utilisateur', 'employer.id', '=', 'utilisateur.id')
            ->where('employer.id', $id)
            ->select('employer.id', 'utilisateur.nom')
            ->first();

        if (!$employe) {
            return redirect()->back()->with('error', 'Employé non trouvé.');
        }

        $evaluation = \App\Services\EvaluationService::evaluer($id, $debut, $fin);
        $stats = \App\Services\EvaluationService::statsPeriode($id, $debut, $fin);
        $rendements = Presence::where('employerID', $id)
            ->whereBetween('date', [$debut, $fin])
            ->whereNotNull('rendement')
            ->where('rendement', '!=', '')
            ->orderBy('date')
            ->get(['date', 'heureArrivee', 'heureDepart', 'rendement']);

        $pdf = Pdf::loadView('admin.evaluation_bulletin_pdf', compact('employe', 'evaluation', 'stats', 'rendements', 'mois', 'debut', 'fin'));
        $filename = 'bulletin_evaluation_' . $mois . '_' . $id . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Enregistre (ou met à jour) une évaluation manuelle pour un employé et un mois.
     */
    public function storeEvaluation(Request $request)
    {
        $request->validate([
            'employerID' => 'required|integer|exists:employer,id',
            'mois' => 'required|date_format:Y-m',
            'note' => 'required|numeric|min:0|max:20',
            'couleur' => 'required|in:vert,orange,rouge',
            'commentaire' => 'nullable|string|max:2000',
        ]);

        \App\Models\Evaluation::updateOrCreate(
            ['employerID' => $request->employerID, 'mois' => $request->mois],
            [
                'note' => (float) $request->note,
                'couleur' => $request->couleur,
                'commentaire' => $request->commentaire,
                'evaluateur_id' => Auth::id(),
            ]
        );

        // Alerte automatique si l'évaluation passe en rouge
        if ($request->couleur === 'rouge') {
            $employe = \App\Models\Utilisateur::find($request->employerID);
            $adminPrincipal = $this->primaryAdmin();
            if ($employe && $adminPrincipal && (int) $adminPrincipal->id !== (int) Auth::id()) {
                $adminPrincipal->notify(new \App\Notifications\EvaluationRougeNotification(
                    $employe->nom,
                    $request->mois,
                    (float) $request->note
                ));
            }
        }

        return redirect()->back()->with('success', 'Évaluation enregistrée.');
    }

    public function showEmployeeList(Request $request)
    {
        $search = $request->input('search');
        $roles = $request->input('roles',[]);

        //Recuperer tous les employes avec leur nom,poste et email
        $query = DB::table('utilisateur')
        ->whereIn('role',['Superviseur','Employer'])
        ->select('nom','role','email');

       if ($roles) {
        $query->whereIn('role',$roles);
       }
        if ($search) {
            $query->where('nom','like','%'.$search.'%');
        }

        $employees = $query->get();

        //Retourner la vue avec les donnees des employes
        return view('admin.showEmployeeList',compact('employees','search','roles'));
    }

    /**
 * Mettre à jour le profil de l'administrateur
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 */

    /**
     * Retourne l'administrateur principal (le plus ancien) pour les notifications.
     */
    private function primaryAdmin(): ?\App\Models\Utilisateur
    {
        return \App\Models\Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
    }

    public function updateProfile(Request $request)
{
    // Validation des données
    $validator = Validator::make($request->all(), [
        'nom' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . auth()->id(),
        'password' => 'nullable|string|min:8|confirmed',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Récupérer l'utilisateur connecté
        $user = auth()->user();

        // Mettre à jour les informations
        $user->nom = $request->nom;
        $user->email = $request->email;

        // Mise à jour du mot de passe si fourni
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Traitement de l'avatar
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar s'il existe
            if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }

            // Enregistrer le nouvel avatar
            $avatarName = time() . '.' . $request->avatar->extension();
            $request->avatar->storeAs('avatars', $avatarName, 'public');
            $user->avatar = $avatarName;
        }

        // Enregistrer les modifications
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du profil: ' . $e->getMessage()
        ], 500);
    }
    }
    public function showProfileForm()
    {
        return view('admin.profile', ['user' => auth()->user()]);
    }
}
