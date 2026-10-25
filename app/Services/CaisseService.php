<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\ServiceFourni;
use App\Models\Retrait;
use Illuminate\Support\Collection;

class CaisseService
{
    public function __construct(
        public readonly int $superviseurId,
        public readonly string $typeModule, // 'directrice' ou 'secretaire'
    ) {}

    /**
     * Calculer le solde complet de la caisse.
     */
    public function solde(): array
    {
        // Une seule requête pour les 4 totaux (agrégats conditionnels)
        $row = Commande::where('superviseur_id', $this->superviseurId)
            ->selectRaw("SUM(CASE WHEN statut_paiement != 'annule' THEN montant END) as total_commandes")
            ->selectRaw("SUM(CASE WHEN statut_paiement != 'annule' THEN montant_paye END) as total_commandes_paye")
            ->selectRaw("SUM(CASE WHEN statut_paiement = 'annule' THEN montant_paye END) as remboursements")
            ->first();
        $totalCommandes     = (float) ($row->total_commandes ?? 0);
        $totalCommandesPaye = (float) ($row->total_commandes_paye ?? 0);
        $remboursements     = (float) ($row->remboursements ?? 0);

        // Total caisse (commandes + services + retraits) en deux requêtes agrégées
        $totalServices = ServiceFourni::where('superviseur_id', $this->superviseurId)->sum('montant');
        $totalRetraits = Retrait::where('superviseur_id', $this->superviseurId)->sum('montant');

        // Remboursements des commandes annulées (montant payé retourné en négatif)
        // Seul le montant PAYÉ entre en caisse, moins les remboursements
        $enCaisse = (float) $totalCommandesPaye + (float) $totalServices - (float) $totalRetraits - (float) $remboursements;

        // Les impayés = ce qui est dû (affiché en négatif)
        $impayeTotal = (float) $totalCommandes - (float) $totalCommandesPaye;

        return [
            'total_commandes' => (float) $totalCommandes,
            'total_commandes_paye' => (float) $totalCommandesPaye,
            'total_services' => (float) $totalServices,
            'total_retraits' => (float) $totalRetraits,
            'remboursements' => (float) $remboursements,
            'en_caisse' => $enCaisse,
            'impaye_total' => $impayeTotal,
        ];
    }

    /**
     * Commandes impayées (partielles + à payer) — SANS les retraits.
     */
    public function commandesImpayees(): Collection
    {
        return Commande::where('superviseur_id', $this->superviseurId)
            ->whereNotIn('statut_paiement', ['paye', 'annule'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Total restant à encaisser (uniquement commandes, pas les retraits).
     */
    public function resteAEncaisser(): float
    {
        return Commande::where('superviseur_id', $this->superviseurId)
            ->whereNotIn('statut_paiement', ['paye', 'annule'])
            ->sum(\Illuminate\Support\Facades\DB::raw('montant - montant_paye'));
    }

    /**
     * Détail par catégorie des commandes (toutes périodes).
     */
    public function commandesParCategorie(): array
    {
        // Agrégats groupés côté SQL (au lieu de charger toute la table)
        return Commande::where('superviseur_id', $this->superviseurId)
            ->selectRaw('type, SUM(montant) as total, SUM(montant_paye) as paye, COUNT(*) as cnt')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn($r) => [$r->type => [
                'total' => (float) $r->total,
                'paye' => (float) $r->paye,
                'count' => (int) $r->cnt,
            ]])
            ->toArray();
    }

    /**
     * Détail par catégorie des services (toutes périodes).
     */
    public function servicesParCategorie(): array
    {
        return ServiceFourni::where('superviseur_id', $this->superviseurId)
            ->selectRaw('type, SUM(montant) as total, COUNT(*) as cnt')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn($r) => [$r->type => [
                'total' => (float) $r->total,
                'count' => (int) $r->cnt,
            ]])
            ->toArray();
    }

    /**
     * Données graphique 7 derniers jours.
     */
    public function evolution7jours(): array
    {
        $labels = [];
        $entrees = [];
        $sorties = [];

        $debut = now()->subDays(6)->toDateString();

        // 3 requêtes groupées (au lieu de 21 requêtes journalières)
        $cmdParJour = Commande::where('superviseur_id', $this->superviseurId)
            ->where('date', '>=', $debut)
            ->selectRaw('date, SUM(montant_paye) as total')
            ->groupBy('date')->pluck('total', 'date');
        $svcParJour = ServiceFourni::where('superviseur_id', $this->superviseurId)
            ->where('date', '>=', $debut)
            ->selectRaw('date, SUM(montant) as total')
            ->groupBy('date')->pluck('total', 'date');
        $retParJour = Retrait::where('superviseur_id', $this->superviseurId)
            ->where('date', '>=', $debut)
            ->selectRaw('date, SUM(montant) as total')
            ->groupBy('date')->pluck('total', 'date');

        for ($i = 6; $i >= 0; $i--) {
            $jour = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d/m');

            $entrees[] = (float) ($cmdParJour[$jour] ?? 0) + (float) ($svcParJour[$jour] ?? 0);
            $sorties[] = (float) ($retParJour[$jour] ?? 0);
        }

        return compact('labels', 'entrees', 'sorties');
    }

    /**
     * Répartition par type (toutes périodes).
     */
    public function repartitionTypes(): array
    {
        $commandesParType = Commande::where('superviseur_id', $this->superviseurId)
            ->selectRaw('type, SUM(montant) as total')
            ->groupBy('type')->pluck('total', 'type');

        $servicesParType = ServiceFourni::where('superviseur_id', $this->superviseurId)
            ->selectRaw('type, SUM(montant) as total')
            ->groupBy('type')->pluck('total', 'type');

        return compact('commandesParType', 'servicesParType');
    }

    /**
     * Dernières transactions (commandes + services + retraits).
     */
    public function dernieresTransactions(int $limit = 10): array
    {
        $derniersCommandes = Commande::where('superviseur_id', $this->superviseurId)
            ->orderByDesc('created_at')->take($limit)->get();
        $derniersServices = ServiceFourni::where('superviseur_id', $this->superviseurId)
            ->orderByDesc('created_at')->take($limit)->get();
        $derniersRetraits = Retrait::where('superviseur_id', $this->superviseurId)
            ->orderByDesc('created_at')->take($limit)->get();

        return compact('derniersCommandes', 'derniersServices', 'derniersRetraits');
    }

    /**
     * Historique encaissements (audit logs).
     */
    public function historiqueEncaissements(int $limit = 10): Collection
    {
        return \App\Models\AuditLog::where('module', $this->typeModule)
            ->where('action', "{$this->typeModule}.update")
            ->where('description', 'like', '%Encaissement%')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Statistiques retraits pour la page retraits.
     */
    public function retraitStats(): array
    {
        // Totaux en SQL (au lieu de charger tous les retraits)
        $base = Retrait::where('superviseur_id', $this->superviseurId);
        $totalRetraits = (float) (clone $base)->sum('montant');
        $countRetraits = (int) (clone $base)->count();

        // Retraits des 30 derniers jours — requête DB groupée (rapide)
        $retraits30j = Retrait::where('superviseur_id', $this->superviseurId)
            ->where('date', '>=', now()->subDays(29)->toDateString())
            ->selectRaw('date, SUM(montant) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->map(fn($v) => (float) $v)
            ->toArray();

        $labels30j = [];
        $data30j = [];
        for ($i = 29; $i >= 0; $i--) {
            $jour = now()->subDays($i)->toDateString();
            $labels30j[] = now()->subDays($i)->format('d/m');
            $data30j[] = $retraits30j[$jour] ?? 0.0;
        }

        // Top motifs (agrégats groupés côté SQL)
        $topMotifs = Retrait::where('superviseur_id', $this->superviseurId)
            ->selectRaw('motif, SUM(montant) as total, COUNT(*) as cnt')
            ->groupBy('motif')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'motif' => $r->motif,
                'total' => (float) $r->total,
                'count' => (int) $r->cnt,
            ])
            ->values();

        // Moyenne par retrait
        $moyenne = $countRetraits > 0 ? $totalRetraits / $countRetraits : 0;

        // Plus gros retrait (requête d'agrégat, sans charger la liste)
        $maxRetrait = (float) (clone $base)->max('montant');

        return compact(
            'totalRetraits', 'countRetraits', 'moyenne', 'maxRetrait',
            'labels30j', 'data30j', 'topMotifs'
        );
    }

    /**
     * Données pour le dashboard complet.
     */
    public function dashboardData(): array
    {
        $solde = $this->solde();
        $graphique = $this->evolution7jours();
        $repartition = $this->repartitionTypes();
        $transactions = $this->dernieresTransactions();
        $impayees = $this->commandesImpayees();
        $reste = $this->resteAEncaisser();
        $historique = $this->historiqueEncaissements();
        $cmdCateg = $this->commandesParCategorie();
        $svcCateg = $this->servicesParCategorie();

        return array_merge($solde, $graphique, $repartition, $transactions, [
            'commandesImpayees' => $impayees,
            'resteAEncaisser' => $reste,
            'historiqueEncaissements' => $historique,
            'sommeEnCaisse' => $solde['en_caisse'],
            'impayeTotal' => $solde['impaye_total'],
            'totalCommandes' => $solde['total_commandes'],
            'totalServices' => $solde['total_services'],
            'totalRetraits' => $solde['total_retraits'],
            'labels7j' => $graphique['labels'],
            'dataEntrees7j' => $graphique['entrees'],
            'dataSorties7j' => $graphique['sorties'],
            'commandesParCategorie' => $cmdCateg,
            'servicesParCategorie' => $svcCateg,
        ]);
    }
}
