<?php

namespace App\Http\Controllers;

use App\Models\Employer;
use App\Models\Evaluation;
use App\Models\Utilisateur;
use App\Models\Superviseur;
use App\Models\Presence;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SuperviseurEvaluationController extends Controller
{
    /**
     * Helper: récupérer l'équipe du superviseur connecté.
     */
    private function equipeInfo(): array
    {
        $superviseur = auth()->user();
        $superviseurInfo = Superviseur::where('id', $superviseur->id)->first();

        if (!$superviseurInfo) {
            return ['equipe' => null, 'employerIds' => [], 'superviseurInfo' => null];
        }

        $equipe = $superviseurInfo->equipe;
        $employerIds = Employer::where('equipe', $equipe)->pluck('id')->toArray();

        return compact('equipe', 'employerIds', 'superviseurInfo', 'superviseur');
    }

    // ─── RAPPORTS ────────────────────────────────────────────

    public function showGenerateReport()
    {
        return view('superviseur.generateReport2');
    }

    public function generateReport()
    {
        $info = $this->equipeInfo();
        if (!$info['equipe']) {
            return redirect()->back()->with('error', 'Informations du superviseur non trouvées.');
        }

        extract($info); // $equipe, $employerIds, $superviseur

        $users = Utilisateur::whereIn('id', $employerIds)->get();
        $debut = now()->startOfMonth()->toDateString();
        $fin = now()->endOfMonth()->toDateString();
        $reports = [];

        foreach ($users as $user) {
            $totalPresences = Presence::where('employerID', $user->id)
                ->whereBetween('date', [$debut, $fin])
                ->where('status', 'présent')
                ->count();

            $rendements = Presence::where('employerID', $user->id)
                ->whereBetween('date', [$debut, $fin])
                ->whereNotNull('rendement')
                ->where('rendement', '!=', '')
                ->orderBy('date')
                ->pluck('rendement')
                ->toArray();

            $presencesPeriode = Presence::where('employerID', $user->id)
                ->whereBetween('date', [$debut, $fin])
                ->get(['heureArrivee', 'heureDepart']);
            $totalMinutes = $presencesPeriode->sum(fn ($p) => \App\Services\EvaluationService::minutesTravail($p->heureArrivee, $p->heureDepart));
            $totalHeures = \App\Services\EvaluationService::formaterDureeTotale($totalMinutes);

            $evaluation = \App\Services\EvaluationService::evaluer($user->id, $debut, $fin);

            // Historique 6 mois pour le graphique comparatif
            $historique = [];
            for ($i = 5; $i >= 0; $i--) {
                $mois = now()->subMonths($i);
                $eval = \App\Services\EvaluationService::evaluer(
                    $user->id,
                    $mois->copy()->startOfMonth()->toDateString(),
                    $mois->copy()->endOfMonth()->toDateString()
                );
                $historique[] = [
                    'mois' => $mois->format('Y-m'),
                    'label' => ucfirst($mois->locale('fr')->isoFormat('MMMM')),
                    'note' => $eval['note'],
                    'couleur' => $eval['couleur'],
                ];
            }

            $reports[] = [
                'name' => $user->nom,
                'employerID' => $user->id,
                'totalPresences' => $totalPresences,
                'totalHeures' => $totalHeures,
                'rendements' => $rendements,
                'evaluation_note' => $evaluation['note'],
                'evaluation_couleur' => $evaluation['couleur'],
                'evaluation_commentaire' => $evaluation['commentaire'],
                'evaluation_manuelle' => $evaluation['manuelle'],
                'historique' => $historique,
            ];
        }

        usort($reports, fn ($a, $b) => $b['totalPresences'] <=> $a['totalPresences']);

        return view('superviseur.generateReport2', compact('reports'));
    }

    // ─── EXPORTS ─────────────────────────────────────────────

    public function exportPDF()
    {
        $superviseur = auth()->user();

        if (!$superviseur || $superviseur->role !== 'Superviseur') {
            return redirect()->back()->with('error', 'Vous devez être un superviseur pour générer un rapport.');
        }

        $info = $this->equipeInfo();
        if (!$info['equipe']) {
            return redirect()->back()->with('error', 'Informations du superviseur non trouvées.');
        }

        extract($info);

        $debut = now()->startOfMonth()->toDateString();
        $fin = now()->endOfMonth()->toDateString();
        $reports = [];
        $users = Utilisateur::whereIn('id', $employerIds)->get();

        foreach ($users as $user) {
            $totalPresences = Presence::where('employerID', $user->id)
                ->whereBetween('date', [$debut, $fin])
                ->where('status', 'présent')
                ->count();

            $rendements = Presence::where('employerID', $user->id)
                ->whereBetween('date', [$debut, $fin])
                ->whereNotNull('rendement')
                ->where('rendement', '!=', '')
                ->orderBy('date')
                ->pluck('rendement')
                ->toArray();

            $presencesPeriode = Presence::where('employerID', $user->id)
                ->whereBetween('date', [$debut, $fin])
                ->get(['heureArrivee', 'heureDepart']);
            $totalMinutes = $presencesPeriode->sum(fn ($p) => \App\Services\EvaluationService::minutesTravail($p->heureArrivee, $p->heureDepart));
            $totalHeures = \App\Services\EvaluationService::formaterDureeTotale($totalMinutes);

            $evaluation = \App\Services\EvaluationService::evaluer($user->id, $debut, $fin);

            $reports[] = [
                'name' => $user->nom,
                'employerID' => $user->id,
                'totalPresences' => $totalPresences,
                'totalHeures' => $totalHeures,
                'rendements' => $rendements,
                'evaluation_note' => $evaluation['note'],
                'evaluation_couleur' => $evaluation['couleur'],
                'evaluation_commentaire' => $evaluation['commentaire'],
                'evaluation_manuelle' => $evaluation['manuelle'],
            ];
        }

        usort($reports, fn ($a, $b) => $b['totalPresences'] <=> $a['totalPresences']);

        $pdf = PDF::loadView('superviseur.generateReportPDF', [
            'reports' => $reports,
            'equipe' => $equipe,
            'date' => now()->format('d/m/Y'),
            'superviseur' => $superviseur->nom,
        ]);

        $periode = now()->format('Y-m-d H:i:s');
        $filename = 'rapport_equipe_' . $equipe . '_' . now()->format('Y_m_d_His') . '.pdf';
        $pdfPath = 'rapports/' . $filename;
        Storage::disk('public')->put($pdfPath, $pdf->output());

        try {
            $existingReport = \App\Models\Rapport::where([
                'Sup_id' => $superviseur->id,
                'contenu' => $pdfPath,
            ])->first();

            if (!$existingReport) {
                $rapport = new \App\Models\Rapport();
                $rapport->Sup_id = $superviseur->id;
                $rapport->periode = $periode;
                $rapport->contenu = $pdfPath;
                $rapport->created_at = now();
                $rapport->updated_at = now();

                $admin = DB::table('administrateur')->first();
                if ($admin) {
                    $rapport->Adm_id = $admin->id;
                    $rapport->save();
                } else {
                    Log::error('Aucun administrateur trouvé. Impossible d\'enregistrer le rapport.');
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde du rapport: ' . $e->getMessage());
        }

        return $pdf->download($filename);
    }

    // ─── ÉVALUATIONS ─────────────────────────────────────────

    /**
     * Enregistre (ou met à jour) une évaluation manuelle pour un membre de l'équipe et un mois.
     */
    public function storeEvaluation(Request $request)
    {
        $request->validate([
            'employerID' => 'required|integer|exists:employer,id',
            'mois' => 'required|date_format:Y-m',
            'note' => 'required|numeric|min:0|max:20',
            'couleur' => 'required|in:vert,orange,rouge',
            'commentaire' => 'nullable|string|max:2000',
        ]);

        Evaluation::updateOrCreate(
            ['employerID' => $request->employerID, 'mois' => $request->mois],
            [
                'note' => (float) $request->note,
                'couleur' => $request->couleur,
                'commentaire' => $request->commentaire,
                'evaluateur_id' => \Illuminate\Support\Facades\Auth::id(),
            ]
        );

        // Alerte automatique à l'admin principal si l'évaluation passe en rouge
        if ($request->couleur === 'rouge') {
            $employe = Utilisateur::find($request->employerID);
            $adminPrincipal = Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
            if ($employe && $adminPrincipal && (int) $adminPrincipal->id !== (int) \Illuminate\Support\Facades\Auth::id()) {
                $adminPrincipal->notify(new \App\Notifications\EvaluationRougeNotification(
                    $employe->nom,
                    $request->mois,
                    (float) $request->note
                ));
            }
        }

        return redirect()->back()->with('success', 'Évaluation enregistrée.');
    }

    public function evaluationBulletin(Request $request, $id)
    {
        $info = $this->equipeInfo();
        if (!$info['equipe']) {
            return redirect()->back()->with('error', 'Informations du superviseur non trouvées.');
        }

        if (!in_array((int) $id, array_map('intval', $info['employerIds']))) {
            return redirect()->back()->with('error', 'Ce membre ne fait pas partie de votre équipe.');
        }

        $mois = $request->input('mois', now()->format('Y-m'));
        $debut = $mois . '-01';
        $fin = now()->parse($debut)->endOfMonth()->toDateString();

        $employe = DB::table('employer')
            ->join('utilisateur', 'employer.id', '=', 'utilisateur.id')
            ->where('employer.id', $id)
            ->select('employer.id', 'utilisateur.nom')
            ->first();

        if (!$employe) {
            return redirect()->back()->with('error', 'Membre non trouvé.');
        }

        $evaluation = \App\Services\EvaluationService::evaluer($id, $debut, $fin);
        $stats = \App\Services\EvaluationService::statsPeriode($id, $debut, $fin);
        $rendements = Presence::where('employerID', $id)
            ->whereBetween('date', [$debut, $fin])
            ->whereNotNull('rendement')
            ->where('rendement', '!=', '')
            ->orderBy('date')
            ->get(['date', 'heureArrivee', 'heureDepart', 'rendement']);

        $historique = \App\Services\EvaluationService::historiqueMensuel((int) $id, 6);

        $pdf = Pdf::loadView('superviseur.evaluation_bulletin_pdf', compact('employe', 'evaluation', 'stats', 'rendements', 'mois', 'debut', 'fin', 'historique'));
        $filename = 'bulletin_evaluation_' . $mois . '_' . $id . '.pdf';

        return $pdf->download($filename);
    }

    public function exportComparatifPdf()
    {
        $info = $this->equipeInfo();
        if (!$info['equipe']) {
            return redirect()->back()->with('error', 'Informations du superviseur non trouvées.');
        }

        $notesParEmploye = \App\Services\EvaluationService::notesParEmploye($info['employerIds'], 6);

        $pdf = Pdf::loadView('superviseur.comparatif_notes_pdf', [
            'notesParEmploye' => $notesParEmploye,
            'equipe' => $info['equipe'],
            'date' => now()->format('d/m/Y'),
        ]);

        $filename = 'comparatif_notes_' . str_replace(' ', '_', $info['equipe']) . '_' . now()->format('Y_m_d') . '.pdf';
        return $pdf->download($filename);
    }

    // ─── RENDEMENTS ──────────────────────────────────────────

    /**
     * Suivi quotidien des fiches de rendement des membres de l'équipe.
     */
    public function teamRendements(Request $request)
    {
        $info = $this->equipeInfo();
        if (!$info['equipe']) {
            return redirect()->back()->with('error', 'Informations du superviseur non trouvées.');
        }

        $date = $request->input('date', now()->toDateString());

        $rendements = DB::table('presence')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->whereIn('presence.employerID', $info['employerIds'])
            ->whereDate('presence.date', $date)
            ->whereNotNull('presence.rendement')
            ->where('presence.rendement', '!=', '')
            ->select('presence.id', 'presence.date', 'presence.heureArrivee', 'presence.heureDepart', 'presence.rendement', 'utilisateur.nom')
            ->orderBy('presence.heureArrivee')
            ->get();

        $totalMinutes = 0;
        foreach ($rendements as $r) {
            $r->duree = \App\Services\EvaluationService::dureeTravail($r->heureArrivee, $r->heureDepart);
            $totalMinutes += \App\Services\EvaluationService::minutesTravail($r->heureArrivee, $r->heureDepart);
        }
        $totalDuree = \App\Services\EvaluationService::formaterDureeTotale($totalMinutes);

        $membresSansFiche = Utilisateur::whereIn('id', $info['employerIds'])
            ->whereNotIn('id', DB::table('presence')
                ->whereDate('date', $date)
                ->whereNotNull('rendement')
                ->pluck('employerID'))
            ->get(['id', 'nom']);

        return view('superviseur.rendements', compact('rendements', 'membresSansFiche', 'date', 'totalDuree'));
    }

    /**
     * Export CSV des fiches de rendement de l'équipe pour une date (ou période).
     */
    public function exportTeamRendementsCsv(Request $request)
    {
        $info = $this->equipeInfo();
        if (!$info['equipe']) {
            return redirect()->back()->with('error', 'Informations du superviseur non trouvées.');
        }

        $debut = $request->input('debut', now()->toDateString());
        $fin = $request->input('fin', $debut);

        $lignes = [];
        $lignes[] = ['Équipe', 'Employé', 'Date', 'Arrivée', 'Départ', 'Durée', 'Rendement'];

        $rows = DB::table('presence')
            ->join('utilisateur', 'presence.employerID', '=', 'utilisateur.id')
            ->whereIn('presence.employerID', $info['employerIds'])
            ->whereDate('presence.date', '>=', $debut)
            ->whereDate('presence.date', '<=', $fin)
            ->whereNotNull('presence.rendement')
            ->where('presence.rendement', '!=', '')
            ->select('presence.date', 'presence.heureArrivee', 'presence.heureDepart', 'presence.rendement', 'utilisateur.nom')
            ->orderBy('utilisateur.nom')
            ->orderBy('presence.date')
            ->get();

        foreach ($rows as $r) {
            $lignes[] = [
                $info['equipe'],
                $r->nom,
                date('d/m/Y', strtotime($r->date)),
                $r->heureArrivee ? date('H:i', strtotime($r->heureArrivee)) : '',
                $r->heureDepart ? date('H:i', strtotime($r->heureDepart)) : '',
                \App\Services\EvaluationService::dureeTravail($r->heureArrivee, $r->heureDepart) ?? '',
                $r->rendement,
            ];
        }

        $contenu = \App\Services\ExcelExportService::creer(
            'Rendement de l\'équipe ' . $info['equipe'],
            'Période du ' . date('d/m/Y', strtotime($debut)) . ' au ' . date('d/m/Y', strtotime($fin)) . ' — Le Pharaon',
            ['Équipe', 'Employé', 'Date', 'Arrivée', 'Départ', 'Durée', 'Rendement'],
            $lignes
        )->contenu();

        return response($contenu, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename=rendements_equipe_' . $debut . '_' . $fin . '.xlsx',
        ]);
    }

    /**
     * Export CSV de l'évolution mensuelle des évaluations de l'équipe (6 mois).
     */
    public function exportEvolutionEvaluationsCsv(Request $request)
    {
        $info = $this->equipeInfo();
        if (!$info['equipe']) {
            return redirect()->back()->with('error', 'Informations du superviseur non trouvées.');
        }

        $evolution = \App\Services\EvaluationService::evolutionMensuelle($info['employerIds'], 6);

        $lignes = ["Équipe;Mois;Note moyenne /20;Couleur"];
        $couleurLibelle = [
            '#2E8B57' => 'Vert (>=14)',
            '#D97706' => 'Orange (10-13)',
            '#D64545' => 'Rouge (<10)',
            '#888888' => 'Aucune donnée',
        ];
        foreach ($evolution['labels'] as $i => $label) {
            $lignes[] = $info['equipe'] . ';' . $label . ';' . str_replace('.', ',', (string) $evolution['notes'][$i]) . ';' . ($couleurLibelle[$evolution['couleurs'][$i]] ?? $evolution['couleurs'][$i]);
        }

        $contenu = implode("\n", $lignes) . "\n";
        $nomFichier = 'evolution_evaluations_equipe_' . str_replace(' ', '_', $info['equipe']) . '.csv';

        return response($contenu, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=' . $nomFichier,
        ]);
    }
}
