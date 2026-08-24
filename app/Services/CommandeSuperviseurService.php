<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Retrait;
use App\Services\AuditService;
use Illuminate\Http\Request;

/**
 * Service réutilisable pour la gestion des commandes
 * des postes spécialisés (directrice + secrétaire).
 *
 * Centralise : CRUD commandes, encaissement reste, rapport financier CSV.
 */
class CommandeSuperviseurService
{
    public function __construct(
        private readonly int $superviseurId,
        private readonly string $typePoste, // 'directrice' | 'secretaire'
        private readonly array $typesAutorises,
    ) {}

    /**─────────────────────────── LISTE ───────────────────────────*/

    public function toutesLesCommandes()
    {
        return Commande::where('superviseur_id', $this->superviseurId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**─────────────────────── ENREGISTREMENT ──────────────────────*/

    public function enregistrerCommande(Request $request): Commande
    {
        $request->validate([
            'type'             => 'required|string|in:' . implode(',', array_keys($this->typesAutorises)),
            'montant'          => 'required|numeric|min:0.01',
            'montant_paye'     => 'required|numeric|min:0',
            'statut_paiement'  => 'required|string|in:paye,partiel,a_payer',
            'details'          => 'nullable|string|max:1000',
        ]);

        $montant    = (float) $request->montant;
        $montantPaye = (float) $request->montant_paye;
        $statut     = $request->statut_paiement;

        if ($statut === 'paye' && $montantPaye < $montant) {
            $montantPaye = $montant;
        }
        if ($statut === 'a_payer') {
            $montantPaye = 0;
        }

        $cmd = Commande::create([
            'superviseur_id'   => $this->superviseurId,
            'type'             => $request->type,
            'montant'          => $montant,
            'montant_paye'     => $montantPaye,
            'statut_paiement'  => $statut,
            'details'          => $request->details,
            'date'             => now()->toDateString(),
        ]);

        AuditService::created($this->typePoste, "Commande {$request->type} de {$montant} FCFA ({$statut})", $cmd->toArray(), $request);

        return $cmd;
    }

    /**─────────────────── ENCAISSEMENT RESTE ──────────────────────*/

    public function encaisserReste(Request $request): array
    {
        $request->validate([
            'commande_id'  => 'required|exists:commandes,id',
            'montant_verse' => 'required|numeric|min:0.01',
        ]);

        $commande = Commande::where('id', $request->commande_id)
            ->where('superviseur_id', $this->superviseurId)
            ->firstOrFail();

        $reste = (float) $commande->montant - (float) $commande->montant_paye;
        if ($request->montant_verse > $reste) {
            return ['success' => false, 'error' => "Montant supérieur au reste à encaisser (" . number_format($reste, 0, ',', '') . " FCFA)."];
        }

        $newMontantPaye = (float) $commande->montant_paye + (float) $request->montant_verse;
        $newStatut = $newMontantPaye >= (float) $commande->montant ? 'paye' : 'partiel';

        $commande->update([
            'montant_paye'    => $newMontantPaye,
            'statut_paiement' => $newStatut,
        ]);

        // Notifier l'admin (try-catch pour ne pas bloquer si l'envoi-mail échoue)
        try {
            $admin = \App\Models\Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
            if ($admin) {
                $admin->notify(new \App\Notifications\PaiementCommandeNotification($commande, (float) $request->montant_verse, $newStatut));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Notification paiement échouée: ' . $e->getMessage());
        }

        $msg = $newStatut === 'paye'
            ? "Commande #{$commande->id} entièrement payée."
            : "Reste de " . number_format($request->montant_verse, 0, ',', '.') . " FCFA encaissé sur commande #{$commande->id}.";

        return ['success' => true, 'message' => $msg];
    }

    /**─────────────────────── MISE À JOUR ─────────────────────────*/

    public function modifierCommande(Request $request, int $id): Commande
    {
        $request->validate([
            'montant_paye'    => 'required|numeric|min:0',
            'statut_paiement' => 'required|string|in:paye,partiel,a_payer',
            'details'         => 'nullable|string|max:1000',
        ]);

        $commande = Commande::where('id', $id)
            ->where('superviseur_id', $this->superviseurId)
            ->firstOrFail();

        $montantPaye = (float) $request->montant_paye;
        $statut = $request->statut_paiement;

        // Sécurité : ne jamais diminuer le montant déjà payé
        if ($montantPaye < (float) $commande->montant_paye) {
            $montantPaye = (float) $commande->montant_paye;
        }

        $commande->update([
            'montant_paye'    => $montantPaye,
            'statut_paiement' => $statut,
            'details'         => $request->details,
        ]);

        return $commande;
    }

    public function supprimerCommande(int $id): void
    {
        Commande::where('id', $id)
            ->where('superviseur_id', $this->superviseurId)
            ->firstOrFail()
            ->delete();
    }

    /**─────────────────── DONNÉES DASHBOARD ───────────────────────*/

    public function dashboardData(): array
    {
        $caisse = new CaisseService($this->superviseurId, $this->typePoste);
        $data = $caisse->dashboardData();

        $today = now()->toDateString();
        $commandesJour = Commande::where('superviseur_id', $this->superviseurId)
            ->whereDate('date', $today)->get();

        $data['commandesParType'] = $commandesJour->groupBy('type')->map(fn($c) => $c->sum('montant'));
        $data['typesCaisse'] = $this->typesAutorises;

        return $data;
    }

    /**─────────────────── RAPPORT FINANCIER ───────────────────────*/

    public function rapportData(string $dateDebut, string $dateFin, array $filters = []): array
    {
        // ── Commandes avec filtres ──
        $cmdQuery = Commande::where('superviseur_id', $this->superviseurId)
            ->whereBetween('date', [$dateDebut, $dateFin]);
        if (!empty($filters['type'])) {
            $cmdQuery->where('type', $filters['type']);
        }
        if (!empty($filters['statut'])) {
            $cmdQuery->where('statut_paiement', $filters['statut']);
        }
        if (!empty($filters['montant_min'])) {
            $cmdQuery->where('montant', '>=', $filters['montant_min']);
        }
        if (!empty($filters['montant_max'])) {
            $cmdQuery->where('montant', '<=', $filters['montant_max']);
        }
        $commandes = $cmdQuery->get();

        // ── Services avec filtres ──
        $svcQuery = \App\Models\ServiceFourni::where('superviseur_id', $this->superviseurId)
            ->whereBetween('date', [$dateDebut, $dateFin]);
        if (!empty($filters['type'])) {
            $svcQuery->where('type', $filters['type']);
        }
        if (!empty($filters['montant_min'])) {
            $svcQuery->where('montant', '>=', $filters['montant_min']);
        }
        if (!empty($filters['montant_max'])) {
            $svcQuery->where('montant', '<=', $filters['montant_max']);
        }
        $services = $svcQuery->get();

        // ── Retraits (pas de filtre type, juste montant) ──
        $rtQuery = Retrait::where('superviseur_id', $this->superviseurId)
            ->whereBetween('date', [$dateDebut, $dateFin]);
        if (!empty($filters['montant_min'])) {
            $rtQuery->where('montant', '>=', $filters['montant_min']);
        }
        if (!empty($filters['montant_max'])) {
            $rtQuery->where('montant', '<=', $filters['montant_max']);
        }
        $retraits = $rtQuery->get();

        $totalCommandes = $commandes->sum('montant');
        $totalServices  = $services->sum('montant');
        $totalRetraits  = $retraits->sum('montant');

        $commandesParType = $commandes->groupBy('type')->map(fn($c) => $c->sum('montant'));
        $servicesParType  = $services->groupBy('type')->map(fn($s) => $s->sum('montant'));

        // Données journalières pour graphique
        $detailJournalier = [];
        $start = \Carbon\Carbon::parse($dateDebut);
        $end   = \Carbon\Carbon::parse($dateFin);
        while ($start->lte($end)) {
            $d = $start->toDateString();
            $detailJournalier[] = [
                'label'   => $start->format('d/m'),
                'entrees' => $commandes->where('date', $d)->sum('montant')
                           + $services->where('date', $d)->sum('montant'),
                'sorties' => $retraits->where('date', $d)->sum('montant'),
            ];
            $start->addDay();
        }

        return compact(
            'commandes', 'services', 'retraits',
            'totalCommandes', 'totalServices', 'totalRetraits',
            'commandesParType', 'servicesParType', 'detailJournalier'
        );
    }

    /**─────────────────── EXPORT CSV ──────────────────────────────*/

    public function rapportCsvData(string $dateDebut, string $dateFin): array
    {
        return $this->rapportData($dateDebut, $dateFin);
    }
}
