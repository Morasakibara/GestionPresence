<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Comparatif des notes — {{ $equipe }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:"Segoe UI","Helvetica Neue",Arial,sans-serif; color:#080808; font-size:11px; line-height:1.5; }

    .header { background:#080808; color:white; padding:24px 30px; text-align:center; margin-bottom:20px; }
    .header img { height:55px; margin-bottom:8px; }
    .header h1 { color:#D39B23; font-size:22px; margin-bottom:2px; letter-spacing:1px; }
    .header h2 { color:#FACE4A; font-size:14px; font-weight:400; }
    .header .meta { color:#888; font-size:11px; margin-top:8px; }

    .legend { display:flex; gap:16px; justify-content:center; margin-bottom:18px; font-size:10px; }
    .legend span { display:inline-flex; align-items:center; gap:4px; }
    .dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
    .dot-vert { background:#2E8B57; }
    .dot-orange { background:#D97706; }
    .dot-rouge { background:#D64545; }

    .summary { display:flex; gap:12px; margin-bottom:20px; }
    .summary-card { flex:1; border:1px solid #E7E7E7; border-radius:8px; padding:12px; text-align:center; background:#FAFAFA; }
    .summary-card .value { font-size:22px; font-weight:700; color:#D39B23; }
    .summary-card .label { font-size:9px; color:#888; text-transform:uppercase; letter-spacing:0.5px; margin-top:2px; }
    .summary-card.green .value { color:#2E8B57; }
    .summary-card.orange .value { color:#D97706; }
    .summary-card.red .value { color:#D64545; }

    table { width:100%; border-collapse:collapse; margin-bottom:18px; }
    th { background:#080808; color:white; padding:8px 10px; text-align:left; font-size:9px; text-transform:uppercase; letter-spacing:0.5px; }
    td { padding:7px 10px; border-bottom:1px solid #E7E7E7; font-size:10px; }
    tr:nth-child(even) td { background:#FAFAFA; }
    .text-center { text-align:center; }

    .badge { display:inline-block; padding:3px 10px; border-radius:10px; font-size:9px; font-weight:700; }
    .badge-vert { background:#D4EDDA; color:#155724; }
    .badge-orange { background:#FFF3CD; color:#856404; }
    .badge-rouge { background:#F8D7DA; color:#721C24; }

    .note { font-size:14px; font-weight:700; }
    .note-vert { color:#2E8B57; }
    .note-orange { color:#D97706; }
    .note-rouge { color:#D64545; }

    .section-title { background:#D39B23; color:white; padding:8px 14px; border-radius:6px; font-weight:700; font-size:12px; margin:16px 0 8px 0; }

    .footer { text-align:center; color:#888; font-size:9px; margin-top:24px; padding-top:12px; border-top:1px solid #E7E7E7; }
</style>
</head>
<body>

<!-- ═══════════════ EN-TÊTE ═══════════════ -->
<div class="header">
    @php
        $logoPath = public_path('storage/avatars/logo-pharaon.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    @endphp
    @if($logoBase64)
        <img src="{{ $logoBase64 }}" alt="Le Pharaon">
    @endif
    <h1>COMPARATIF DES NOTES</h1>
    <h2>ÉQUIPE {{ strtoupper($equipe) }}</h2>
    <div class="meta">Comparaison des notes d'évaluation sur 6 mois — Généré le {{ $date }}</div>
</div>

<!-- ═══════════════ LÉGENDE ═══════════════ -->
<div class="legend">
    <span><span class="dot dot-vert"></span> Vert ≥ 14/20</span>
    <span><span class="dot dot-orange"></span> Orange 10–13/20</span>
    <span><span class="dot dot-rouge"></span> Rouge &lt; 10/20</span>
</div>

@if(!empty($notesParEmploye))
<!-- ═══════════════ RÉSUMÉ ═══════════════ -->
@php
    $total = count($notesParEmploye);
    $moyennes = array_map(fn($e) => $e['moyenne'], $notesParEmploye);
    $moyGlobale = $total > 0 ? round(array_sum($moyennes) / $total, 1) : 0;
    $verts = count(array_filter($notesParEmploye, fn($e) => $e['couleur'] === 'vert'));
    $oranges = count(array_filter($notesParEmploye, fn($e) => $e['couleur'] === 'orange'));
    $rouges = count(array_filter($notesParEmploye, fn($e) => $e['couleur'] === 'rouge'));
@endphp
<div class="summary">
    <div class="summary-card">
        <div class="value">{{ $total }}</div>
        <div class="label">Membres</div>
    </div>
    <div class="summary-card">
        <div class="value">{{ number_format($moyGlobale, 1, ',', '') }}</div>
        <div class="label">Note moyenne</div>
    </div>
    <div class="summary-card green">
        <div class="value">{{ $verts }}</div>
        <div class="label">Verts</div>
    </div>
    <div class="summary-card orange">
        <div class="value">{{ $oranges }}</div>
        <div class="label">Oranges</div>
    </div>
    <div class="summary-card red">
        <div class="value">{{ $rouges }}</div>
        <div class="label">Rouges</div>
    </div>
</div>

<!-- ═══════════════ TABLEAU ═══════════════ -->
<div class="section-title">📊 Tableau comparatif des notes</div>
<table>
    <thead>
        <tr>
            <th style="width:20%">Employé</th>
            @foreach(($notesParEmploye[0]['moisListe'] ?? []) as $m)
                <th class="text-center" style="width:12%">{{ $m['label'] }}</th>
            @endforeach
            <th class="text-center" style="width:10%;background:#D39B23">Moyenne</th>
        </tr>
    </thead>
    <tbody>
        @foreach($notesParEmploye as $emp)
        <tr>
            <td style="font-weight:600">{{ $emp['nom'] }}</td>
            @foreach($emp['moisListe'] as $m)
                @php $cell = $emp['notes'][$m['mois']] ?? null; @endphp
                <td class="text-center">
                    @if($cell && $cell['note'] > 0)
                        <span class="note note-{{ $cell['couleur'] }}">{{ number_format($cell['note'], 1, ',', '') }}</span>
                    @else
                        <span style="color:#ccc">—</span>
                    @endif
                </td>
            @endforeach
            <td class="text-center">
                <span class="badge badge-{{ $emp['couleur'] }}">{{ number_format($emp['moyenne'], 1, ',', '') }}</span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- ═══════════════ CLASSEMENT ═══════════════ -->
<div class="section-title" style="background:#2E8B57">🏆 Classement par moyenne</div>
<table>
    <thead>
        <tr>
            <th style="width:8%">#</th>
            <th>Employé</th>
            <th class="text-center" style="width:15%">Moyenne /20</th>
            <th class="text-center" style="width:15%">Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($notesParEmploye as $i => $emp)
        <tr>
            <td style="font-weight:700;color:#D39B23">{{ $i + 1 }}</td>
            <td style="font-weight:600">{{ $emp['nom'] }}</td>
            <td class="text-center">
                <span class="note note-{{ $emp['couleur'] }}">{{ number_format($emp['moyenne'], 1, ',', '') }}</span>
            </td>
            <td class="text-center">
                <span class="badge badge-{{ $emp['couleur'] }}">
                    {{ $emp['couleur'] === 'vert' ? 'EXCELLENT' : ($emp['couleur'] === 'orange' ? 'SATISFAISANT' : 'CRITIQUE') }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@else
<div style="text-align:center;padding:40px;color:#888">Aucune donnée disponible pour cette équipe.</div>
@endif

<div class="footer">
    Le Pharaon — Système de Gestion de Présence &bull; Document généré le {{ $date }}
</div>

</body>
</html>
