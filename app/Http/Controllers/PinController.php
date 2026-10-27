<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class PinController extends Controller
{
    /** Cookie chiffré de récupération par PIN après expiration de session. */
    public const COOKIE_NAME = 'pharaon_pin_recovery';

    /** Durée de validité du cookie de récupération (12 h). */
    public const COOKIE_TTL_MINUTES = 720;

    /** Nombre max de tentatives de PIN en récupération (par heure). */
    public const MAX_ATTEMPTS = 5;

    /**
     * Poser le cookie de récupération PIN (appelé après une connexion réussie).
     *
     * Ce cookie survit à l'expiration de la session : il permettra de se
     * reloguer au seul code PIN, au lieu du couple email + mot de passe.
     * Ne s'applique qu'aux utilisateurs ayant activé le PIN.
     */
    public static function issueRecoveryCookie(Utilisateur $user): void
    {
        if ($user->pin_required && $user->pin_code) {
            $payload = json_encode(['id' => $user->id, 'iat' => now()->timestamp]);
            Cookie::queue(
                Cookie::make(self::COOKIE_NAME, $payload, self::COOKIE_TTL_MINUTES, '/', null, false, true)
            );
        }
    }

    /** Supprimer le cookie de récupération (déconnexion explicite). */
    public static function forgetRecoveryCookie(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    /**
     * L'utilisateur désigné par le cookie de récupération, s'il est valide.
     */
    public static function recoveryUser(Request $request): ?Utilisateur
    {
        $raw = $request->cookie(self::COOKIE_NAME);
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        try {
            $payload = json_decode($raw, true);
        } catch (\Throwable) {
            return null;
        }

        if (! is_array($payload) || ! isset($payload['id'], $payload['iat'])) {
            return null;
        }

        // Expiration côté serveur aussi (le cookie peut être trafiqué hors app)
        if (now()->timestamp - (int) $payload['iat'] > self::COOKIE_TTL_MINUTES * 60) {
            return null;
        }

        $user = Utilisateur::find((int) $payload['id']);
        if (! $user || ! $user->pin_required || ! $user->pin_code) {
            return null;
        }

        return $user;
    }

    /**
     * Vérifier le PIN et restaurer la session.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|size:4',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Non authentifié.'], 401);
        }

        if (! $user->pin_required || ! $user->pin_code) {
            return response()->json(['error' => 'PIN non configuré.'], 422);
        }

        if (! Hash::check($request->pin, $user->pin_code)) {
            return response()->json(['error' => 'Code PIN incorrect.'], 422);
        }

        // PIN correct → la session est restaurée
        // On enregistre un marker dans la session pour prouver que le PIN a été validé
        session(['pin_verified' => true, 'pin_verified_at' => now()->timestamp]);

        return response()->json([
            'success' => true,
            'redirect' => $this->redirectPath($user),
        ]);
    }

    /**
     * Récupération de session expirée : connexion au seul code PIN.
     *
     * Déclenché depuis la page de login quand le cookie de récupération
     * est présent. Limité à 5 tentatives par heure.
     */
    public function verifyRecovery(Request $request)
    {
        $request->validate(['pin' => 'required|string|size:4']);

        $user = self::recoveryUser($request);
        if (! $user) {
            return response()->json(
                ['error' => 'Récupération indisponible — connectez-vous avec email et mot de passe.'],
                401
            );
        }

        $key = 'pin-recovery:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            self::forgetRecoveryCookie();

            return response()->json(
                ['error' => 'Trop de tentatives. Connectez-vous avec votre email et mot de passe.'],
                429
            );
        }

        if (! Hash::check($request->pin, $user->pin_code)) {
            RateLimiter::hit($key, 3600);

            return response()->json(['error' => 'Code PIN incorrect.'], 422);
        }

        RateLimiter::clear($key);

        // Reconnexion complète équivalente : session fraîche + PIN approuvé
        Auth::login($user);
        session()->regenerate();
        $user->emettreJetonSession(); // session unique : expulse les autres appareils
        session([
            'pin_verified' => true,
            'pin_verified_at' => now()->timestamp,
            'current_role' => $user->role === 'Superviseur' ? 'Superviseur' : $user->role,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => $this->redirectPath($user),
        ]);
    }

    /**
     * Définir / changer le PIN (depuis les paramètres).
     */
    public function setPin(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'pin' => 'required|string|size:4|digits:4',
            'pin_confirmation' => 'required|same:pin',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->motDePasse)) {
            return redirect()->back()->with('error', 'Mot de passe actuel incorrect.');
        }

        $user->update([
            'pin_code' => Hash::make($request->pin),
            'pin_required' => true,
        ]);

        return redirect()->back()->with('success', 'Code PIN enregistré avec succès.');
    }

    /**
     * Désactiver le PIN.
     */
    public function removePin(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->motDePasse)) {
            return redirect()->back()->with('error', 'Mot de passe actuel incorrect.');
        }

        $user->update([
            'pin_code' => null,
            'pin_required' => false,
        ]);

        return redirect()->back()->with('success', 'Code PIN supprimé.');
    }

    /**
     * Vérifier si l'utilisateur a besoin de valider son PIN.
     */
    public function checkStatus()
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['pin_required' => false]);
        }

        $needsPin = $user->pin_required && ! $this->isPinVerified($user->id);

        return response()->json([
            'pin_required' => $needsPin,
            'has_pin' => (bool) $user->pin_code,
        ]);
    }

    /**
     * Réinitialiser la vérification PIN (appelé au logout).
     */
    public static function clearPinSession(): void
    {
        session()->forget(['pin_verified', 'pin_verified_at']);
        self::forgetRecoveryCookie();
    }

    /**
     * Vérifie si le PIN a été validé dans la session.
     */
    private function isPinVerified(int $userId): bool
    {
        return session('pin_verified', false);
    }

    /**
     * Redirection après validation PIN.
     */
    private function redirectPath(Utilisateur $user): string
    {
        return match ($user->role) {
            'Administrateur' => '/admin/dashboard',
            'Employer' => '/user/dashboard',
            'Superviseur' => session('current_role') === 'Employer'
                ? '/user/dashboard'
                : '/superviseur/supdashboard',
            default => '/dashboard',
        };
    }
}
