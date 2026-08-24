<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\Presence;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminReportController extends Controller
{
    public function showGenerateReportForm()
    {
        return view('admin.generateReport');
    }

    /**
     * Générer le rapport et exporter en PDF
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'export_format' => 'required|string',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $presences = DB::table('presence')
            ->join('employer', 'presence.employerID', '=', 'employer.id')
            ->join('utilisateur', 'employer.id', '=', 'utilisateur.id')
            ->whereBetween('presence.date', [$startDate, $endDate])
            ->where('presence.status', 'présent')
            ->select('utilisateur.nom as employer_nom', 'presence.employerID')
            ->get();

        $reportData = [];
        foreach ($presences->groupBy('employer_nom') as $nom => $group) {
            $employerID = (int) $group->first()->employerID;
            $rendements = Presence::where('employerID', $employerID)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereNotNull('rendement')
                ->where('rendement', '!=', '')
                ->orderBy('date')
                ->pluck('rendement')
                ->toArray();

            $presencesPeriode = Presence::where('employerID', $employerID)
                ->whereBetween('date', [$startDate, $endDate])
                ->get(['heureArrivee', 'heureDepart']);
            $totalMinutes = $presencesPeriode->sum(fn ($p) => \App\Services\EvaluationService::minutesTravail($p->heureArrivee, $p->heureDepart));
            $totalHeures = \App\Services\EvaluationService::formaterDureeTotale($totalMinutes);

            $evaluation = \App\Services\EvaluationService::evaluer($employerID, $startDate, $endDate);

            $historique = [];
            for ($i = 5; $i >= 0; $i--) {
                $mois = now()->subMonths($i);
                $eval = \App\Services\EvaluationService::evaluer(
                    $employerID,
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

            $reportData[] = (object) [
                'employer_nom' => $nom,
                'employerID' => $employerID,
                'total_presence' => $group->count(),
                'total_heures' => $totalHeures,
                'rendements' => $rendements,
                'evaluation_note' => $evaluation['note'],
                'evaluation_couleur' => $evaluation['couleur'],
                'evaluation_commentaire' => $evaluation['commentaire'],
                'evaluation_manuelle' => $evaluation['manuelle'],
                'historique' => $historique,
            ];
        }

        usort($reportData, fn ($a, $b) => $b->total_presence <=> $a->total_presence);
        $reportData = collect($reportData);

        if ($request->export_format === 'pdf') {
            return $this->exportToPDF($reportData, $startDate, $endDate);
        }

        return view('admin.report', compact('reportData', 'startDate', 'endDate'));
    }

    /**
     * Exporter le rapport en PDF
     * @param \Illuminate\Support\Collection|array $reportData
     */
    public function exportToPDF($reportData, ?string $startDate = null, ?string $endDate = null)
    {
        /** @var Utilisateur|null $admin */
        $admin = Auth::user();

        if (!$admin || $admin->role !== 'Administrateur') {
            return redirect()->back()->with('error', 'Vous devez être un administrateur pour générer un rapport.');
        }

        $adminInfo = DB::table('administrateur')->where('id', $admin->id)->first();

        if (!$adminInfo) {
            return redirect()->back()->with('error', 'Informations de l\'administrateur non trouvées.');
        }

        $pdf = Pdf::loadView('admin.report_pdf', [
            'reportData' => $reportData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'admin' => $admin->nom,
            'generatedDate' => now()->format('d/m/Y'),
        ]);

        $filename = 'rapport_presence_' . str_replace('-', '_', $startDate) . '_' . str_replace('-', '_', $endDate) . '.pdf';
        $pdfPath = 'rapports/' . $filename;
        Storage::disk('public')->put($pdfPath, $pdf->output());

        $rapport = new \App\Models\Rapport();
        $rapport->Adm_id = $admin->id;
        $rapport->periode = now()->format('Y-m-d H:i:s');
        $rapport->contenu = $pdfPath;
        $rapport->created_at = now();
        $rapport->updated_at = now();

        if (DB::getSchemaBuilder()->getColumnListing('rapport')[2] === 'Sup_id') {
            $superviseur = DB::table('superviseur')->first();
            if ($superviseur) {
                $rapport->Sup_id = $superviseur->id;
            } else {
                return redirect()->back()->with('error', 'Aucun superviseur trouvé dans le système. Impossible d\'enregistrer le rapport.');
            }
        }

        $rapport->save();

        return $pdf->download($filename);
    }

    public function exportReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $presences = DB::table('presence')
            ->join('employer', 'presence.employerID', '=', 'employer.id')
            ->join('utilisateur', 'employer.id', '=', 'utilisateur.id')
            ->whereBetween('presence.date', [$startDate, $endDate])
            ->where('presence.status', 'présent')
            ->select('utilisateur.nom as employer_nom', 'presence.employerID')
            ->get();

        $reportData = [];
        foreach ($presences->groupBy('employer_nom') as $nom => $group) {
            $employerID = (int) $group->first()->employerID;
            $rendements = Presence::where('employerID', $employerID)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereNotNull('rendement')
                ->where('rendement', '!=', '')
                ->orderBy('date')
                ->pluck('rendement')
                ->toArray();

            $presencesPeriode = Presence::where('employerID', $employerID)
                ->whereBetween('date', [$startDate, $endDate])
                ->get(['heureArrivee', 'heureDepart']);
            $totalMinutes = $presencesPeriode->sum(fn ($p) => \App\Services\EvaluationService::minutesTravail($p->heureArrivee, $p->heureDepart));
            $totalHeures = \App\Services\EvaluationService::formaterDureeTotale($totalMinutes);

            $evaluation = \App\Services\EvaluationService::evaluer($employerID, $startDate, $endDate);

            $reportData[] = (object) [
                'employer_nom' => $nom,
                'employerID' => $employerID,
                'total_presence' => $group->count(),
                'total_heures' => $totalHeures,
                'rendements' => $rendements,
                'evaluation_note' => $evaluation['note'],
                'evaluation_couleur' => $evaluation['couleur'],
                'evaluation_commentaire' => $evaluation['commentaire'],
                'evaluation_manuelle' => $evaluation['manuelle'],
            ];
        }

        usort($reportData, fn ($a, $b) => $b->total_presence <=> $a->total_presence);
        $reportData = collect($reportData);

        return $this->exportToPDF($reportData, $startDate, $endDate);
    }

    /**
     * Export CSV des évaluations et des rendements par employé.
     */
    public function exportEvaluationsCsv(Request $request)
    {
        $mois = $request->input('mois', now()->format('Y-m'));
        $debut = $mois . '-01';
        $fin = now()->parse($debut)->endOfMonth()->toDateString();

        $employes = DB::table('employer')
            ->join('utilisateur', 'employer.id', '=', 'utilisateur.id')
            ->select('employer.id', 'utilisateur.nom')
            ->orderBy('utilisateur.nom')
            ->get();

        $lignes = [];
        $lignes[] = ['Employé', 'Mois', 'Note /20', 'Couleur', 'Commentaire', 'Heures travaillées', 'Rendements du mois'];

        foreach ($employes as $employe) {
            $evaluation = \App\Services\EvaluationService::evaluer($employe->id, $debut, $fin);

            $presencesPeriode = Presence::where('employerID', $employe->id)
                ->whereBetween('date', [$debut, $fin])
                ->get(['heureArrivee', 'heureDepart']);
            $totalMinutes = $presencesPeriode->sum(fn ($p) => \App\Services\EvaluationService::minutesTravail($p->heureArrivee, $p->heureDepart));
            $totalHeures = \App\Services\EvaluationService::formaterDureeTotale($totalMinutes);

            $rendements = Presence::where('employerID', $employe->id)
                ->whereBetween('date', [$debut, $fin])
                ->whereNotNull('rendement')
                ->where('rendement', '!=', '')
                ->orderBy('date')
                ->get(['date', 'rendement'])
                ->map(fn ($r) => $r->date . ' : ' . $r->rendement)
                ->implode("\n");

            $lignes[] = [
                $employe->nom,
                $mois,
                number_format($evaluation['note'], 1, ',', ''),
                $evaluation['couleur'],
                $evaluation['commentaire'],
                $totalHeures,
                $rendements,
            ];
        }

        $service = \App\Services\ExcelExportService::creer(
            'Évaluations & rendements — Le Pharaon',
            'Mois de ' . ucfirst(\Carbon\Carbon::parse($debut)->locale('fr')->isoFormat('MMMM YYYY')),
            ['Employé', 'Mois', 'Note /20', 'Couleur', 'Commentaire', 'Heures travaillées', 'Rendements du mois'],
            $lignes
        );

        $ligneEnTete = 5;
        foreach ($employes as $i => $employe) {
            $evaluation = \App\Services\EvaluationService::evaluer($employe->id, $debut, $fin);
            $couleur = match ($evaluation['couleur']) {
                'vert' => \App\Services\ExcelExportService::SUCCES,
                'orange' => \App\Services\ExcelExportService::ALERTE,
                'rouge' => \App\Services\ExcelExportService::DANGER,
                default => \App\Services\ExcelExportService::NOIR,
            };
            $service->colorerCellule($ligneEnTete + 1 + $i, 3, $couleur);
            $service->colorerCellule($ligneEnTete + 1 + $i, 4, $couleur);
        }

        $contenu = $service->contenu();

        return response($contenu, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename=evaluations_rendements_' . $mois . '.xlsx',
        ]);
    }

    /**
     * Export CSV de l'évolution mensuelle des évaluations (6 mois).
     */
    public function exportEvolutionEvaluationsCsv(Request $request)
    {
        $equipe = $request->query('equipe');
        $employerIds = null;

        if ($equipe) {
            $employerIds = \App\Models\Employer::where('equipe', $equipe)->pluck('id')->toArray();
        }

        $evolution = \App\Services\EvaluationService::evolutionMensuelle($employerIds, 6);

        $lignes = ["Mois;Note moyenne /20;Couleur"];
        $couleurLibelle = [
            '#2E8B57' => 'Vert (>=14)',
            '#D97706' => 'Orange (10-13)',
            '#D64545' => 'Rouge (<10)',
            '#888888' => 'Aucune donnée',
        ];
        foreach ($evolution['labels'] as $i => $label) {
            $lignes[] = $label . ';' . str_replace('.', ',', (string) $evolution['notes'][$i]) . ';' . ($couleurLibelle[$evolution['couleurs'][$i]] ?? $evolution['couleurs'][$i]);
        }

        $contenu = implode("\n", $lignes) . "\n";
        $nomFichier = 'evolution_evaluations_' . ($equipe ? str_replace(' ', '_', $equipe) : 'entreprise') . '.csv';

        return response($contenu, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=' . $nomFichier,
        ]);
    }

    /**
     * Bulletin individuel d'évaluation PDF pour un employé et un mois.
     */
    public function evaluationBulletin(Request $request, int $id)
    {
        /** @var Utilisateur|null $admin */
        $admin = Auth::user();
        if (!$admin || $admin->role !== 'Administrateur') {
            return redirect()->back()->with('error', 'Accès réservé à l\'administrateur.');
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
            return redirect()->back()->with('error', 'Employé non trouvé.');
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

        $pdf = Pdf::loadView('admin.evaluation_bulletin_pdf', compact('employe', 'evaluation', 'stats', 'rendements', 'mois', 'debut', 'fin', 'historique'));
        $filename = 'bulletin_evaluation_' . $mois . '_' . $id . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Enregistre (ou met à jour) une évaluation manuelle pour un employé et un mois.
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
                'evaluateur_id' => Auth::id(),
            ]
        );

        if ($request->couleur === 'rouge') {
            $employe = Utilisateur::find($request->employerID);
            $adminPrincipal = $this->primaryAdmin();
            if ($employe && $adminPrincipal && (int) $adminPrincipal->id !== (int) Auth::id()) {
                $adminPrincipal->notify(new \App\Notifications\EvaluationRougeNotification(
                    $employe->nom,
                    $request->mois,
                    (float) $request->note
                ));
            }
        }

        return redirect()->back()->with('success', 'Évaluation enregistrée.');
    }

    /**
     * Retourne l'administrateur principal (le plus ancien) pour les notifications.
     */
    private function primaryAdmin(): ?\App\Models\Utilisateur
    {
        return \App\Models\Utilisateur::where('role', 'Administrateur')->orderBy('id')->first();
    }
}
