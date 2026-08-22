<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Models\ServiceFourni;
use App\Models\Superviseur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandeController extends Controller
{
    /**
     * GET /api/commandes — lister les commandes de l'utilisateur (via son superviseur).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $sup = Superviseur::where('id', $user->id)->first();

        if (!$sup) {
            return response()->json(['commandes' => []]);
        }

        $commandes = Commande::where('superviseur_id', $sup->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'type' => $c->type,
                'montant' => (float) $c->montant,
                'montant_paye' => (float) $c->montant_paye,
                'reste' => (float) $c->reste,
                'statut_paiement' => $c->statut_paiement,
                'details' => $c->details,
                'date' => $c->date?->format('Y-m-d'),
                'created_at' => $c->created_at?->format('Y-m-d H:i'),
            ]);

        return response()->json(['commandes' => $commandes]);
    }

    /**
     * POST /api/commandes — enregistrer une commande (mobile).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'montant_paye' => 'nullable|numeric|min:0',
            'statut_paiement' => 'required|in:paye,partiel,a_payer',
            'details' => 'nullable|string|max:2000',
        ]);

        $user = $request->user();
        $sup = Superviseur::where('id', $user->id)->first();

        if (!$sup) {
            return response()->json(['message' => 'Aucun profil superviseur associé.'], 403);
        }

        $montantPaye = $request->montant_paye ?? ($request->statut_paiement === 'paye' ? $request->montant : 0);

        $commande = Commande::create([
            'superviseur_id' => $sup->id,
            'type' => $request->type,
            'montant' => $request->montant,
            'montant_paye' => $montantPaye,
            'statut_paiement' => $request->statut_paiement,
            'details' => $request->details,
            'date' => now()->toDateString(),
        ]);

        return response()->json([
            'message' => 'Commande enregistrée avec succès.',
            'commande' => [
                'id' => $commande->id,
                'type' => $commande->type,
                'montant' => (float) $commande->montant,
                'montant_paye' => (float) $commande->montant_paye,
                'statut_paiement' => $commande->statut_paiement,
            ],
        ], 201);
    }

    /**
     * GET /api/services — lister les services de l'utilisateur.
     */
    public function services(Request $request): JsonResponse
    {
        $user = $request->user();
        $sup = Superviseur::where('id', $user->id)->first();

        if (!$sup) {
            return response()->json(['services' => []]);
        }

        $services = ServiceFourni::where('superviseur_id', $sup->id)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'type' => $s->type,
                'montant' => (float) $s->montant,
                'details' => $s->details,
                'date' => $s->date?->format('Y-m-d'),
            ]);

        return response()->json(['services' => $services]);
    }
}
