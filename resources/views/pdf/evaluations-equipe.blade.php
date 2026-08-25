<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Évaluations — Le Pharaon</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:"Segoe UI","Helvetica Neue",Arial,sans-serif; color:#080808; font-size:11px; line-height:1.5; }

    /* ═══ EN-TÊTE ═══ */
    .header { background:#080808; color:white; padding:24px 30px; text-align:center; margin-bottom:20px; }
    .header img { height:55px; margin-bottom:8px; }
    .header h1 { color:#D39B23; font-size:22px; margin-bottom:2px; letter-spacing:1px; }
    .header h2 { color:#FACE4A; font-size:14px; font-weight:400; }
    .header .meta { color:#888; font-size:11px; margin-top:8px; }

    /* ═══ LÉGENDE ═══ */
    .legend { display:flex; gap:16px; justify-content:center; margin-bottom:18px; font-size:10px; }
    .legend span { display:inline-flex; align-items:center; gap:4px; }
    .dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
    .dot-vert { background:#2E8B57; }
    .dot-orange { background:#D97706; }
    .dot-rouge { background:#D64545; }

    /* ═══ RÉSUMÉ ═══ */
    .summary { display:flex; gap:12px; margin-bottom:20px; }
    .summary-card { flex:1; border:1px solid #E7E7E7; border-radius:8px; padding:12px; text-align:center; background:#FAFAFA; }
    .summary-card .value { font-size:22px; font-weight:700; color:#D39B23; }
    .summary-card .label { font-size:9px; color:#888; text-transform:uppercase; letter-spacing:0.5px; margin-top:2px; }
    .summary-card.green .value { color:#2E8B57; }
    .summary-card.orange .value { color:#D97706; }
    .summary-card.red .value { color:#D64545; }

    /* ═══ TABLEAU ═══ */
    table { width:100%; border-collapse:collapse; margin-bottom:18px; }
    th { background:#080808; color:white; padding:8px 10px; text-align:left; font-size:9px; text-transform:uppercase; letter-spacing:0.5px; }
    td { padding:7px 10px; border-bottom:1px solid #E7E7E7; font-size:10px; }
    tr:nth-child(even) td { background:#FAFAFA; }
    .text-center { text-align:center; }
    .text-right { text-align:right; }

    /* ═══ BADGES ═══ */
    .badge { display:inline-block; padding:3px 10px; border-radius:10px; font-size:9px; font-weight:700; }
    .badge-vert { background:#D4EDDA; color:#155724; }
    .badge-orange { background:#FFF3CD; color:#856404; }
    .badge-rouge { background:#F8D7DA; color:#721C24; }

    /* ═══ NOTE ═══ */
    .note { font-size:14px; font-weight:700; }
    .note-vert { color:#2E8B57; }
    .note-orange { color:#D97706; }
    .note-rouge { color:#D64545; }

    /* ═══ HISTORIQUE ═══ */
    .history-table th { background:#3B82C4; font-size:8px; padding:5px 6px; }
    .history-table td { padding:4px 6px; font-size:9px; text-align:center; }

    /* ═══ SECTION ═══ */
    .section-title { background:#D39B23; color:white; padding:8px 14px; border-radius:6px; font-weight:700; font-size:12px; margin:16px 0 8px 0; }
    .section-title.blue { background:#3B82C4; }
    .section-title.green { background:#2E8B57; }

    /* ═══ RENDU ═══ */
    .rendement-box { background:#F8F8F8; border:1px solid #E7E7E7; border-radius:6px; padding:8px 12px; margin-bottom:6px; font-size:9px; }
    .rendement-date { color:#D39B23; font-weight:700; font-size:8px; }
    .rendement-text { color:#555; margin-top:2px; }

    /* ═══ FICHE ═══ */
    .bulletin { page-break-inside:avoid; margin-bottom:24px; border:1px solid #E7E7E7; border-radius:10px; overflow:hidden; }
    .bulletin-header { background:linear-gradient(135deg,#080808 0%,#1a1a1a 100%); color:white; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; }
    .bulletin-header .name { font-size:14px; font-weight:700; }
    .bulletin-header .id { font-size:9px; color:#888; }
    .bulletin-body { padding:14px 18px; }

    /* ═══ FOOTER ═══ */
    .footer { text-align:center; color:#888; font-size:9px; margin-top:24px; padding-top:12px; border-top:1px solid #E7E7E7; }
    .page-break { page-break-before:always; }
</style>
</head>
<body>

<!-- ═══════════════ EN-TÊTE ═══════════════ -->
<div class="header">
    @if(!empty($logoBase64))
        <img src="{{ $logoBase64 }}" alt="Le Pharaon">
    @endif
    <h1>ÉVALUATIONS &amp; RENDEMENTS</h1>
    <h2>{{ strtoupper($titre) }}</h2>
    <div class="meta">
        Période : {{ $dateDebut }} au {{ $dateFin }} &mdash; Généré le {{ now()->format('d/m/Y à H:i') }}
        @if(!empty($auteur)) — {{ $auteur }}@endif
    </div>
</div>

<!-- ═══════════════ LÉGENDE ═══════════════ -->
<div class="legend">
    <span><span class="dot dot-vert"></span> Vert ≥ 14/20</span>
    <span><span class="dot dot-orange"></span> Orange 10–13/20</span>
    <span><span class="dot dot-rouge"></span> Rouge &lt; 10/20</span>
</div>

<!-- ═══════════════ RÉSUMÉ GLOBAL ═══════════════ -->
<div class="summary">
    <div class="summary-card">
        <div class="value">{{ $reports->count() }}</div>
        <div class="label">Employés évalués</div>
    </div>
    <div class="summary-card">
        <div class="value">{{ $reports->count() > 0 ? number_format($reports->avg('evaluation_note'), 1, ',', '') : '0' }}</div>
        <div class="label">Note moyenne /20</div>
    </div>
    <div class="summary-card green">
        <div class="value">{{ $reports->filter(fn($r) => $r->evaluation_couleur === 'vert')->count() }}</div>
        <div class="label">Verts</div>
    </div>
    <div class="summary-card orange">
        <div class="value">{{ $reports->filter(fn($r) => $r->evaluation_couleur === 'orange')->count() }}</div>
        <div class="label">Oranges</div>
    </div>
    <div class="summary-card red">
        <div class="value">{{ $reports->filter(fn($r) => $r->evaluation_couleur === 'rouge')->count() }}</div>
        <div class="label">Rouges</div>
    </div>
</div>

<!-- ═══════════════ TABLEAU RÉCAPITULATIF ═══════════════ -->
<div class="section-title">📋 Tableau récapitulatif</div>
<table>
    <thead>
        <tr>
            <th style="width:22%">Employé</th>
            <th class="text-center" style="width:10%">Présences</th>
            <th class="text-center" style="width:10%">Heures</th>
            <th class="text-center" style="width:12%">Note /20</th>
            <th class="text-center" style="width:10%">Couleur</th>
            <th class="text-center" style="width:10%">Rendements</th>
            <th style="width:26%">Commentaire</th>
        </tr>
    </thead>
    <tbody>
        @forelse($reports as $report)
        <tr>
            <td style="font-weight:600">{{ $report['name'] ?? $report->name ?? '-' }}</td>
            <td class="text-center">{{ $report['totalPresences'] ?? $report->total_presence ?? 0 }}</td>
            <td class="text-center">{{ $report['totalHeures'] ?? $report->total_heures ?? '-' }}</td>
            <td class="text-center">
                @php
                    $note = $report['evaluation_note'] ?? $report->evaluation_note ?? 0;
                    $couleur = $report['evaluation_couleur'] ?? $report->evaluation_couleur ?? 'orange';
                @endphp
                <span class="note note-{{ $couleur }}">{{ number_format($note, 1, ',', '') }}</span>
            </td>
            <td class="text-center">
                <span class="badge badge-{{ $couleur }}">
                    {{ strtoupper($couleur) }}
                </span>
            </td>
            <td class="text-center">
                @php $rendements = $report['rendements'] ?? $report->rendements ?? []; @endphp
                {{ is_array($rendements) ? count($rendements) : count((array)$rendements) }}
            </td>
            <td style="font-size:9px;color:#555">{{ $report['evaluation_commentaire'] ?? $report->evaluation_commentaire ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center" style="padding:20px;color:#888">Aucune donnée d'évaluation pour cette période.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<!-- ═══════════════ HISTORIQUE COMPARATIF 6 MOIS ═══════════════ -->
@if(!empty($historique) && count($historique) > 0)
<div class="section-title blue">📈 Évolution mensuelle (6 mois)</div>
<table class="history-table">
    <thead>
        <tr>
            <th>Employé</th>
            @foreach($historique[0] ?? [] as $mois => $data)
                <th>{{ $data['label'] ?? $mois }}</th>
            @endforeach
            <th style="background:#D39B23">Moyenne</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reports as $report)
        @php
            $empId = $report['employerID'] ?? $report->employerID ?? 0;
            $histEmp = $historique[$empId] ?? null;
        @endphp
        <tr>
            <td style="font-weight:600">{{ $report['name'] ?? $report->name ?? '-' }}</td>
            @if($histEmp)
                @foreach($histEmp as $mois => $data)
                    <td>
                        @if($data['note'] > 0)
                            <span class="note note-{{ $data['couleur'] }}">{{ number_format($data['note'], 1, ',', '') }}</span>
                        @else
                            <span style="color:#ccc">—</span>
                        @endif
                    </td>
                @endforeach
                @php
                    $notesVals = array_filter(array_column($histEmp, 'note'), fn($n) => $n > 0);
                    $moyEmp = count($notesVals) > 0 ? round(array_sum($notesVals) / count($notesVals), 1) : 0;
                @endphp
                <td style="font-weight:700;color:#D39B23">{{ number_format($moyEmp, 1, ',', '') }}</td>
            @else
                <td colspan="7" style="color:#ccc;text-align:center">Aucune donnée</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<!-- ═══════════════ FICHES INDIVIDUELLES ═══════════════ -->
<div class="page-break"></div>
<div class="section-title green">📄 Fiches individuelles</div>

@foreach($reports as $report)
@php
    $empId = $report['employerID'] ?? $report->employerID ?? 0;
    $nom = $report['name'] ?? $report->name ?? 'Employé #' . $empId;
    $note = $report['evaluation_note'] ?? $report->evaluation_note ?? 0;
    $couleur = $report['evaluation_couleur'] ?? $report->evaluation_couleur ?? 'orange';
    $commentaire = $report['evaluation_commentaire'] ?? $report->evaluation_commentaire ?? '-';
    $totalPresences = $report['totalPresences'] ?? $report->total_presence ?? 0;
    $totalHeures = $report['totalHeures'] ?? $report->total_heures ?? '-';
    $rendements = $report['rendements'] ?? $report->rendements ?? [];
    $manuelle = $report['evaluation_manuelle'] ?? $report->evaluation_manuelle ?? false;
@endphp
<div class="bulletin">
    <div class="bulletin-header">
        <div>
            <div class="name">{{ $nom }}</div>
            <div class="id">Employé #{{ $empId }}</div>
        </div>
        <div style="text-align:right">
            <div class="note note-{{ $couleur }}" style="font-size:20px;color:white">{{ number_format($note, 1, ',', '') }}/20</div>
            <div style="font-size:9px;color:#FACE4A;text-transform:uppercase;font-weight:700">{{ $couleur }}</div>
        </div>
    </div>
    <div class="bulletin-body">
        <div style="display:flex;gap:12px;margin-bottom:10px">
            <div class="summary-card" style="flex:1">
                <div class="value" style="font-size:16px;color:#080808">{{ $totalPresences }}</div>
                <div class="label">Jours de présence</div>
            </div>
            <div class="summary-card" style="flex:1">
                <div class="value" style="font-size:16px;color:#D39B23">{{ $totalHeures }}</div>
                <div class="label">Heures travaillées</div>
            </div>
            <div class="summary-card" style="flex:1">
                <div class="value" style="font-size:16px;color:#080808">{{ is_array($rendements) ? count($rendements) : count((array)$rendements) }}</div>
                <div class="label">Fiches rendement</div>
            </div>
        </div>

        <div style="margin-bottom:8px">
            <strong style="font-size:10px;color:#555">Évaluation :</strong>
            <span class="badge badge-{{ $couleur }}" style="margin-left:4px">{{ strtoupper($couleur) }}</span>
            @if($manuelle)<span style="font-size:8px;color:#888;margin-left:4px">(manuelle)</span>@endif
        </div>

        <div style="font-size:10px;color:#555;margin-bottom:8px">
            <strong>Commentaire :</strong> {{ $commentaire }}
        </div>

        @if(!empty($rendements) && count((array)$rendements) > 0)
        <div style="font-size:9px;color:#888;margin-bottom:4px;font-weight:600;text-transform:uppercase">Dernières fiches de rendement</div>
        @foreach(array_slice((array)$rendements, 0, 5) as $r)
            @if(is_object($r))
            <div class="rendement-box">
                <div class="rendement-date">{{ $r->date ?? '-' }}</div>
                <div class="rendement-text">{{ $r->rendement ?? '-' }}</div>
            </div>
            @elseif(is_string($r))
            <div class="rendement-box">
                <div class="rendement-text">{{ $r }}</div>
            </div>
            @endif
        @endforeach
        @if(count((array)$rendements) > 5)
            <div style="font-size:8px;color:#888;text-align:right">+ {{ count((array)$rendements) - 5 }} autres fiches…</div>
        @endif
        @endif
    </div>
</div>
@if($loop->iteration % 3 === 0 && !$loop->last)
<div class="page-break"></div>
@endif
@endforeach

<!-- ═══════════════ PIED DE PAGE ═══════════════ -->
<div class="footer">
    Le Pharaon — Système de Gestion de Présence &bull; Document généré automatiquement le {{ now()->format('d/m/Y à H:i') }}
    @if(!empty($titre)) — {{ $title }} @endif
</div>

</body>
</html>
