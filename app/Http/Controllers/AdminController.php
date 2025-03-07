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


class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
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
    ]);

    $startDate = $request->start_date;
    $endDate = $request->end_date;

    // Récupérer les données de présence entre les dates spécifiées avec le nom des employés
    $presences = DB::table('presence')
        ->join('employer', 'presence.employerID', '=', 'employer.id')
        ->join('utilisateur', 'employer.id', '=', 'utilisateur.id')
        ->whereBetween('presence.date', [$startDate, $endDate])
        ->where('presence.status', 'present')
        ->select('utilisateur.nom as employer_nom', DB::raw('count(presence.status) as total_presence'))
        ->groupBy('utilisateur.nom')
        ->get();

    // Afficher le rapport dans une autre page
    return view('admin.report',compact('presences','startDate','endDate'));
}

// Exporter le rapport en PDF
public function exportToPDF($reportData)
{
    $pdf = Pdf::loadView('admin.report_pdf', compact('reportData'));
    return $pdf->download('rapport_presence.pdf');
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
        ->where('presence.status', 'present')
        ->select('utilisateur.nom as employer_nom', DB::raw('count(presence.status) as total_presence'))
        ->groupBy('utilisateur.nom')
        ->get();

    // Exporter le rapport en PDF
    return $this->exportToPDF($presences);
}


    public function showEmployeeList(Request $request)
    {
        $search = $request->input('search');
        $roles = $request->input('roles',[]);

        //Recuperer tous les employes avec leur nom,poste et email
        $query = DB::table('utilisateur')
        ->whereIn('role',['superviseur','employer'])
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

    public function showEmployee()
    {
        return view('admin.showEmployee');
    }

    /**
 * Mettre à jour le profil de l'administrateur
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 */

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