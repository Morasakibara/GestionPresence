<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\PresenceTraitement;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Historique complet d'une présence, accessible selon le rôle :
 * - Admin : toutes les présences.
 * - Superviseur : les présences des membres de son équipe.
 * - Employé : ses propres présences uniquement.
 */
class PresenceHistoryController extends Controller
{
    /**
     * Vérifie que l'utilisateur connecté peut consulter la présence.
     * Retourne null si autorisé, sinon un message d'erreur.
     */
    private function checkAccess(Presence $presence): ?string
    {
        $user = Auth::user();

        if (!$user) {
            return 'Vous devez être connecté.';
        }

        if ($user->role === 'Administrateur') {
            return null; // L'admin voit tout
        }

        if ($user->role === 'Superviseur') {
            $employerInfo = DB::table('employer')->where('id', $presence->employerID)->first();
            if ($employerInfo && (int) $employerInfo->Sup_id === (int) $user->id) {
                return null;
            }

            return 'Cette présence ne fait pas partie de votre équipe.';
        }

        if ($user->role === 'Employer' && (int) $presence->employerID === (int) $user->id) {
            return null;
        }

        return 'Accès non autorisé à cette présence.';
    }

    /**
     * Affiche la timeline complète d'une présence.
     */
    public function show($id)
    {
        $presence = Presence::find($id);

        if (!$presence) {
            return redirect()->back()->with('error', 'Présence introuvable.');
        }

        $error = $this->checkAccess($presence);
        if ($error) {
            return redirect()->back()->with('error', $error);
        }

        $traitements = PresenceTraitement::where('presence_id', $presence->id)
            ->orderByDesc('created_at')
            ->get();

        // Nom de l'employé pour les vues admin/superviseur
        $employeInfo = Utilisateur::find($presence->employerID);
        $presence->employer_nom = $employeInfo ? $employeInfo->nom : null;

        return view('user.presence-history', compact('presence', 'traitements'));
    }

    /**
     * Exporte la timeline d'une présence en PDF (archivage).
     */
    public function exportPdf($id)
    {
        $presence = Presence::find($id);

        if (!$presence) {
            return redirect()->back()->with('error', 'Présence introuvable.');
        }

        $error = $this->checkAccess($presence);
        if ($error) {
            return redirect()->back()->with('error', $error);
        }

        $employe = Utilisateur::find($presence->employerID);
        $traitements = PresenceTraitement::where('presence_id', $presence->id)
            ->orderBy('created_at')
            ->get();

        $pdf = Pdf::loadView('user.presence_history_pdf', [
            'presence' => $presence,
            'employe' => $employe,
            'traitements' => $traitements,
            'generatedDate' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('historique_presence_' . $presence->date . '.pdf');
    }
}
