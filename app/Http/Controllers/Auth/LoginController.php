<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->role === 'Superviseur') {
                return $this->showRoleSelectionModal($user);
            } elseif ($user->role === 'Employer') {
                session(['current_role' => 'Employer']);
                return redirect()->intended('/user/dashboard');
            } elseif ($user->role === 'administrateur') {
                return redirect()->intended('/admin/dashboard');
            }

            // Optionally handle other roles or redirect to a default page
            return redirect()->intended('dashboard');
        }

        return redirect('login')->withErrors(['email' => 'Email ou mot de passe incorrect.']);
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
