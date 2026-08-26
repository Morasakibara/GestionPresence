<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class PinController extends Controller
{
    /**
     * Vérifier le PIN et restaurer la session.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|size:4',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Non authentifié.'], 401);
        }

        if (!$user->pin_required || !$user->pin_code) {
            return response()->json(['error' => 'PIN non configuré.'], 422);
        }

        if (!Hash::check($request->pin, $user->pin_code)) {
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
     * Définir / changer le PIN (depuis les paramètres).
     */
    public function setPin(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'pin'              => 'required|string|size:4|digits:4',
            'pin_confirmation' => 'required|same:pin',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->motDePasse)) {
            return redirect()->back()->with('error', 'Mot de passe actuel incorrect.');
        }

        $user->update([
            'pin_code'     => Hash::make($request->pin),
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

        if (!Hash::check($request->current_password, $user->motDePasse)) {
            return redirect()->back()->with('error', 'Mot de passe actuel incorrect.');
        }

        $user->update([
            'pin_code'     => null,
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
        if (!$user) {
            return response()->json(['pin_required' => false]);
        }

        $needsPin = $user->pin_required && !$this->isPinVerified($user->id);

        return response()->json([
            'pin_required' => $needsPin,
            'has_pin'      => (bool) $user->pin_code,
        ]);
    }

    /**
     * Réinitialiser la vérification PIN (appelé au logout).
     */
    public static function clearPinSession(): void
    {
        session()->forget(['pin_verified', 'pin_verified_at']);
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
            'Employer'       => '/user/dashboard',
            'Superviseur'    => session('current_role') === 'Employer'
                ? '/user/dashboard'
                : '/superviseur/supdashboard',
            default => '/dashboard',
        };
    }
}
