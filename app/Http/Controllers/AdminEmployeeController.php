<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminEmployeeController extends Controller
{
    public function showAddEmployeeForm()
    {
        return view('admin.addEmployee');
    }

    public function storeEmployee(Request $request)
    {
        $this->authorize('create', Utilisateur::class);
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return redirect()->route('admin.addEmployee')
                ->withErrors($validator)
                ->withInput();
        }

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

        if (in_array('Superviseur', $data['role'])) {
            $rules['equipe'] = 'required|string|max:255';
            $rules['type_superviseur'] = 'nullable|string|in:directrice,secretaire,gestionnaire_stock,superviseur_a';
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
            $superviseur = Superviseur::first();
            if ($superviseur) {
                Employer::create([
                    'id' => $user->id,
                    'Sup_id' => $superviseur->id,
                    'poste' => 'Employer',
                ]);
            } else {
                throw new \Exception('Aucun superviseur trouvé. Veuillez en créer un d\'abord.');
            }
        } elseif (in_array('Superviseur', $data['role'])) {
            Superviseur::create([
                'id' => $user->id,
                'equipe' => $data['equipe'],
                'type_superviseur' => $data['type_superviseur'] ?? null,
            ]);
        }

        return $user;
    }

    public function showDeleteEmployeeForm()
    {
        return view('admin.deleteEmployee');
    }

    public function deleteEmployee(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|exists:Utilisateur,email',
        ]);

        $user = Utilisateur::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('admin.deleteEmployee')->with('error', 'Aucun utilisateur trouvé avec cet email');
        }

        $this->authorize('delete', $user);

        $error = DB::transaction(fn () => $this->deleteManagedUser($user));

        if ($error) {
            return redirect()->route('admin.deleteEmployee')->with('error', $error);
        }

        return redirect()->route('admin.deleteEmployee')->with('success', 'Employé ou superviseur supprimé avec succès');
    }

    public function deleteEmployeeFromList(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|exists:Utilisateur,email',
        ]);

        $user = Utilisateur::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('admin.showEmployeeList')->with('error', 'Aucun utilisateur trouvé avec cet email');
        }

        $this->authorize('delete', $user);

        $error = DB::transaction(fn () => $this->deleteManagedUser($user));

        if ($error) {
            return redirect()->route('admin.showEmployeeList')->with('error', $error);
        }

        return redirect()->route('admin.showEmployeeList')->with('success', 'Employé ou superviseur supprimé avec succès');
    }

    /**
     * Supprime le profil métier puis le compte. Retourne un message d'erreur si la suppression est refusée.
     */
    private function deleteManagedUser(Utilisateur $user): ?string
    {
        if (! in_array($user->role, ['Employer', 'Superviseur'], true)) {
            return 'Seuls les employés et superviseurs peuvent être supprimés depuis cette page.';
        }

        if ($user->role === 'Superviseur') {
            $teamCount = Employer::where('Sup_id', $user->id)->count();

            if ($teamCount > 0) {
                return "Ce superviseur encadre encore {$teamCount} employé(s). Réaffectez-les avant de le supprimer.";
            }

            Superviseur::whereKey($user->id)->delete();
        } else {
            Employer::whereKey($user->id)->delete();
        }

        $user->delete();

        return null;
    }

    public function showEmployeeList(Request $request)
    {
        $this->authorize('viewAny', Utilisateur::class);
        $search = $request->input('search');
        $roles = $request->input('roles', []);

        $query = DB::table('utilisateur')
            ->whereIn('role', ['Superviseur', 'Employer'])
            ->whereNull('deleted_at')
            ->select('id', 'nom', 'role', 'email', 'avatar');

        if ($roles) {
            $query->whereIn('role', $roles);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $employees = $query->orderBy('nom')->get();

        // Nombre d'employés encadrés par chaque superviseur (pour bloquer/guider la
        // suppression et alimenter la réaffectation depuis le modal).
        $supIds = $employees->where('role', 'Superviseur')->pluck('id');
        $managedCounts = $supIds->isEmpty()
            ? collect()
            : DB::table('employer')
                ->whereIn('Sup_id', $supIds)
                ->selectRaw('Sup_id, count(*) as c')
                ->groupBy('Sup_id')
                ->pluck('c', 'Sup_id');

        // Superviseurs cibles possibles pour la réaffectation : liste complète,
        // indépendante du filtre de recherche de la page.
        $superviseurs = DB::table('utilisateur')
            ->where('role', 'Superviseur')
            ->whereNull('deleted_at')
            ->select('id', 'nom', 'role', 'email')
            ->orderBy('nom')
            ->get();

        return view('admin.showEmployeeList', compact(
            'employees', 'search', 'roles', 'managedCounts', 'superviseurs'
        ));
    }

    /**
     * Réaffecte tous les employés d'un superviseur vers un autre avant suppression.
     * Déplace Sup_id et l'équipe pour rester cohérent avec le nouveau superviseur.
     */
    public function reassignEmployees(Request $request)
    {
        $this->authorize('viewAny', Utilisateur::class);

        $data = $request->validate([
            'from_superviseur_id' => ['required', 'integer', 'exists:superviseur,id'],
            'to_superviseur_id' => ['required', 'integer', 'different:from_superviseur_id', 'exists:superviseur,id'],
        ], [
            'to_superviseur_id.different' => 'Choisissez un superviseur différent de celui à supprimer.',
        ]);

        $cible = Superviseur::findOrFail($data['to_superviseur_id']);

        $moved = DB::transaction(function () use ($data, $cible) {
            $miseAJour = ['Sup_id' => $cible->id];
            // La table employer porte une colonne equipe ; on l'aligne sur le nouveau superviseur.
            if (DB::getSchemaBuilder()->hasColumn('employer', 'equipe')) {
                $miseAJour['equipe'] = $cible->equipe;
            }

            return DB::table('employer')
                ->where('Sup_id', $data['from_superviseur_id'])
                ->update($miseAJour);
        });

        AuditService::updated(
            'admin',
            "Réaffectation de {$moved} employé(s) du superviseur #{$data['from_superviseur_id']} vers #{$cible->id}",
            ['from_superviseur_id' => $data['from_superviseur_id']],
            ['to_superviseur_id' => $cible->id, 'moved' => $moved],
            $request
        );

        $nomCible = Utilisateur::where('id', $cible->id)->value('nom') ?: ('#'.$cible->id);

        return redirect()->route('admin.showEmployeeList')
            ->with('success', "{$moved} employé(s) réaffecté(s) à « {$nomCible} ». Vous pouvez maintenant supprimer l'ancien superviseur.");
    }
}
