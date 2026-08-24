<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

        if ($user) {
            $this->authorize('delete', $user);

            if ($user->role == 'Superviseur') {
                Employer::where('Sup_id', $user->id)->delete();
                Superviseur::where('id', $user->id)->delete();
            } elseif ($user->role == 'Employer') {
                Employer::where('id', $user->id)->delete();
            }

            $user->delete();

            return redirect()->route('admin.deleteEmployee')->with('success', 'Employé ou superviseur supprimé avec succès');
        }

        return redirect()->route('admin.deleteEmployee')->with('error', 'Aucun utilisateur trouvé avec cet email');
    }

    public function deleteEmployeeFromList(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|exists:Utilisateur,email',
        ]);

        $user = Utilisateur::where('email', $request->email)->first();

        if ($user) {
            if ($user->role == 'Superviseur') {
                Employer::where('Sup_id', $user->id)->delete();
                Superviseur::where('id', $user->id)->delete();
            } elseif ($user->role == 'Employer') {
                Employer::where('id', $user->id)->delete();
            }

            $user->delete();

            return redirect()->route('admin.showEmployeeList')->with('success', 'Employé ou superviseur supprimé avec succès');
        }

        return redirect()->route('admin.showEmployeeList')->with('error', 'Aucun utilisateur trouvé avec cet email');
    }

    public function showEmployeeList(Request $request)
    {
        $this->authorize('viewAny', Utilisateur::class);
        $search = $request->input('search');
        $roles = $request->input('roles', []);

        $query = DB::table('utilisateur')
            ->whereIn('role', ['Superviseur', 'Employer'])
            ->select('nom', 'role', 'email');

        if ($roles) {
            $query->whereIn('role', $roles);
        }
        if ($search) {
            $query->where('nom', 'like', '%' . $search . '%');
        }

        $employees = $query->get();

        return view('admin.showEmployeeList', compact('employees', 'search', 'roles'));
    }
}
