<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Utilisateur;
use App\Services\LoginAttemptService;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    private LoginAttemptService $loginAttempts;

    public function __construct(LoginAttemptService $loginAttempts)
    {
        $this->loginAttempts = $loginAttempts;
    }

    public function showLoginForm(Request $request)
    {
        // Session expirée + cookie de récupération → panneau « déverrouillage par PIN »
        $pinRecovery = \App\Http\Controllers\PinController::recoveryUser($request);

        return view('auth.login', [
            'pinRecovery' => $pinRecovery ? ['email' => $pinRecovery->email] : null,
        ]);
    }

    public function login(Request $request)
    {
        $email = strtolower(trim($request->input('email', '')));

        // ─── 1. Vérifier le lockout prolongé (15 min après 10 échecs) ────
        if ($email && $this->loginAttempts->isLockedOut($email)) {
            throw ValidationException::withMessages([
                'email' => [$this->loginAttempts->lockoutMessage($email)],
            ]);
        }

        // ─── 2. Throttle classique Laravel (5/min par email+IP) ──────────
        $throttleKey = 'login:' . ($email ?: $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ['Trop de tentatives. Réessayez dans ' . $seconds . ' secondes.'],
            ]);
        }

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Veuillez saisir votre adresse email.',
            'email.email'       => 'Veuillez saisir une adresse email valide.',
            'password.required' => 'Veuillez saisir votre mot de passe.',
        ]);

        Log::info('Tentative de connexion', ['email' => $email]);

        // ─── 3. Recherche de l'utilisateur ───────────────────────────────
        $user = Utilisateur::where('email', $email)->first();

        if (!$user) {
            Log::warning('Utilisateur non trouvé', ['email' => $email]);
            $this->recordFailure($email, $throttleKey);
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect.'],
            ]);
        }

        // ─── 4. Vérification du mot de passe ─────────────────────────────
        $passwordMatches = Hash::check($request->password, $user->motDePasse);

        if ($passwordMatches) {
            // ── Succès : tout reset ──
            RateLimiter::clear($throttleKey);
            $this->loginAttempts->reset($email);
            Auth::login($user);

            // Le PIN ne doit être demandé qu'à l'expiration de la session :
            // une connexion email + mot de passe est un jeton plus fort que le PIN.
            session([
                'pin_verified' => true,
                'pin_verified_at' => now()->timestamp,
                // Le son de notification ne doit retentir qu'une fois, juste après la connexion
                'notif_sound_armed' => true,
            ]);

            // Cookie permettant de se relogger au seul PIN si la session expire.
            \App\Http\Controllers\PinController::issueRecoveryCookie($user);

            Log::info('Utilisateur connecté avec succès', ['id' => $user->id, 'role' => $user->role]);

            if ($user->role === 'Superviseur') {
                return $this->showRoleSelectionModal($user);
            } elseif ($user->role === 'Employer') {
                session(['current_role' => 'Employer']);
                return redirect()->intended('/user/dashboard');
            } elseif ($user->role === 'Administrateur') {
                return redirect()->intended('/admin/dashboard');
            }

            return redirect()->intended('dashboard');
        }

        // ── Échec : incrémenter les deux compteurs ──
        $this->recordFailure($email, $throttleKey);

        Log::warning('Échec authentification', ['email' => $email]);

        $attempts = $this->loginAttempts->getAttempts($email);
        $remaining = $this->loginAttempts->maxAttempts() - $attempts;

        $message = $remaining > 3
            ? 'Email ou mot de passe incorrect.'
            : "Email ou mot de passe incorrect. Attention, votre compte sera bloqué après {$remaining} autre(s) tentative(s).";

        throw ValidationException::withMessages([
            'email' => [$message],
        ]);
    }

    /**
     * Enregistre un échec dans les deux systèmes (throttle + lockout).
     */
    private function recordFailure(string $email, string $throttleKey): void
    {
        $this->loginAttempts->recordFailure($email);
        RateLimiter::hit($throttleKey, 60);
    }

    public function showRoleSelectionModal()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        if ($user->role !== 'Superviseur') {
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
        \App\Http\Controllers\PinController::clearPinSession();
        Auth::logout();
        return redirect('/login');
    }
}
