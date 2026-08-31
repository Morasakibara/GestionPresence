<?php

namespace App\Services;

use Illuminate\Http\Response;

class ExportService
{
    /** Séparateur CSV (; pour Excel FR) */
    private const SEP = ';';

    /**
     * Génère un CSV professionnel avec en-tête Le Pharaon.
     * Design aligné sur le PDF : en-tête société, sections structurées, sous-totaux.
     */
    public static function csv(
        string $titre,
        array $colonnes,
        array $lignes,
        string $module = 'Le Pharaon'
    ): Response {
        $bom = "\xEF\xBB\xBF";
        $nbCols = max(count($colonnes), 1);
        $contenu = $bom;

        // ═══ EN-TÊTE SOCIÉTÉ ═══
        $contenu .= "\"LE PHARAON\";\n";
        $contenu .= "\"Système de Gestion de Présence\";\n";
        $contenu .= "\"\";\n";

        // ═══ TITRE RAPPORT ═══
        $contenu .= "\"{$titre}\";\n";
        $contenu .= "\"Module : {$module}\";\n";
        $contenu .= "\"Date d'export : " . now()->format('d/m/Y à H:i') . "\";\n";
        $contenu .= "\"\";\n";

        // ═══ LIGNE SÉPARATRICE ═══
        $contenu .= str_repeat('═', 90) . "\n";

        // ═══ EN-TÊTES DE COLONNES ═══
        $contenu .= implode(self::SEP, array_map(fn($c) => "\"{$c}\"", $colonnes)) . "\n";

        // ═══ LIGNE SÉPARATRICE ═══
        $contenu .= str_repeat('─', 90) . "\n";

        // ═══ DONNÉES ═══
        foreach ($lignes as $ligne) {
            // Compléter les lignes manquantes
            while (count($ligne) < $nbCols) {
                $ligne[] = '';
            }
            $contenu .= self::escapeRow(array_slice($ligne, 0, $nbCols)) . "\n";
        }

        // ═══ PIED DE PAGE ═══
        $contenu .= str_repeat('═', 90) . "\n";
        $contenu .= "\"LE PHARAON — {$module} — Export confidentiel\";\n";
        $contenu .= "\"Généré le " . now()->format('d/m/Y à H:i') . "\";\n";

        return response($contenu, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=' . self::slug($titre) . '_' . now()->format('Y_m_d_His') . '.csv',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Export commandes — style structuré avec sous-totaux par catégorie.
     */
    public static function commandes($commandes, array $typesLabels, string $module): Response
    {
        $colonnes = ['N°', 'Date', 'Type', 'Montant Total', 'Montant Payé', 'Reste à payer', 'Statut', 'Détails'];
        $lignes = [];
        $n = 0;
        $totalMontant = 0;
        $totalPaye = 0;
        $totalReste = 0;
        $byType = [];

        foreach ($commandes as $cmd) {
            $n++;
            $cat = $typesLabels[$cmd->type] ?? $cmd->type;
            $reste = (float) $cmd->montant - (float) $cmd->montant_paye;
            $totalMontant += (float) $cmd->montant;
            $totalPaye += (float) $cmd->montant_paye;
            $totalReste += $reste;
            $byType[$cat] = ($byType[$cat] ?? 0) + (float) $cmd->montant;

            $statutLabel = match($cmd->statut_paiement) {
                'paye' => 'Payé',
                'partiel' => 'Partiel',
                'a_payer' => 'À payer',
                'annule' => 'Annulé',
                default => $cmd->statut_paiement,
            };

            $lignes[] = [
                $n, $cmd->date->format('d/m/Y'), $cat,
                number_format($cmd->montant, 0, ',', ' '), number_format($cmd->montant_paye, 0, ',', ' '),
                number_format($reste, 0, ',', ' '), $statutLabel, $cmd->details ?? '',
            ];
        }

        // ═══ SOUS-TOTAUX PAR CATÉGORIE ═══
        if (!empty($byType)) {
            $lignes[] = array_fill(0, 8, '');
            $lignes[] = ['', '', '═══ RÉPARTITION PAR TYPE ═══', '', '', '', '', ''];
            foreach ($byType as $cat => $total) {
                $lignes[] = ['', '', "  ↳ {$cat}", number_format($total, 0, ',', ' ') . ' FCFA', '', '', '', ''];
            }
        }

        // ═══ SOUS-TOTAL ═══
        $lignes[] = array_fill(0, 8, '');
        $lignes[] = [
            '', '', '═══ SOUS-TOTAL ═══', number_format($totalMontant, 0, ',', ' ') . ' FCFA',
            number_format($totalPaye, 0, ',', ' ') . ' FCFA', number_format($totalReste, 0, ',', ' ') . ' FCFA',
            $n . ' commande(s)', '',
        ];

        // ═══ SYNTHÈSE ═══
        $lignes[] = array_fill(0, 8, '');
        $lignes[] = ['', '', '═══ SYNTHÈSE ═══', '', '', '', '', ''];
        $lignes[] = ['', '', '  Total commandes', number_format($totalMontant, 0, ',', ' ') . ' FCFA', '', '', '', ''];
        $lignes[] = ['', '', '  Montant encaissé', '', number_format($totalPaye, 0, ',', ' ') . ' FCFA', '', '', ''];
        $lignes[] = ['', '', '  Reste à encaisser', '', '', number_format($totalReste, 0, ',', ' ') . ' FCFA', '', ''];

        $titre = "Commandes — {$module} — " . now()->format('d/m/Y');
        return self::csv($titre, $colonnes, $lignes);
    }

    /**
     * Export services — structuré avec sous-totaux par catégorie.
     */
    public static function services($services, array $typesLabels, string $module): Response
    {
        $colonnes = ['N°', 'Date', 'Heure', 'Type', 'Montant', 'Détails'];
        $lignes = [];
        $n = 0;
        $totalMontant = 0;
        $byType = [];

        foreach ($services as $svc) {
            $n++;
            $cat = $typesLabels[$svc->type] ?? $svc->type;
            $totalMontant += (float) $svc->montant;
            $byType[$cat] = ($byType[$cat] ?? 0) + (float) $svc->montant;

            $lignes[] = [
                $n, $svc->date->format('d/m/Y'), $svc->created_at->format('H:i'),
                $cat, number_format($svc->montant, 0, ',', ' ') . ' FCFA',
                $svc->details ?? '',
            ];
        }

        // ═══ SOUS-TOTAUX PAR CATÉGORIE ═══
        if (!empty($byType)) {
            $lignes[] = array_fill(0, 6, '');
            $lignes[] = ['', '', '═══ RÉPARTITION PAR TYPE ═══', '', '', ''];
            foreach ($byType as $cat => $total) {
                $lignes[] = ['', '', "  ↳ {$cat}", number_format($total, 0, ',', ' ') . ' FCFA', '', ''];
            }
        }

        // ═══ SOUS-TOTAL ═══
        $lignes[] = array_fill(0, 6, '');
        $lignes[] = [
            '', '', '═══ SOUS-TOTAL ═══', number_format($totalMontant, 0, ',', ' ') . ' FCFA',
            $n . ' service(s)', '',
        ];

        $titre = "Services — {$module} — " . now()->format('d/m/Y');
        return self::csv($titre, $colonnes, $lignes);
    }

    /**
     * Export retraits — structuré avec sous-total.
     */
    public static function retraits($retraits, string $module): Response
    {
        $colonnes = ['N°', 'Date', 'Heure', 'Motif', 'Montant'];
        $lignes = [];
        $n = 0;
        $totalMontant = 0;

        foreach ($retraits as $r) {
            $n++;
            $totalMontant += (float) $r->montant;
            $lignes[] = [
                $r->id, $r->date->format('d/m/Y'), $r->created_at->format('H:i'),
                $r->motif, '-' . number_format($r->montant, 0, ',', ' ') . ' FCFA',
            ];
        }

        $lignes[] = array_fill(0, 5, '');
        $lignes[] = [
            '', '', '═══ SOUS-TOTAL ═══',
            $n . ' retrait(s)', '-' . number_format($totalMontant, 0, ',', ' ') . ' FCFA',
        ];

        $titre = "Retraits — {$module} — " . now()->format('d/m/Y');
        return self::csv($titre, $colonnes, $lignes);
    }

    /**
     * ══════════════════════════════════════════════════════════════
     * RAPPORT FINANCIER CONSOLIDÉ — MÊME STRUCTURE QUE LE PDF
     * Commandes + Services + Retraits + Sous-totaux + Bilan
     * ══════════════════════════════════════════════════════════════
     */
    public static function rapport(
        string $dateDebut,
        string $dateFin,
        $commandes,
        $services,
        $retraits,
        array $typesLabels,
        string $module
    ): Response {
        $colonnes = ['N°', 'Type Opération', 'Date', 'Heure', 'Catégorie', 'Détails', 'Montant Total', 'Montant Payé', 'Reste', 'Statut'];
        $nbCols = count($colonnes);
        $lignes = [];
        $n = 0;

        // ═══════════════════════ EN-TÊTE ═══════════════════════
        $lignes[] = ['LE PHARAON', '', '', '', '', '', '', '', '', ''];
        $lignes[] = ['RAPPORT FINANCIER — ' . strtoupper($module), '', '', '', '', '', '', '', '', ''];
        $lignes[] = ['Période : ' . $dateDebut . ' au ' . $dateFin, '', '', '', '', '', '', '', '', ''];
        $lignes[] = ['Généré le : ' . now()->format('d/m/Y à H:i'), '', '', '', '', '', '', '', '', ''];
        $lignes[] = array_fill(0, $nbCols, '');

        // ═══════════════════════ COMMANDES ═══════════════════════
        $lignes[] = ['═══ ENTRÉES — COMMANDES ═══', '', '', '', '', '', '', '', '', ''];
        $totalCmdMontant = 0;
        $totalCmdPaye = 0;
        $cmdByType = [];

        foreach ($commandes as $cmd) {
            $n++;
            $totalCmdMontant += (float) $cmd->montant;
            $totalCmdPaye += (float) $cmd->montant_paye;
            $catLabel = $typesLabels[$cmd->type] ?? $cmd->type;
            $cmdByType[$catLabel] = ($cmdByType[$catLabel] ?? 0) + (float) $cmd->montant;
            $statutLabel = match($cmd->statut_paiement) {
                'paye' => 'Payé', 'partiel' => 'Partiel', 'a_payer' => 'À payer', 'annule' => 'Annulé',
                default => $cmd->statut_paiement,
            };
            $lignes[] = [
                $n, 'Commande', $cmd->date->format('d/m/Y'), $cmd->created_at->format('H:i'),
                $catLabel, $cmd->details ?? '',
                number_format($cmd->montant, 0, ',', ' '), number_format($cmd->montant_paye, 0, ',', ' '),
                number_format($cmd->montant - $cmd->montant_paye, 0, ',', ' '), $statutLabel,
            ];
        }

        // Répartition par catégorie commande
        if (!empty($cmdByType)) {
            $lignes[] = array_fill(0, $nbCols, '');
            $lignes[] = ['', '', '── RÉPARTITION COMMANDES ──', '', '', '', '', '', '', ''];
            foreach ($cmdByType as $cat => $total) {
                $lignes[] = ['', '', '', '', "  ↳ {$cat}", '', number_format($total, 0, ',', ' ') . ' FCFA', '', '', ''];
            }
        }

        // Sous-total commandes
        $lignes[] = array_fill(0, $nbCols, '');
        $lignes[] = [
            '', 'SOUS-TOTAL COMMANDES', '', '', '',
            count($commandes) . ' commande(s)',
            number_format($totalCmdMontant, 0, ',', ' ') . ' FCFA',
            number_format($totalCmdPaye, 0, ',', ' ') . ' FCFA',
            number_format($totalCmdMontant - $totalCmdPaye, 0, ',', ' ') . ' FCFA', '',
        ];
        $lignes[] = array_fill(0, $nbCols, '');

        // ═══════════════════════ SERVICES ═══════════════════════
        $lignes[] = ['═══ ENTRÉES — SERVICES FOURNIS ═══', '', '', '', '', '', '', '', '', ''];
        $totalSvcMontant = 0;
        $svcByType = [];

        foreach ($services as $svc) {
            $n++;
            $totalSvcMontant += (float) $svc->montant;
            $catLabel = $typesLabels[$svc->type] ?? $svc->type;
            $svcByType[$catLabel] = ($svcByType[$catLabel] ?? 0) + (float) $svc->montant;
            $lignes[] = [
                $n, 'Service', $svc->date->format('d/m/Y'), $svc->created_at->format('H:i'),
                $catLabel, $svc->details ?? '',
                number_format($svc->montant, 0, ',', ' '), number_format($svc->montant, 0, ',', ' '),
                '—', '—',
            ];
        }

        // Répartition par catégorie service
        if (!empty($svcByType)) {
            $lignes[] = array_fill(0, $nbCols, '');
            $lignes[] = ['', '', '── RÉPARTITION SERVICES ──', '', '', '', '', '', '', ''];
            foreach ($svcByType as $cat => $total) {
                $lignes[] = ['', '', '', '', "  ↳ {$cat}", '', number_format($total, 0, ',', ' ') . ' FCFA', '', '', ''];
            }
        }

        // Sous-total services
        $lignes[] = array_fill(0, $nbCols, '');
        $lignes[] = [
            '', 'SOUS-TOTAL SERVICES', '', '', '',
            count($services) . ' service(s)',
            number_format($totalSvcMontant, 0, ',', ' ') . ' FCFA',
            number_format($totalSvcMontant, 0, ',', ' ') . ' FCFA', '—', '',
        ];
        $lignes[] = array_fill(0, $nbCols, '');

        // ═══════════════════════ RETRAITS ═══════════════════════
        $totalRetraitMontant = 0;

        if (count($retraits) > 0) {
            $lignes[] = ['═══ SORTIES — RETRAITS ═══', '', '', '', '', '', '', '', '', ''];

            foreach ($retraits as $r) {
                $n++;
                $totalRetraitMontant += (float) $r->montant;
                $lignes[] = [
                    $n, 'Retrait', $r->date->format('d/m/Y'), $r->created_at->format('H:i'),
                    '—', $r->motif,
                    '-' . number_format($r->montant, 0, ',', ' '), '—', '—', '—',
                ];
            }

            // Sous-total retraits
            $lignes[] = array_fill(0, $nbCols, '');
            $lignes[] = [
                '', 'SOUS-TOTAL RETRAITS', '', '', '',
                count($retraits) . ' retrait(s)',
                '-' . number_format($totalRetraitMontant, 0, ',', ' ') . ' FCFA',
                '—', '—', '',
            ];
            $lignes[] = array_fill(0, $nbCols, '');
        }

        // ═══════════════════════ SYNTHÈSE ═══════════════════════
        $lignes[] = ['═══ SYNTHÈSE FINANCIÈRE ═══', '', '', '', '', '', '', '', '', ''];
        $lignes[] = [
            '', 'Total Entrées (Commandes)', '', '', '',
            '', number_format($totalCmdPaye, 0, ',', ' ') . ' FCFA', '', '', '',
        ];
        $lignes[] = [
            '', 'Total Entrées (Services)', '', '', '',
            '', number_format($totalSvcMontant, 0, ',', ' ') . ' FCFA', '', '', '',
        ];
        $lignes[] = [
            '', 'Total Sorties (Retraits)', '', '', '',
            '', '-' . number_format($totalRetraitMontant, 0, ',', ' ') . ' FCFA', '', '', '',
        ];
        $lignes[] = array_fill(0, $nbCols, '');

        $totalEntrees = $totalCmdPaye + $totalSvcMontant;
        $bilan = $totalEntrees - $totalRetraitMontant;
        $lignes[] = [
            '', 'BILAN NET', '', '', '',
            'Entrées : ' . number_format($totalEntrees, 0, ',', ' ') . ' | Sorties : ' . number_format($totalRetraitMontant, 0, ',', ' '),
            number_format($bilan, 0, ',', ' ') . ' FCFA',
            'Sorties : ' . number_format($totalRetraitMontant, 0, ',', ' '),
            $bilan >= 0 ? 'Positif' : 'Déficitaire', '',
        ];

        // ═══════════════════════ PIED DE PAGE ═══════════════════════
        $lignes[] = array_fill(0, $nbCols, '');
        $lignes[] = ['LE PHARAON — Rapport généré le ' . now()->format('d/m/Y à H:i'), '', '', '', '', '', '', '', '', ''];

        $titre = "Rapport {$dateDebut} au {$dateFin} — {$module}";
        return self::csv($titre, $colonnes, $lignes);
    }

    /**
     * ══════════════════════════════════════════════════════════════
     * EXPORT HTML AVEC LOGO ET COULEURS LE PHARAON
     * ══════════════════════════════════════════════════════════════
     */
    public static function rapportHtml(
        string $dateDebut,
        string $dateFin,
        $commandes,
        $services,
        $retraits,
        array $typesLabels,
        string $module
    ): Response {
        $totalCmdMontant = $commandes->sum('montant');
        $totalCmdPaye = $commandes->sum('montant_paye');
        $totalSvcMontant = $services->sum('montant');
        $totalRetraitMontant = $retraits->sum('montant');
        $bilan = $totalCmdPaye + $totalSvcMontant - $totalRetraitMontant;
        $logoUrl = asset('storage/avatars/logo-pharaon.png');

        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport ' . $module . ' — Le Pharaon</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:"Segoe UI",Arial,sans-serif; background:#f8f8f8; color:#080808; }
    .container { max-width:1000px; margin:0 auto; padding:20px; }
    .header { background:#080808; color:white; padding:30px; border-radius:12px 12px 0 0; text-align:center; }
    .header img { height:60px; margin-bottom:10px; filter:drop-shadow(0 2px 8px rgba(211,155,35,0.4)); }
    .header h1 { color:#D39B23; font-size:22px; margin-bottom:4px; }
    .header h2 { color:#FACE4A; font-size:16px; font-weight:400; }
    .header p { color:#888; font-size:13px; margin-top:6px; }
    .content { background:white; padding:20px; border:1px solid #e7e7e7; }
    .section-title { background:#D39B23; color:white; padding:10px 16px; border-radius:8px; font-weight:700; font-size:14px; margin:20px 0 10px; }
    .section-title.red { background:#D64545; }
    .section-title.blue { background:#3B82C4; }
    table { width:100%; border-collapse:collapse; margin-bottom:16px; font-size:12px; }
    th { background:#080808; color:white; padding:8px 10px; text-align:left; font-size:11px; text-transform:uppercase; }
    td { padding:7px 10px; border-bottom:1px solid #eee; }
    tr:hover td { background:#FBF3E6; }
    .subtotal { background:#FBF3E6; font-weight:700; }
    .subtotal td { border-top:2px solid #D39B23; padding:10px; }
    .subtotal-row { background:#f3f3f3; font-weight:600; }
    .subtotal-row td { border-top:2px solid #D39B23; padding:10px; color:#080808; }
    .bilan { background:linear-gradient(135deg,#FBF3E6,#fff); border:2px solid #D39B23; border-radius:12px; padding:20px; margin-top:20px; }
    .bilan h3 { color:#D39B23; margin-bottom:12px; }
    .bilan .row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #eee; }
    .bilan .row:last-child { border-bottom:none; }
    .bilan .total { font-size:20px; font-weight:700; color:#D39B23; margin-top:10px; }
    .green { color:#2E8B57; } .red { color:#D64545; } .blue { color:#3B82C4; }
    .footer { text-align:center; padding:16px; color:#888; font-size:11px; border-top:1px solid #e7e7e7; }
    .badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600; }
    .badge-paye { background:#d4edda; color:#155724; }
    .badge-partiel { background:#fff3cd; color:#856404; }
    .badge-apayer { background:#f8d7da; color:#721c24; }
    @media print { body { background:white; } .container { padding:0; } }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="' . $logoUrl . '" alt="Le Pharaon">
        <h1>RAPPORT FINANCIER</h1>
        <h2>' . strtoupper($module) . '</h2>
        <p>Période : ' . $dateDebut . ' au ' . $dateFin . ' &mdash; Généré le ' . now()->format('d/m/Y à H:i') . '</p>
    </div>
    <div class="content">';

        // ═══ COMMANDES ═══
        if ($commandes->count() > 0) {
            $html .= '<div class="section-title">📋 ENTRÉES — COMMANDES (' . $commandes->count() . ')</div>';
            $html .= '<table><thead><tr><th>N°</th><th>Date</th><th>Type</th><th>Détails</th><th>Total</th><th>Payé</th><th>Reste</th><th>Statut</th></tr></thead><tbody>';
            $i = 0;
            foreach ($commandes as $cmd) {
                $i++;
                $cat = $typesLabels[$cmd->type] ?? $cmd->type;
                $badgeClass = match($cmd->statut_paiement) { 'paye' => 'badge-paye', 'partiel' => 'badge-partiel', default => 'badge-apayer' };
                $badgeText = match($cmd->statut_paiement) { 'paye' => 'Payé', 'partiel' => 'Partiel', default => 'À payer' };
                $reste = $cmd->montant - $cmd->montant_paye;
                $html .= "<tr>
                    <td>{$i}</td><td>{$cmd->date->format('d/m/Y')}</td><td>{$cat}</td>
                    <td>" . ($cmd->details ?? '—') . "</td>
                    <td class='green'>" . number_format($cmd->montant, 0, ',', ' ') . "</td>
                    <td class='blue'>" . number_format($cmd->montant_paye, 0, ',', ' ') . "</td>
                    <td class='red'>" . number_format($reste, 0, ',', ' ') . "</td>
                    <td><span class='badge {$badgeClass}'>{$badgeText}</span></td>
                </tr>";
            }
            $html .= "</tbody></table>";
            $html .= '<div class="subtotal-row"><table style="width:100%"><tr><td><strong>Total commandes : ' . number_format($totalCmdMontant, 0, ',', ' ') . ' FCFA</strong></td><td style="text-align:right"><strong>Payé : <span class="blue">' . number_format($totalCmdPaye, 0, ',', ' ') . '</span> FCFA | Reste : <span class="red">' . number_format($totalCmdMontant - $totalCmdPaye, 0, ',', ' ') . '</span> FCFA</strong></td></tr></table></div>';
        }

        // ═══ SERVICES ═══
        if ($services->count() > 0) {
            $html .= '<div class="section-title blue">📸 ENTRÉES — SERVICES (' . $services->count() . ')</div>';
            $html .= '<table><thead><tr><th>N°</th><th>Date</th><th>Heure</th><th>Type</th><th>Détails</th><th>Montant</th></tr></thead><tbody>';
            $i = 0;
            foreach ($services as $svc) {
                $i++;
                $cat = $typesLabels[$svc->type] ?? $svc->type;
                $html .= "<tr><td>{$i}</td><td>{$svc->date->format('d/m/Y')}</td><td>{$svc->created_at->format('H:i')}</td><td>{$cat}</td><td>" . ($svc->details ?? '—') . "</td><td class='green'>" . number_format($svc->montant, 0, ',', ' ') . " FCFA</td></tr>";
            }
            $html .= "</tbody></table>";
            $html .= '<div class="subtotal-row"><table style="width:100%"><tr><td><strong>Total services : <span class="blue">' . number_format($totalSvcMontant, 0, ',', ' ') . '</span> FCFA</strong></td></tr></table></div>';
        }

        // ═══ RETRAITS ═══
        if ($retraits->count() > 0) {
            $html .= '<div class="section-title red">📤 SORTIES — RETRAITS (' . $retraits->count() . ')</div>';
            $html .= '<table><thead><tr><th>N°</th><th>Date</th><th>Heure</th><th>Motif</th><th>Montant</th></tr></thead><tbody>';
            $i = 0;
            foreach ($retraits as $r) {
                $i++;
                $html .= "<tr><td>{$i}</td><td>{$r->date->format('d/m/Y')}</td><td>{$r->created_at->format('H:i')}</td><td>{$r->motif}</td><td class='red'>-" . number_format($r->montant, 0, ',', ' ') . " FCFA</td></tr>";
            }
            $html .= "</tbody></table>";
            $html .= '<div class="subtotal-row"><table style="width:100%"><tr><td><strong>Total retraits : <span class="red">-' . number_format($totalRetraitMontant, 0, ',', ' ') . '</span> FCFA</strong></td></tr></table></div>';
        }

        // ═══ BILAN ═══
        $html .= '<div class="bilan">
            <h3>🏆 SYNTHÈSE FINANCIÈRE</h3>
            <div class="row"><span>Entrées — Commandes payées</span><span class="green">+ ' . number_format($totalCmdPaye, 0, ',', ' ') . ' FCFA</span></div>
            <div class="row"><span>Entrées — Services fournis</span><span class="green">+ ' . number_format($totalSvcMontant, 0, ',', ' ') . ' FCFA</span></div>
            <div class="row"><span>Sorties — Retraits</span><span class="red">- ' . number_format($totalRetraitMontant, 0, ',', ' ') . ' FCFA</span></div>
            <div class="total">Bilan net : ' . ($bilan >= 0 ? '<span class="green">' : '<span class="red">') . number_format($bilan, 0, ',', ' ') . ' FCFA</span></div>
        </div>';

        $html .= '</div>
    <div class="footer">Le Pharaon — Système de Gestion de Présence &bull; Rapport confidentiel</div>
</div>
</body></html>';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    /**
     * Échappe une ligne CSV.
     */
    private static function escapeRow(array $cells): string
    {
        return implode(self::SEP, array_map(function ($cell) {
            if (is_null($cell)) return '""';
            $cell = str_replace('"', '""', (string) $cell);
            return "\"{$cell}\"";
        }, $cells));
    }

    /**
     * Convertit un titre en nom de fichier slug.
     */
    private static function slug(string $texte): string
    {
        $texte = strtolower($texte);
        $texte = preg_replace('/[^a-z0-9\s-]/', '', $texte);
        $texte = preg_replace('/[\s-]+/', '_', $texte);
        return trim($texte, '_');
    }
}
