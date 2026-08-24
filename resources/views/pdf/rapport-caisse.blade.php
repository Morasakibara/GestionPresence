<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport {{ $module }} — Le Pharaon</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:"Segoe UI","Helvetica Neue",Arial,sans-serif; color:#080808; font-size:11px; line-height:1.4; }

    /* ═══ EN-TÊTE ═══ */
    .header { background:#080808; color:white; padding:24px 30px; border-radius:0 0 12px 12px; text-align:center; margin-bottom:20px; }
    .header img { height:55px; margin-bottom:8px; }
    .header h1 { color:#D39B23; font-size:20px; margin-bottom:2px; letter-spacing:1px; }
    .header h2 { color:#FACE4A; font-size:14px; font-weight:400; }
    .header .period { color:#888; font-size:11px; margin-top:6px; }

    /* ═══ SECTIONS ═══ */
    .section { margin-bottom:18px; }
    .section-title { background:#D39B23; color:white; padding:8px 14px; border-radius:6px; font-weight:700; font-size:12px; margin-bottom:8px; }
    .section-title.red { background:#D64545; }
    .section-title.blue { background:#3B82C4; }

    /* ═══ TABLEAUX ═══ */
    table { width:100%; border-collapse:collapse; margin-bottom:10px; }
    th { background:#080808; color:white; padding:7px 8px; text-align:left; font-size:10px; text-transform:uppercase; letter-spacing:0.5px; }
    td { padding:6px 8px; border-bottom:1px solid #eee; font-size:10px; }
    tr:nth-child(even) td { background:#fafafa; }
    .text-right { text-align:right; }
    .text-center { text-align:center; }

    /* ═══ COULEURS ═══ */
    .green { color:#2E8B57; font-weight:600; }
    .red { color:#D64545; font-weight:600; }
    .blue { color:#3B82C4; font-weight:600; }
    .gold { color:#D39B23; font-weight:600; }

    /* ═══ BADGES ═══ */
    .badge { display:inline-block; padding:2px 7px; border-radius:8px; font-size:9px; font-weight:700; }
    .badge-paye { background:#d4edda; color:#155724; }
    .badge-partiel { background:#fff3cd; color:#856404; }
    .badge-apayer { background:#f8d7da; color:#721c24; }

    /* ═══ SOUS-TOTAUX ═══ */
    .subtotal { background:#FBF3E6; font-weight:700; }
    .subtotal td { border-top:2px solid #D39B23; padding:8px; font-size:11px; }

    /* ═══ RÉPARTITION ═══ */
    .repartition { margin:4px 0 10px 12px; }
    .repartition-row { display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px dotted #ddd; font-size:10px; }

    /* ═══ BILAN ═══ */
    .bilan { border:2px solid #D39B23; border-radius:10px; padding:16px 20px; margin-top:16px; background:linear-gradient(135deg,#FBF3E6 0%,#fff 100%); }
    .bilan h3 { color:#D39B23; font-size:14px; margin-bottom:10px; border-bottom:1px solid #D39B23; padding-bottom:6px; }
    .bilan-row { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #eee; font-size:11px; }
    .bilan-total { font-size:16px; font-weight:700; margin-top:10px; padding-top:8px; border-top:2px solid #D39B23; text-align:right; }

    /* ═══ PIED DE PAGE ═══ */
    .footer { text-align:center; color:#888; font-size:9px; margin-top:24px; padding-top:10px; border-top:1px solid #ddd; }

    /* ═══ SAUT DE PAGE ═══ */
    .page-break { page-break-before:always; }
</style>
</head>
<body>

<!-- ═══════════════ EN-TÊTE ═══════════════ -->
<div class="header">
    @if($logoBase64)
        <img src="{{ $logoBase64 }}" alt="Le Pharaon">
    @endif
    <h1>RAPPORT FINANCIER</h1>
    <h2>{{ strtoupper($module) }}</h2>
    <div class="period">Période : {{ $dateDebut }} au {{ $dateFin }} &mdash; Généré le {{ now()->format('d/m/Y à H:i') }}</div>
</div>

<!-- ═══════════════ COMMANDES ═══════════════ -->
@if($commandes->count() > 0)
<div class="section">
    <div class="section-title">📋 ENTRÉES — COMMANDES ({{ $commandes->count() }})</div>
    <table>
        <thead>
            <tr>
                <th width="4%">N°</th>
                <th width="10%">Date</th>
                <th width="14%">Catégorie</th>
                <th width="18%">Détails</th>
                <th width="12%" class="text-right">Total</th>
                <th width="12%" class="text-right">Payé</th>
                <th width="12%" class="text-right">Reste</th>
                <th width="10%" class="text-center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commandes as $i => $cmd)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $cmd->date->format('d/m/Y') }}</td>
                <td>{{ $typesLabels[$cmd->type] ?? $cmd->type }}</td>
                <td>{{ $cmd->details ?? '—' }}</td>
                <td class="text-right green">{{ number_format($cmd->montant, 0, ',', ' ') }}</td>
                <td class="text-right blue">{{ number_format($cmd->montant_paye, 0, ',', ' ') }}</td>
                <td class="text-right red">{{ number_format($cmd->montant - $cmd->montant_paye, 0, ',', ' ') }}</td>
                <td class="text-center">
                    @if($cmd->statut_paiement === 'paye')
                        <span class="badge badge-paye">Payé</span>
                    @elseif($cmd->statut_paiement === 'partiel')
                        <span class="badge badge-partiel">Partiel</span>
                    @else
                        <span class="badge badge-apayer">À payer</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Répartition par catégorie --}}
    @if(!empty($cmdByType))
    <div class="repartition">
        <strong style="font-size:10px;color:#555;">Répartition par catégorie :</strong>
        @foreach($cmdByType as $type => $data)
        <div class="repartition-row">
            <span>{{ $typesLabels[$type] ?? $type }} ({{ $data['count'] }})</span>
            <span class="gold">{{ number_format($data['total'], 0, ',', ' ') }} FCFA</span>
        </div>
        @endforeach
    </div>
    @endif

    <table>
        <tr class="subtotal">
            <td colspan="4"><strong>Sous-total commandes</strong></td>
            <td class="text-right"><strong class="gold">{{ number_format($totalCmdMontant, 0, ',', ' ') }} FCFA</strong></td>
            <td class="text-right"><strong class="blue">{{ number_format($totalCmdPaye, 0, ',', ' ') }} FCFA</strong></td>
            <td class="text-right"><strong class="red">{{ number_format($totalCmdMontant - $totalCmdPaye, 0, ',', ' ') }} FCFA</strong></td>
            <td></td>
        </tr>
    </table>
</div>
@endif

<!-- ═══════════════ SERVICES ═══════════════ -->
@if($services->count() > 0)
<div class="section">
    <div class="section-title blue">📸 ENTRÉES — SERVICES ({{ $services->count() }})</div>
    <table>
        <thead>
            <tr>
                <th width="4%">N°</th>
                <th width="10%">Date</th>
                <th width="8%">Heure</th>
                <th width="18%">Catégorie</th>
                <th width="24%">Détails</th>
                <th width="14%" class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $i => $svc)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $svc->date->format('d/m/Y') }}</td>
                <td>{{ $svc->created_at->format('H:i') }}</td>
                <td>{{ $typesLabels[$svc->type] ?? $svc->type }}</td>
                <td>{{ $svc->details ?? '—' }}</td>
                <td class="text-right green">{{ number_format($svc->montant, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($svcByType))
    <div class="repartition">
        <strong style="font-size:10px;color:#555;">Répartition par catégorie :</strong>
        @foreach($svcByType as $type => $data)
        <div class="repartition-row">
            <span>{{ $typesLabels[$type] ?? $type }} ({{ $data['count'] }})</span>
            <span class="blue">{{ number_format($data['total'], 0, ',', ' ') }} FCFA</span>
        </div>
        @endforeach
    </div>
    @endif

    <table>
        <tr class="subtotal">
            <td colspan="5"><strong>Sous-total services</strong></td>
            <td class="text-right"><strong class="blue">{{ number_format($totalSvcMontant, 0, ',', ' ') }} FCFA</strong></td>
        </tr>
    </table>
</div>
@endif

<!-- ═══════════════ RETRAITS ═══════════════ -->
@if($retraits->count() > 0)
<div class="section">
    <div class="section-title red">📤 SORTIES — RETRAITS ({{ $retraits->count() }})</div>
    <table>
        <thead>
            <tr>
                <th width="4%">N°</th>
                <th width="12%">Date</th>
                <th width="8%">Heure</th>
                <th width="40%">Motif</th>
                <th width="14%" class="text-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retraits as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->date->format('d/m/Y') }}</td>
                <td>{{ $r->created_at->format('H:i') }}</td>
                <td>{{ $r->motif }}</td>
                <td class="text-right red">-{{ number_format($r->montant, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <table>
        <tr class="subtotal">
            <td colspan="4"><strong>Sous-total retraits</strong></td>
            <td class="text-right"><strong class="red">-{{ number_format($totalRetraitMontant, 0, ',', ' ') }} FCFA</strong></td>
        </tr>
    </table>
</div>
@endif

<!-- ═══════════════ BILAN ═══════════════ -->
<div class="bilan">
    <h3>🏆 SYNTHÈSE FINANCIÈRE</h3>
    <div class="bilan-row">
        <span>💰 Entrées — Commandes payées</span>
        <span class="green">+ {{ number_format($totalCmdPaye, 0, ',', ' ') }} FCFA</span>
    </div>
    <div class="bilan-row">
        <span>💰 Entrées — Services fournis</span>
        <span class="green">+ {{ number_format($totalSvcMontant, 0, ',', ' ') }} FCFA</span>
    </div>
    <div class="bilan-row">
        <span>📤 Sorties — Retraits</span>
        <span class="red">- {{ number_format($totalRetraitMontant, 0, ',', ' ') }} FCFA</span>
    </div>
    <div class="bilan-total">
        Bilan net :
        @if($bilan >= 0)
            <span class="green">{{ number_format($bilan, 0, ',', ' ') }} FCFA</span>
        @else
            <span class="red">{{ number_format($bilan, 0, ',', ' ') }} FCFA</span>
        @endif
    </div>
</div>

<!-- ═══════════════ PIED DE PAGE ═══════════════ -->
<div class="footer">
    Le Pharaon — Système de Gestion de Présence<br>
    Rapport confidentiel &bull; Généré le {{ now()->format('d/m/Y à H:i') }}
</div>

</body>
</html>
