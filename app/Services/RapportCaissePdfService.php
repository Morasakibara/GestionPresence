<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class RapportCaissePdfService
{
    /**
     * Génère le rapport PDF complet (commandes + services + retraits + bilan).
     */
    public static function generate(
        string $dateDebut,
        string $dateFin,
        $commandes,
        $services,
        $retraits,
        array $typesLabels,
        string $module
    ): \Barryvdh\DomPDF\PDF {
        // Calculs
        $totalCmdMontant = $commandes->sum('montant');
        $totalCmdPaye = $commandes->sum('montant_paye');
        $totalSvcMontant = $services->sum('montant');
        $totalRetraitMontant = $retraits->sum('montant');
        $bilan = $totalCmdPaye + $totalSvcMontant - $totalRetraitMontant;

        // Répartition par catégorie
        $cmdByType = $commandes->groupBy('type')->map(fn($c) => [
            'count' => $c->count(),
            'total' => $c->sum('montant'),
            'paye' => $c->sum('montant_paye'),
        ])->toArray();

        $svcByType = $services->groupBy('type')->map(fn($s) => [
            'count' => $s->count(),
            'total' => $s->sum('montant'),
        ])->toArray();

        // Logo en base64
        $logoPath = public_path('storage/avatars/logo-pharaon.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $data = compact(
            'dateDebut', 'dateFin', 'module',
            'commandes', 'services', 'retraits',
            'totalCmdMontant', 'totalCmdPaye',
            'totalSvcMontant', 'totalRetraitMontant',
            'bilan', 'cmdByType', 'svcByType',
            'typesLabels', 'logoBase64'
        );

        $html = view('pdf.rapport-caisse', $data)->render();

        return Pdf::loadHtml($html)
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
                'dpi' => 150,
            ]);
    }
}
