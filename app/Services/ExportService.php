<?php

namespace App\Services;

use Illuminate\Http\Response;

class ExportService
{
    /**
     * Génère un CSV professionnel avec en-tête Le Pharaon et styling.
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

        // En-têtes de colonnes (en gras via Excel)
        $contenu .= implode(';', array_map(fn($c) => "\"{$c}\"", $colonnes)) . "\n";

        // Ligne de séparation décorative
        $contenu .= str_repeat('—', 60) . "\n";

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
        $contenu .= str_repeat('—', 60) . "\n";
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
     * Export des commandes avec statut de paiement coloré.
     */
    public static function commandes(
        $commandes,
        array $typesLabels,
        string $module
    ): Response {
        $colonnes = ['#', 'Date', 'Type', 'Montant Total', 'Montant Payé', 'Reste', 'Statut', 'Détails'];

        $lignes = $commandes->map(function ($cmd) use ($typesLabels) {
            $statutLabel = match($cmd->statut_paiement) {
                'paye' => '✅ Payé',
                'partiel' => '🟠 Partiel',
                'a_payer' => '🔴 À payer',
                default => $cmd->statut_paiement,
            };

            return [
                $cmd->id,
                $cmd->date->format('d/m/Y'),
                $typesLabels[$cmd->type] ?? $cmd->type,
                number_format($cmd->montant, 0, ',', ' ') . ' FCFA',
                number_format($cmd->montant_paye, 0, ',', ' ') . ' FCFA',
                number_format($cmd->montant - $cmd->montant_paye, 0, ',', ' ') . ' FCFA',
                $statutLabel,
                $cmd->details ?? '',
            ];
        })->toArray();

        $titre = "Commandes — {$module}";
        return self::csv($titre, $colonnes, $lignes);
    }

    /**
     * Export des services fournis.
     */
    public static function services(
        $services,
        array $typesLabels,
        string $module
    ): Response {
        $colonnes = ['#', 'Date', 'Heure', 'Type', 'Montant', 'Détails'];

        $lignes = $services->map(function ($svc) use ($typesLabels) {
            return [
                $svc->id,
                $svc->date->format('d/m/Y'),
                $svc->created_at->format('H:i'),
                $typesLabels[$svc->type] ?? $svc->type,
                number_format($svc->montant, 0, ',', ' ') . ' FCFA',
                $svc->details ?? '',
            ];
        })->toArray();

        $titre = "Services — {$module}";
        return self::csv($titre, $colonnes, $lignes);
    }

    /**
     * Export des retraits.
     */
    public static function retraits($retraits, string $module): Response
    {
        $colonnes = ['#', 'Date', 'Heure', 'Motif', 'Montant'];

        $lignes = $retraits->map(function ($r) {
            return [
                $r->id,
                $r->date->format('d/m/Y'),
                $r->created_at->format('H:i'),
                $r->motif,
                '-' . number_format($r->montant, 0, ',', ' ') . ' FCFA',
            ];
        })->toArray();

        $titre = "Retraits — {$module}";
        return self::csv($titre, $colonnes, $lignes);
    }

    /**
     * Export du rapport financier consolidé — section par section.
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
        $colonnes = ['Type Opération', 'Date', 'Heure', 'Catégorie', 'Montant Payé', 'Montant Total', 'Statut', 'Détails'];
        $lignes = [];

        // ── COMMANDES ──
        $totalCmdMontant = 0;
        $totalCmdPaye = 0;
        foreach ($commandes as $cmd) {
            $totalCmdMontant += $cmd->montant;
            $totalCmdPaye += $cmd->montant_paye;
            $statutLabel = match($cmd->statut_paiement) {
                'paye' => 'Payé',
                'partiel' => 'Partiel',
                'a_payer' => 'À payer',
                default => $cmd->statut_paiement,
            };
            $lignes[] = [
                'Commande',
                $cmd->date->format('d/m/Y'),
                $cmd->created_at->format('H:i'),
                $typesLabels[$cmd->type] ?? $cmd->type,
                number_format($cmd->montant_paye, 0, ',', ' ') . ' FCFA',
                number_format($cmd->montant, 0, ',', ' ') . ' FCFA',
                $statutLabel,
                $cmd->details ?? '',
            ];
        }

        // ── SOUS-TOTAL COMMANDES ──
        $lignes[] = ['', '', '', '', '', '', '', ''];
        $lignes[] = [
            '📋 SOUS-TOTAL COMMANDES',
            '',
            '',
            count($commandes) . ' commande(s)',
            number_format($totalCmdPaye, 0, ',', ' ') . ' FCFA',
            number_format($totalCmdMontant, 0, ',', ' ') . ' FCFA',
            '',
            'Reste : ' . number_format($totalCmdMontant - $totalCmdPaye, 0, ',', ' ') . ' FCFA',
        ];
        $lignes[] = ['', '', '', '', '', '', '', ''];

        // ── SERVICES ──
        $totalSvcMontant = 0;
        foreach ($services as $svc) {
            $totalSvcMontant += $svc->montant;
            $lignes[] = [
                'Service',
                $svc->date->format('d/m/Y'),
                $svc->created_at->format('H:i'),
                $typesLabels[$svc->type] ?? $svc->type,
                number_format($svc->montant, 0, ',', ' ') . ' FCFA',
                '—',
                '—',
                $svc->details ?? '',
            ];
        }

        // ── SOUS-TOTAL SERVICES ──
        $lignes[] = ['', '', '', '', '', '', '', ''];
        $lignes[] = [
            '📋 SOUS-TOTAL SERVICES',
            '',
            '',
            count($services) . ' service(s)',
            number_format($totalSvcMontant, 0, ',', ' ') . ' FCFA',
            '—',
            '',
            '',
        ];
        $lignes[] = ['', '', '', '', '', '', '', ''];

        // ── RETRAITS ──
        $totalRetraitMontant = 0;
        foreach ($retraits as $r) {
            $totalRetraitMontant += $r->montant;
            $lignes[] = [
                'Retrait',
                $r->date->format('d/m/Y'),
                $r->created_at->format('H:i'),
                '—',
                '-' . number_format($r->montant, 0, ',', ' ') . ' FCFA',
                '—',
                '—',
                $r->motif,
            ];
        }

        // ── SOUS-TOTAL RETRAITS ──
        if (count($retraits) > 0) {
            $lignes[] = ['', '', '', '', '', '', '', ''];
            $lignes[] = [
                '📋 SOUS-TOTAL RETRAITS',
                '',
                '',
                count($retraits) . ' retrait(s)',
                '-' . number_format($totalRetraitMontant, 0, ',', ' ') . ' FCFA',
                '—',
                '',
                '',
            ];
            $lignes[] = ['', '', '', '', '', '', '', ''];
        }

        // ── BILAN FINAL ──
        $bilan = $totalCmdPaye + $totalSvcMontant - $totalRetraitMontant;
        $lignes[] = [
            '💰 BILAN NET (Entrées - Sorties)',
            '',
            '',
            '',
            number_format($bilan, 0, ',', ' ') . ' FCFA',
            '',
            '',
            'Entrées: ' . number_format($totalCmdPaye + $totalSvcMontant, 0, ',', ' ') . ' | Sorties: ' . number_format($totalRetraitMontant, 0, ',', ' '),
        ];

        $titre = "Rapport du {$dateDebut} au {$dateFin} — {$module}";
        return self::csv($titre, $colonnes, $lignes);
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
