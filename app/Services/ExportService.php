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
        $contenu .= "={$module}=\n";
        $contenu .= "={$titre}=\n";
        $contenu .= "Date : " . now()->format('d/m/Y H:i') . "\n";
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
        $contenu .= "=\"{$module} — Export \" & TEXTE(NOW();\"dd/mm/yyyy hh:mm\")\n";

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
     * Export du rapport financier consolidé.
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
        $colonnes = ['Type', 'Date', 'Heure', 'Catégorie', 'Détails', 'Montant'];
        $lignes = [];

        foreach ($commandes as $cmd) {
            $lignes[] = [
                'Commande',
                $cmd->date->format('d/m/Y'),
                $cmd->created_at->format('H:i'),
                $typesLabels[$cmd->type] ?? $cmd->type,
                ($cmd->details ?? '') . ($cmd->statut_paiement !== 'paye' ? " [{$cmd->statut_paiement}]" : ''),
                number_format($cmd->montant_paye, 0, ',', ' ') . ' FCFA',
            ];
        }

        foreach ($services as $svc) {
            $lignes[] = [
                'Service',
                $svc->date->format('d/m/Y'),
                $svc->created_at->format('H:i'),
                $typesLabels[$svc->type] ?? $svc->type,
                $svc->details ?? '',
                number_format($svc->montant, 0, ',', ' ') . ' FCFA',
            ];
        }

        foreach ($retraits as $r) {
            $lignes[] = [
                'Retrait',
                $r->date->format('d/m/Y'),
                $r->created_at->format('H:i'),
                '—',
                $r->motif,
                '-' . number_format($r->montant, 0, ',', ' ') . ' FCFA',
            ];
        }

        // Trier par date puis heure
        usort($lignes, function ($a, $b) {
            $dateCmp = strcmp($a[1], $b[1]);
            return $dateCmp !== 0 ? $dateCmp : strcmp($a[2], $b[2]);
        });

        $titre = "Rapport {$dateDebut} au {$dateFin} — {$module}";
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
