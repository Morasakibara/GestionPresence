<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\AuditService;

class CongeController extends Controller
{
    /**
     * Liste des demandes de congé/absence pour le superviseur connecté.
     */
    public function index(Request $request)
    {
        $superviseur = Auth::user();
        $supInfo = Superviseur::where('id', $superviseur->id)->first();

        if (!$supInfo) {
            return redirect()->back()->with('error', 'Profil superviseur introuvable.');
        }

        // Récupérer les IDs des employés de l'équipe
        $equipeIds = DB::table('employer')
            ->where('equipe', $supInfo->equipe)
            ->pluck('id')
            ->toArray();

        $query = Conge::whereIn('employer_id', $equipeIds)
            ->with('employe')
            ->orderByDesc('created_at');

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $conges = $query->paginate(15)->withQueryString();

        // Stats
        $stats = [
            'total' => Conge::whereIn('employer_id', $equipeIds)->count(),
            'en_attente' => Conge::whereIn('employer_id', $equipeIds)->where('statut', 'en_attente')->count(),
            'approuves' => Conge::whereIn('employer_id', $equipeIds)->where('statut', 'approuve')->count(),
            'refuses' => Conge::whereIn('employer_id', $equipeIds)->where('statut', 'refuse')->count(),
        ];

        return view('superviseur.conges', compact('conges', 'stats', 'supInfo'));
    }

    /**
     * Approuver une demande de congé.
     */
    public function approuver(Conge $conge)
    {
        $conge->update([
            'statut' => 'approuve',
            'traite_par' => Auth::id(),
            'commentaire_admin' => 'Demande approuvée par le superviseur.',
        ]);

        AuditService::updated('conges', "Congé #{$conge->id} approuvé", $conge->toArray());

        // Notifier l'employé
        $employe = Utilisateur::find($conge->employer_id);
        if ($employe) {
            $employe->notify(new \App\Notifications\CongeNotification($conge, 'approuve'));
        }

        $nom = $employe->nom ?? 'l\'employé';
        return redirect()->back()->with('success', "Demande de congé de {$nom} approuvée.");
    }

    /**
     * Refuser une demande de congé.
     */
    public function refuser(Request $request, Conge $conge)
    {
        $request->validate([
            'commentaire' => 'required|string|max:500',
        ]);

        $conge->update([
            'statut' => 'refuse',
            'traite_par' => Auth::id(),
            'commentaire_admin' => $request->commentaire,
        ]);

        AuditService::updated('conges', "Congé #{$conge->id} refusé", $conge->toArray());

        $employe = Utilisateur::find($conge->employer_id);
        if ($employe) {
            $employe->notify(new \App\Notifications\CongeNotification($conge, 'refuse'));
        }

        return redirect()->back()->with('success', "Demande de congé refusée.");
    }

    /**
     * Vue employé : soumettre une demande de congé.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'type' => 'required|in:conge,absence_justifiee,absence_injustifiee',
            'motif' => 'required|string|max:1000',
        ]);

        $employeId = Auth::id();

        // Vérifier pas de chevauchement
        $chevauchement = Conge::where('employer_id', $employeId)
            ->where('statut', '!=', 'refuse')
            ->where(function ($q) use ($request) {
                $q->whereBetween('date_debut', [$request->date_debut, $request->date_fin])
                  ->orWhereBetween('date_fin', [$request->date_debut, $request->date_fin])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('date_debut', '<=', $request->date_debut)
                         ->where('date_fin', '>=', $request->date_fin);
                  });
            })
            ->exists();

        if ($chevauchement) {
            return redirect()->back()->with('error', 'Vous avez déjà une demande de congé/absence sur cette période.');
        }

        Conge::create([
            'employer_id' => $employeId,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'type' => $request->type,
            'motif' => $request->motif,
            'statut' => 'en_attente',
        ]);

        AuditService::created('conges', "Nouvelle demande de congé soumise", [
            'employer_id' => $employeId,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
        ]);

        return redirect()->back()->with('success', 'Votre demande de congé a été soumise.');
    }
}
