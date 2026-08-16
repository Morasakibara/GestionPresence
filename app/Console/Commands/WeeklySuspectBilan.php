<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Presence;
use App\Models\Utilisateur;
use App\Notifications\BilanHebdoNotification;
use Illuminate\Support\Facades\DB;

class WeeklySuspectBilan extends Command
{
    protected $signature = 'presence:bilan-hebdo';
    protected $description = 'Envoie à l\'administrateur un bilan hebdomadaire des présences suspectes';

    public function handle()
    {
        $start = now()->startOfWeek()->subWeek();   // début de la semaine précédente
        $end = now()->startOfWeek();                // fin (exclusive)

        $rows = DB::table('presence')
            ->where('suspect', true)
            ->whereBetween('created_at', [$start, $end])
            ->select('statut_traitement', DB::raw('count(*) as total'))
            ->groupBy('statut_traitement')
            ->pluck('total', 'statut_traitement');

        $total = $rows->sum();

        $periode = $start->format('d/m/Y') . ' au ' . $end->copy()->subDay()->format('d/m/Y');

        if ($total === 0) {
            $this->info('Aucune présence suspecte sur la semaine du ' . $periode . '.');

            return 0;
        }

        $lignes = [
            'Total de présences suspectes'       => $total,
            'En attente (nouveau)'               => $rows['nouveau'] ?? 0,
            'Examinées'                          => $rows['examiné'] ?? 0,
            'Justifiées'                         => $rows['justifié'] ?? 0,
            'Rejetées'                           => $rows['rejeté'] ?? 0,
        ];

        // Détails pour le PDF joint (présences suspectes de la semaine) — stdClass sérialisables JSON
        $details = DB::table('presence')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->where('presence.suspect', true)
            ->whereBetween('presence.created_at', [$start, $end])
            ->select('utilisateur.nom as employer_nom', 'presence.date', 'presence.statut_traitement', 'presence.motif_suspicion')
            ->orderByDesc('presence.date')
            ->get();

        $data = [
            'periode'  => $periode,
            'total'    => $total,
            'nouveau'  => $rows['nouveau'] ?? 0,
            'examine'  => $rows['examiné'] ?? 0,
            'justifie' => $rows['justifié'] ?? 0,
            'rejete'   => $rows['rejeté'] ?? 0,
            'lignes'   => $lignes,
            'details'  => $details,
        ];

        $admin = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();

        if (!$admin) {
            $this->error('Aucun administrateur trouvé pour recevoir le bilan.');

            return 1;
        }

        $admin->notify(new BilanHebdoNotification($data));
        $this->info('Bilan hebdomadaire envoyé : ' . $total . ' présence(s) suspecte(s).');

        return 0;
    }
}
