<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin d'évaluation - {{ $employe->nom }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.5;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 2px solid #D39B23;
            padding-bottom: 12px;
        }
        .brand-header .brand-left {
            display: flex;
            align-items: center;
        }
        .logo {
            width: 55px;
            height: auto;
            margin-right: 12px;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #D39B23;
        }
        .brand-header .right {
            font-size: 11px;
            color: #6b7280;
            text-align: right;
        }
        .header {
            background-color: #080808;
            color: white;
            padding: 18px 20px;
            margin-bottom: 24px;
            border-radius: 6px;
            border-left: 4px solid #D39B23;
        }
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 6px 0;
        }
        .header p {
            margin: 3px 0;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
        }
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .card h2 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #374151;
            margin: 0 0 10px 0;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 6px;
        }
        .note-badge {
            display: inline-block;
            border-radius: 999px;
            padding: 6px 18px;
            font-size: 20px;
            font-weight: bold;
        }
        .vert { background-color: rgba(22,163,74,0.12); color: #15803d; }
        .orange { background-color: rgba(249,115,22,0.12); color: #c2410c; }
        .rouge { background-color: rgba(220,38,38,0.12); color: #b91c1c; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        thead {
            background-color: #080808;
            color: white;
        }
        th {
            text-align: left;
            padding: 8px 10px;
        }
        td {
            padding: 7px 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .stats-grid {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .stat {
            flex: 1;
            min-width: 90px;
            text-align: center;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 6px;
            background-color: #f9fafb;
        }
        .stat .value {
            font-size: 18px;
            font-weight: bold;
            color: #885910;
        }
        .stat .label {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }
        .footer {
            margin-top: 24px;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .legend { font-size: 10px; color: #6b7280; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand-header">
            <div class="brand-left">
                <img class="logo" src="{{ public_path('storage/avatars/logo-pharaon.png') }}" alt="Le Pharaon">
                <div class="company-name">Le Pharaon</div>
            </div>
            <div class="right">
                Bulletin individuel d'évaluation<br>
                Généré le {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>

        <div class="header">
            <h1>{{ $employe->nom }}</h1>
            <p>Mois : {{ \Illuminate\Support\Carbon::parse($mois . '-01')->locale('fr')->isoFormat('MMMM YYYY') }}</p>
            <p>Période : du {{ date('d/m/Y', strtotime($debut)) }} au {{ date('d/m/Y', strtotime($fin)) }}</p>
        </div>

        <div class="card">
            <h2>Évaluation du mois</h2>
            <div style="text-align:center; margin-bottom:10px;">
                <span class="note-badge {{ $evaluation['couleur'] }}">
                    {{ $evaluation['note'] }}/20
                </span>
                <div style="margin-top:6px; font-size:12px; color:#6b7280;">
                    @if($evaluation['couleur'] === 'vert') 🟢 Excellent — discipline et rendement au rendez-vous
                    @elseif($evaluation['couleur'] === 'orange') 🟠 Satisfaisant — à surveiller
                    @else 🔴 Critique — attention discipline et rendement @endif
                    @if($evaluation['manuelle']) (évaluation manuelle de la hiérarchie) @endif
                </div>
            </div>
            <p style="font-size:12px; color:#374151;">{{ $evaluation['commentaire'] }}</p>
            <p class="legend">Légende : 🟢 Vert ≥ 14/20 · 🟠 Orange 10-13/20 · 🔴 Rouge &lt; 10/20</p>
        </div>

        @if(!empty($historique))
        <div class="card">
            <h2>Évolution de la note (6 derniers mois)</h2>
            @php
                $maxNote = 20;
                $couleurHex = [
                    'vert' => '#2E8B57',
                    'orange' => '#D97706',
                    'rouge' => '#D64545',
                ];
                $notesWithValues = array_column(array_filter($historique, fn($h) => $h['note'] > 0), 'note');
                $moyEvol = count($notesWithValues) > 0 ? round(array_sum($notesWithValues) / count($notesWithValues), 1) : 0;
                $tendanceLabel = '—';
                $tendanceCouleur = 'vert';
                $deltaVal = 0;
                if (count($notesWithValues) >= 2) {
                    $prev = $notesWithValues[count($notesWithValues) - 2];
                    $last = end($notesWithValues);
                    $deltaVal = round($last - $prev, 1);
                    if ($deltaVal > 0.5) { $tendanceLabel = '▲ Hausse'; $tendanceCouleur = 'vert'; }
                    elseif ($deltaVal < -0.5) { $tendanceLabel = '▼ Baisse'; $tendanceCouleur = 'rouge'; }
                    else { $tendanceLabel = '— Stable'; $tendanceCouleur = 'orange'; }
                }
            @endphp
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    @foreach($historique as $h)
                    <td style="text-align:center; vertical-align:bottom; padding:0 4px;">
                        <div style="display:inline-block; width:60%; height:{{ max(4, round(($h['note'] / $maxNote) * 90)) }}px; background-color:{{ $couleurHex[$h['couleur']] ?? '#888888' }}; border-radius:3px 3px 0 0;"></div>
                    </td>
                    @endforeach
                </tr>
                <tr>
                    @foreach($historique as $h)
                    <td style="text-align:center; font-size:9px; color:#6b7280; padding-top:4px;">{{ \Illuminate\Support\Str::limit($h['label'], 3, '') }}</td>
                    @endforeach
                </tr>
                <tr>
                    @foreach($historique as $h)
                    <td style="text-align:center; font-size:10px; font-weight:bold; color:#374151; padding-top:2px;">{{ $h['note'] }}/20</td>
                    @endforeach
                </tr>
            </table>
            <p class="legend">Évolution mensuelle de la note sur 20 — 🟢 Vert ≥ 14 · 🟠 Orange 10-13 · 🔴 Rouge &lt; 10</p>
            <div style="display:flex; gap:16px; margin-top:14px;">
                <div style="flex:1; border:1px solid #e5e7eb; border-radius:6px; padding:10px; text-align:center; background:#f9fafb;">
                    <div style="font-size:20px; font-weight:bold; color:#885910;">{{ $moyEvol }}/20</div>
                    <div style="font-size:10px; color:#6b7280; margin-top:2px;">Moyenne (6 mois)</div>
                </div>
                <div style="flex:1; border:1px solid #e5e7eb; border-radius:6px; padding:10px; text-align:center; background:#f9fafb;">
                    <div style="font-size:16px; font-weight:bold; color:{{ $tendanceCouleur === 'vert' ? '#15803d' : ($tendanceCouleur === 'rouge' ? '#b91c1c' : '#92400e') }};">
                        {{ $tendanceLabel }} {{ $deltaVal != 0 ? ($deltaVal > 0 ? '+' : '') . $deltaVal . ' pt' : '' }}
                    </div>
                    <div style="font-size:10px; color:#6b7280; margin-top:2px;">Tendance</div>
                </div>
            </div>
        </div>
        @endif

        <div class="card">
            <h2>Statistiques de la période</h2>
            <div class="stats-grid">
                <div class="stat">
                    <div class="value">{{ $stats['presences_completes'] }}</div>
                    <div class="label">Présences</div>
                </div>
                <div class="stat">
                    <div class="value">{{ $stats['retards'] }}</div>
                    <div class="label">Retards</div>
                </div>
                <div class="stat">
                    <div class="value">{{ $stats['absences'] }}</div>
                    <div class="label">Absences</div>
                </div>
                <div class="stat">
                    <div class="value">{{ $stats['suspectes'] }}</div>
                    <div class="label">Suspectes</div>
                </div>
                <div class="stat">
                    <div class="value">{{ $stats['rendements_remplis'] }}</div>
                    <div class="label">Fiches remplies</div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Tâches effectuées (fiches de rendement)</h2>
            @if(count($rendements) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Arrivée</th>
                            <th>Départ</th>
                            <th>Tâches effectuées</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rendements as $r)
                        <tr>
                            <td style="white-space:nowrap;">{{ date('d/m/Y', strtotime($r->date)) }}</td>
                            <td>{{ $r->heureArrivee ? date('H:i', strtotime($r->heureArrivee)) : '-' }}</td>
                            <td>{{ $r->heureDepart ? date('H:i', strtotime($r->heureDepart)) : '-' }}</td>
                            <td>{{ $r->rendement }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="font-size:12px; color:#9ca3af;">Aucune fiche de rendement remplie sur la période.</p>
            @endif
        </div>

        <div class="footer">
            Document généré automatiquement par l'application de gestion de présence Le Pharaon.
        </div>
    </div>
</body>
</html>
