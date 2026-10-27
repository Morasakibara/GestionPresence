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
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFinanceController extends Controller
{
    /**
     * Vue admin : état de la caisse (directrice + secrétaire).
     *
     * Agrège TOUS les superviseurs d'un même type (plusieurs comptes « secretaire »
     * peuvent exister) et accepte un filtre de date (?date=YYYY-MM-DD). Sans données
     * pour le jour choisi, on affiche aussi le cumul historique pour que la page ne
     * soit jamais vide alors que la base contient des écritures.
     */
    public function etatCaisse(Request $request)
    {
        $date = $request->query('date');
        // Repli sur aujourd'hui si la date est absente ou invalide.
        if (! $date || ! \DateTime::createFromFormat('Y-m-d', $date)) {
            $date = now()->toDateString();
        }

        $libelles = ['directrice' => 'Directrice', 'secretaire' => 'Secrétaire'];
        $data = [];

        foreach ($libelles as $type => $label) {
            // Tous les superviseurs de ce type (la base peut en contenir plusieurs).
            $supIds = Superviseur::where('type_superviseur', $type)->pluck('id');
            if ($supIds->isEmpty()) {
                continue;
            }

            $nom = Utilisateur::whereIn('id', $supIds)->pluck('nom')->implode(', ');

            $entreesJour = Commande::whereIn('superviseur_id', $supIds)->whereDate('date', $date)->sum('montant')
                + ServiceFourni::whereIn('superviseur_id', $supIds)->whereDate('date', $date)->sum('montant');
            $sortiesJour = Retrait::whereIn('superviseur_id', $supIds)->whereDate('date', $date)->sum('montant');

            $entreesTotal = Commande::whereIn('superviseur_id', $supIds)->sum('montant')
                + ServiceFourni::whereIn('superviseur_id', $supIds)->sum('montant');
            $sortiesTotal = Retrait::whereIn('superviseur_id', $supIds)->sum('montant');

            $data[$type] = [
                'nom' => $nom ?: $label,
                'entrees' => (float) $entreesJour,
                'sorties' => (float) $sortiesJour,
                'chiffre_affaire' => (float) ($entreesJour - $sortiesJour),
                'entrees_total' => (float) $entreesTotal,
                'sorties_total' => (float) $sortiesTotal,
                'chiffre_affaire_total' => (float) ($entreesTotal - $sortiesTotal),
            ];
        }

        return view('admin.etat_caisse', compact('data', 'date'));
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
        if ($secretaire) {
            $supIds->push($secretaire->id);
        }
        if ($directrice) {
            $supIds->push($directrice->id);
        }

        $paiements = Commande::when($supIds->isNotEmpty(), fn ($q) => $q->whereIn('superviseur_id', $supIds))
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
            $dayQuery = Commande::when($supIds->isNotEmpty(), fn ($q) => $q->whereIn('superviseur_id', $supIds))
                ->whereDate('created_at', $jour);
            $payes[] = (clone $dayQuery)->where('statut_paiement', 'paye')->count();
            $partiels[] = (clone $dayQuery)->where('statut_paiement', 'partiel')->count();
            $aPayer[] = (clone $dayQuery)->where('statut_paiement', 'a_payer')->count();
        }

        $topCommandes = Commande::when($supIds->isNotEmpty(), fn ($q) => $q->whereIn('superviseur_id', $supIds))
            ->orderByDesc('montant')
            ->limit(10)
            ->get();

        $caParPoste = [];
        foreach (['directrice' => 'Imprimerie', 'secretaire' => 'Photo'] as $type => $label) {
            $sup = Superviseur::where('type_superviseur', $type)->first();
            if (! $sup) {
                continue;
            }
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
        $query = AuditService::query($module, null, 200);
        $logs = $query->paginate(50);

        $modules = AuditLog::distinct()->pluck('module')->filter()->values();

        return view('admin.audit_logs', compact('logs', 'modules', 'module'));
    }
}
