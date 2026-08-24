<?php

namespace App\Services;

use Illuminate\Http\Response;

class ExportService
{
    /**
     * Génère un CSV professionnel avec en-tête Le Pharaon.
     */
    public static function csv(
        string $titre,
        array $colonnes,
        array $lignes,
        string $module = 'Le Pharaon'
    ): Response {
        $bom = "\xEF\xBB\xBF"; // UTF-8 BOM pour Excel
        $contenu = $bom;

        // En-tête du rapport
        $contenu .= "\"{$module}\";\n";
        $contenu .= "\"{$titre}\";\n";
        $contenu .= "\"Date d'export : " . now()->format('d/m/Y à H:i') . "\";\n";
        $contenu .= "\n";

        // Ligne vide de séparation
        $contenu .= str_repeat(';', count($colonnes)) . "\n";

        // En-têtes de colonnes
        $contenu .= implode(';', array_map(fn($c) => "\"{$c}\"", $colonnes)) . "\n";

        // Ligne de séparation décorative
        $contenu .= str_repeat('—', 80) . "\n";

        // Données
        foreach ($lignes as $ligne) {
            $contenu .= implode(';', array_map(function ($cell) {
                if (is_null($cell)) return '""';
                $cell = str_replace('"', '""', (string) $cell);
                return "\"{$cell}\"";
            }, $ligne)) . "\n";
        }

        // Pied de page
        $contenu .= "\n";
        $contenu .= str_repeat('—', 80) . "\n";
        $contenu .= "\"{$module} — Export généré le " . now()->format('d/m/Y à H:i') . "\";\n";

        return response($contenu, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=' . self::slug($titre) . '_' . now()->format('Y_m_d_His') . '.csv',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Export des commandes individuellement.
     */
    public static function commandes($commandes, array $typesLabels, string $module): Response
    {
        $colonnes = ['#', 'Date', 'Type', 'Montant Total', 'Montant Payé', 'Reste', 'Statut', 'Détails'];
        $lignes = $commandes->map(function ($cmd) use ($typesLabels) {
            $statutLabel = match($cmd->statut_paiement) {
                'paye' => '✅ Payé',
                'partiel' => '🟠 Partiel',
                'a_payer' => '🔴 À payer',
                default => $cmd->statut_paiement,
            };
            return [
                $cmd->id, $cmd->date->format('d/m/Y'),
                $typesLabels[$cmd->type] ?? $cmd->type,
                number_format($cmd->montant, 0, ',', ' ') . ' FCFA',
                number_format($cmd->montant_paye, 0, ',', ' ') . ' FCFA',
                number_format($cmd->montant - $cmd->montant_paye, 0, ',', ' ') . ' FCFA',
                $statutLabel, $cmd->details ?? '',
            ];
        })->toArray();
        return self::csv("Commandes — {$module}", $colonnes, $lignes);
    }

    /**
     * Export des services individuellement.
     */
    public static function services($services, array $typesLabels, string $module): Response
    {
        $colonnes = ['#', 'Date', 'Heure', 'Type', 'Montant', 'Détails'];
        $lignes = $services->map(function ($svc) use ($typesLabels) {
            return [
                $svc->id, $svc->date->format('d/m/Y'), $svc->created_at->format('H:i'),
                $typesLabels[$svc->type] ?? $svc->type,
                number_format($svc->montant, 0, ',', ' ') . ' FCFA',
                $svc->details ?? '',
            ];
        })->toArray();
        return self::csv("Services — {$module}", $colonnes, $lignes);
    }

    /**
     * Export des retraits individuellement.
     */
    public static function retraits($retraits, string $module): Response
    {
        $colonnes = ['#', 'Date', 'Heure', 'Motif', 'Montant'];
        $lignes = $retraits->map(fn($r) => [
            $r->id, $r->date->format('d/m/Y'), $r->created_at->format('H:i'),
            $r->motif, '-' . number_format($r->montant, 0, ',', ' ') . ' FCFA',
        ])->toArray();
        return self::csv("Retraits — {$module}", $colonnes, $lignes);
    }

    /**
     * ══════════════════════════════════════════════════════════════
     * RAPPORT FINANCIER CONSOLIDÉ — TOUTES LES DONNÉES
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
        $lignes = [];
        $n = 0;

        // ═══════════════════════ EN-TÊTE ═══════════════════════
        $lignes[] = ['LE PHARAON', '', '', '', '', '', '', '', '', ''];
        $lignes[] = ['RAPPORT FINANCIER — ' . strtoupper($module), '', '', '', '', '', '', '', '', ''];
        $lignes[] = ['Période : ' . $dateDebut . ' au ' . $dateFin, '', '', '', '', '', '', '', '', ''];
        $lignes[] = ['Généré le : ' . now()->format('d/m/Y à H:i'), '', '', '', '', '', '', '', '', ''];
        $lignes[] = array_fill(0, 10, '');

        // ═══════════════════════ COMMANDES ═══════════════════════
        $lignes[] = ['═══ ENTRÉES — COMMANDES ═══', '', '', '', '', '', '', '', '', ''];
        $totalCmdMontant = 0;
        $totalCmdPaye = 0;
        $cmdByType = [];

        foreach ($commandes as $cmd) {
            $n++;
            $totalCmdMontant += $cmd->montant;
            $totalCmdPaye += $cmd->montant_paye;
            $catLabel = $typesLabels[$cmd->type] ?? $cmd->type;
            $cmdByType[$catLabel] = ($cmdByType[$catLabel] ?? 0) + $cmd->montant;
            $statutLabel = match($cmd->statut_paiement) {
                'paye' => 'Payé', 'partiel' => 'Partiel', 'a_payer' => 'À payer', default => $cmd->statut_paiement,
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
            $lignes[] = array_fill(0, 10, '');
            foreach ($cmdByType as $cat => $total) {
                $lignes[] = ['', '', '', '', "  ↳ {$cat}", '', number_format($total, 0, ',', ' ') . ' FCFA', '', '', ''];
            }
        }

        // Sous-total commandes
        $lignes[] = array_fill(0, 10, '');
        $lignes[] = [
            '', '📋 SOUS-TOTAL COMMANDES', '', '', '',
            count($commandes) . ' commande(s)',
            number_format($totalCmdMontant, 0, ',', ' ') . ' FCFA',
            number_format($totalCmdPaye, 0, ',', ' ') . ' FCFA',
            number_format($totalCmdMontant - $totalCmdPaye, 0, ',', ' ') . ' FCFA', '',
        ];
        $lignes[] = array_fill(0, 10, '');

        // ═══════════════════════ SERVICES ═══════════════════════
        $lignes[] = ['═══ ENTRÉES — SERVICES FOURNIS ═══', '', '', '', '', '', '', '', '', ''];
        $totalSvcMontant = 0;
        $svcByType = [];

        foreach ($services as $svc) {
            $n++;
            $totalSvcMontant += $svc->montant;
            $catLabel = $typesLabels[$svc->type] ?? $svc->type;
            $svcByType[$catLabel] = ($svcByType[$catLabel] ?? 0) + $svc->montant;
            $lignes[] = [
                $n, 'Service', $svc->date->format('d/m/Y'), $svc->created_at->format('H:i'),
                $catLabel, $svc->details ?? '',
                number_format($svc->montant, 0, ',', ' '), number_format($svc->montant, 0, ',', ' '),
                '—', '—',
            ];
        }

        // Répartition par catégorie service
        if (!empty($svcByType)) {
            $lignes[] = array_fill(0, 10, '');
            foreach ($svcByType as $cat => $total) {
                $lignes[] = ['', '', '', '', "  ↳ {$cat}", '', number_format($total, 0, ',', ' ') . ' FCFA', '', '', ''];
            }
        }

        // Sous-total services
        $lignes[] = array_fill(0, 10, '');
        $lignes[] = [
            '', '📋 SOUS-TOTAL SERVICES', '', '', '',
            count($services) . ' service(s)',
            number_format($totalSvcMontant, 0, ',', ' ') . ' FCFA',
            number_format($totalSvcMontant, 0, ',', ' ') . ' FCFA', '', '',
        ];
        $lignes[] = array_fill(0, 10, '');

        // ═══════════════════════ RETRAITS ═══════════════════════
        $totalRetraitMontant = 0;

        if (count($retraits) > 0) {
            $lignes[] = ['═══ SORTIES — RETRAITS ═══', '', '', '', '', '', '', '', '', ''];

            foreach ($retraits as $r) {
                $n++;
                $totalRetraitMontant += $r->montant;
                $lignes[] = [
                    $n, 'Retrait', $r->date->format('d/m/Y'), $r->created_at->format('H:i'),
                    '—', $r->motif,
                    '-' . number_format($r->montant, 0, ',', ' '), '—', '—', '—',
                ];
            }

            // Sous-total retraits
            $lignes[] = array_fill(0, 10, '');
            $lignes[] = [
                '', '📋 SOUS-TOTAL RETRAITS', '', '', '',
                count($retraits) . ' retrait(s)',
                '-' . number_format($totalRetraitMontant, 0, ',', ' ') . ' FCFA',
                '—', '—', '',
            ];
            $lignes[] = array_fill(0, 10, '');
        }

        // ═══════════════════════ SYNTHÈSE ═══════════════════════
        $lignes[] = ['═══ SYNTHÈSE ═══', '', '', '', '', '', '', '', '', ''];
        $lignes[] = [
            '', '💰 Total Entrées (Cmd)', '', '', '',
            '', number_format($totalCmdPaye, 0, ',', ' ') . ' FCFA',
            '', '', '',
        ];
        $lignes[] = [
            '', '💰 Total Entrées (Svc)', '', '', '',
            '', number_format($totalSvcMontant, 0, ',', ' ') . ' FCFA',
            '', '', '',
        ];
        $lignes[] = [
            '', '📤 Total Sorties (Retraits)', '', '', '',
            '', '-' . number_format($totalRetraitMontant, 0, ',', ' ') . ' FCFA',
            '', '', '',
        ];
        $lignes[] = array_fill(0, 10, '');

        $bilan = $totalCmdPaye + $totalSvcMontant - $totalRetraitMontant;
        $lignes[] = [
            '', '🏆 BILAN NET', '', '', '',
            'Entrées : ' . number_format($totalCmdPaye + $totalSvcMontant, 0, ',', ' '),
            number_format($bilan, 0, ',', ' ') . ' FCFA',
            'Sorties : ' . number_format($totalRetraitMontant, 0, ',', ' '),
            $bilan >= 0 ? '✅ Positif' : '⚠️ Déficitaire', '',
        ];

        // ═══════════════════════ PIED DE PAGE ═══════════════════════
        $lignes[] = array_fill(0, 10, '');
        $lignes[] = ['LE PHARAON — Rapport généré le ' . now()->format('d/m/Y à H:i'), '', '', '', '', '', '', '', '', ''];

        $titre = "Rapport {$dateDebut} au {$dateFin} — {$module}";
        return self::csv($titre, $colonnes, $lignes);
    }

    /**
     * ══════════════════════════════════════════════════════════════
     * EXPORT HTML AVEC LOGO ET COULEURS LE PHARAON
     *══════════════════════════════════════════════════════════════
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
