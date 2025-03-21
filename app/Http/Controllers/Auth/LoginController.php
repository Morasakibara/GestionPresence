<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Veuillez saisir votre adresse email.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'password.required' => 'Veuillez saisir votre mot de passe.',
        ]);

        // Journaliser les identifiants pour déboguer
        Log::info('Tentative de connexion', ['email' => $request->email]);

        // Récupérer l'utilisateur par email
        $user = Utilisateur::where('email', $request->email)->first();

        // Vérifier si l'utilisateur existe
        if (!$user) {
            Log::warning('Utilisateur non trouvé', ['email' => $request->email]);
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect.'],
            ]);
        }

        // Journaliser les informations de l'utilisateur pour déboguer
        Log::info('Utilisateur trouvé', [
            'id' => $user->id,
            'nom' => $user->nom,
            'role' => $user->role,
            'motDePasseLength' => $user->motDePasse ? strlen($user->motDePasse) : 0,
            'motDePasseStartsWith' => $user->motDePasse ? substr($user->motDePasse, 0, 10) . '...' : 'null'
        ]);

        // Vérifier si le mot de passe correspond
        $passwordMatches = Hash::check($request->password, $user->motDePasse);
        Log::info('Vérification du mot de passe', [
            'passwordMatches' => $passwordMatches,
            'passwordLength' => strlen($request->password)
        ]);

        if ($passwordMatches) {
            // Connecter manuellement l'utilisateur
            Auth::login($user);
            Log::info('Utilisateur connecté avec succès', ['id' => $user->id]);

            // Redirection en fonction du rôle
            if ($user->role === 'Superviseur') {
                return $this->showRoleSelectionModal($user);
            } elseif ($user->role === 'Employer') {
                session(['current_role' => 'Employer']);
                return redirect()->intended('/user/dashboard');
            } elseif ($user->role === 'administrateur') {
                return redirect()->intended('/admin/dashboard');
            }

            // Redirection par défaut
            return redirect()->intended('dashboard');
        }

        // Authentification échouée
        Log::warning('Échec d\'authentification - mot de passe incorrect', ['email' => $request->email]);
        throw ValidationException::withMessages([
            'email' => ['Email ou mot de passe incorrect.'],
        ]);
    }

    public function showRoleSelectionModal()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        // Assurez-vous que l'utilisateur est bien un superviseur
        if ($user->role !== 'Superviseur') {
            // Si ce n'est pas un superviseur, rediriger vers le tableau de bord approprié
            session(['current_role' => $user->role]);
            return redirect()->intended($user->role === 'Employer' ? '/user/dashboard' : '/admin/dashboard');
        }

        return view('auth.role_selection', compact('user'));
    }

    public function selectRole(Request $request)
    {
        $role = $request->input('role');

        if ($role === 'Employer' || $role === 'Superviseur') {
            session(['current_role' => $role]);
            return response()->json([
                'redirect' => $role === 'Employer' ? '/user/dashboard' : '/superviseur/supdashboard'
            ]);
        }

        return response()->json(['error' => 'Rôle non valide'], 400);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }
}
