<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Commande;
use App\Models\Retrait;
use App\Models\ServiceFourni;
use App\Models\StockPapier;
use App\Models\StockTshirt;
use App\Models\Superviseur;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFinanceController extends Controller
{
    /**
     * Vue admin : état de la caisse (directrice + secrétaire)
     */
    public function etatCaisse()
    {
        $today = now()->toDateString();

        $directrice = Superviseur::where('type_superviseur', 'directrice')->first();
        $secretaire = Superviseur::where('type_superviseur', 'secretaire')->first();

        $data = [];
        foreach (['directrice' => $directrice, 'secretaire' => $secretaire] as $label => $sup) {
            if (!$sup) continue;
            $totalCommandes = Commande::where('superviseur_id', $sup->id)->whereDate('date', $today)->sum('montant');
            $totalServices = ServiceFourni::where('superviseur_id', $sup->id)->whereDate('date', $today)->sum('montant');
            $totalRetraits = Retrait::where('superviseur_id', $sup->id)->whereDate('date', $today)->sum('montant');
            $data[$label] = [
                'nom' => Utilisateur::find($sup->id)->nom ?? $label,
                'entrees' => $totalCommandes + $totalServices,
                'sorties' => $totalRetraits,
                'chiffre_affaire' => $totalCommandes + $totalServices - $totalRetraits,
            ];
        }

        return view('admin.etat_caisse', compact('data'));
    }

    /**
     * Vue admin : état du stock (gestionnaire)
     */
    public function etatStock()
    {
        $gestionnaire = Superviseur::where('type_superviseur', 'gestionnaire_stock')->first();

        $tshirts = $papiers = collect();
        $totalTshirts = 0;
        if ($gestionnaire) {
            $tshirts = StockTshirt::where('superviseur_id', $gestionnaire->id)->get();
            $papiers = StockPapier::where('superviseur_id', $gestionnaire->id)->get();
            $totalTshirts = $tshirts->sum('quantite');
        }

        return view('admin.etat_stock', compact('tshirts', 'papiers', 'totalTshirts'));
    }

    /**
     * Tableau de bord des paiements — graphiques par statut (payé/partiel/à payer).
     */
    public function paiementStats()
    {
        $secretaire = Superviseur::where('type_superviseur', 'secretaire')->first();
        $directrice = Superviseur::where('type_superviseur', 'directrice')->first();

        $supIds = collect();
        if ($secretaire) $supIds->push($secretaire->id);
        if ($directrice) $supIds->push($directrice->id);

        $paiements = Commande::when($supIds->isNotEmpty(), fn($q) => $q->whereIn('superviseur_id', $supIds))
            ->select('statut_paiement', DB::raw('count(*) as total'), DB::raw('sum(montant) as montant_total'), DB::raw('sum(montant_paye) as montant_paye'))
            ->groupBy('statut_paiement')
            ->get();

        $stats = [
            'paye' => ['count' => 0, 'montant' => 0, 'paye' => 0],
            'partiel' => ['count' => 0, 'montant' => 0, 'paye' => 0],
            'a_payer' => ['count' => 0, 'montant' => 0, 'paye' => 0],
        ];
        foreach ($paiements as $p) {
            $key = $p->statut_paiement ?? 'a_payer';
            $stats[$key] = [
                'count' => (int) $p->total,
                'montant' => (float) $p->montant_total,
                'paye' => (float) $p->montant_paye,
            ];
        }

        $labels = [];
        $payes = [];
        $partiels = [];
        $aPayer = [];
        for ($i = 29; $i >= 0; $i--) {
            $jour = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d/m');
            $dayQuery = Commande::when($supIds->isNotEmpty(), fn($q) => $q->whereIn('superviseur_id', $supIds))
                ->whereDate('created_at', $jour);
            $payes[] = (clone $dayQuery)->where('statut_paiement', 'paye')->count();
            $partiels[] = (clone $dayQuery)->where('statut_paiement', 'partiel')->count();
            $aPayer[] = (clone $dayQuery)->where('statut_paiement', 'a_payer')->count();
        }

        $topCommandes = Commande::when($supIds->isNotEmpty(), fn($q) => $q->whereIn('superviseur_id', $supIds))
            ->orderByDesc('montant')
            ->limit(10)
            ->get();

        $caParPoste = [];
        foreach (['directrice' => 'Imprimerie', 'secretaire' => 'Photo'] as $type => $label) {
            $sup = Superviseur::where('type_superviseur', $type)->first();
            if (!$sup) continue;
            $total = (float) Commande::where('superviseur_id', $sup->id)->sum('montant');
            $paye = (float) Commande::where('superviseur_id', $sup->id)->sum('montant_paye');
            $caParPoste[$label] = ['total' => $total, 'paye' => $paye, 'restant' => $total - $paye];
        }

        return view('admin.paiement_stats', compact(
            'stats', 'labels', 'payes', 'partiels', 'aPayer',
            'topCommandes', 'caParPoste'
        ));
    }

    /**
     * Journal d'audit — toutes les opérations critiques enregistrées.
     */
    public function auditLogs(Request $request)
    {
        $module = $request->input('module');
        $query = \App\Services\AuditService::query($module, null, 200);
        $logs = $query->paginate(50);

        $modules = AuditLog::distinct()->pluck('module')->filter()->values();

        return view('admin.audit_logs', compact('logs', 'modules', 'module'));
    }
}
