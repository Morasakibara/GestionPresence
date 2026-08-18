<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Comparatif des notes — {{ $equipe }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1f2937; margin: 0; padding: 0; }
        .container { width: 100%; padding: 20px; }
        .brand-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 2px solid #D39B23; padding-bottom: 12px; }
        .brand-header .brand-left { display: flex; align-items: center; }
        .logo { width: 55px; height: auto; margin-right: 12px; }
        .company-name { font-size: 22px; font-weight: bold; color: #D39B23; }
        .brand-header .right { font-size: 11px; color: #6b7280; text-align: right; }
        .header { background-color: #080808; color: white; padding: 18px 20px; margin-bottom: 24px; border-radius: 6px; border-left: 4px solid #D39B23; }
        .header h1 { font-size: 20px; font-weight: bold; margin: 0 0 6px 0; }
        .header p { margin: 3px 0; font-size: 13px; color: rgba(255,255,255,0.9); }
        .card { border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px 16px; margin-bottom: 16px; }
        .card h2 { font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #374151; margin: 0 0 10px 0; border-bottom: 1px solid #f3f4f6; padding-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        thead { background-color: #080808; color: white; }
        th { text-align: left; padding: 8px 10px; }
        td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; }
        .badge { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 10px; font-weight: bold; }
        .vert { background-color: rgba(22,163,74,0.12); color: #15803d; }
        .orange { background-color: rgba(249,115,22,0.12); color: #c2410c; }
        .rouge { background-color: rgba(220,38,38,0.12); color: #b91c1c; }
        .footer { margin-top: 24px; font-size: 10px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 10px; }
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
                Comparatif des notes — Équipe {{ $equipe }}<br>
                Généré le {{ $date }}
            </div>
        </div>

        <div class="header">
            <h1>Notes par membre — {{ $equipe }}</h1>
            <p>Comparaison des notes d'évaluation sur les 6 derniers mois</p>
        </div>

        @if(!empty($notesParEmploye))
        <div class="card">
            <h2>Tableau comparatif</h2>
            <table>
                <thead>
                    <tr>
                        <th>Employé</th>
                        @foreach(($notesParEmploye[0]['moisListe'] ?? []) as $m)
                            <th style="text-align:center;">{{ $m['label'] }}</th>
                        @endforeach
                        <th style="text-align:center;">Moyenne</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notesParEmploye as $emp)
                    <tr>
                        <td style="font-weight:bold;">{{ $emp['nom'] }}</td>
                        @foreach($emp['moisListe'] as $m)
                            @php $cell = $emp['notes'][$m['mois']] ?? null; @endphp
                            <td style="text-align:center;">
                                @if($cell && $cell['note'] > 0)
                                    <span class="badge {{ $cell['couleur'] }}">{{ $cell['note'] }}/20</span>
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td style="text-align:center;">
                            <span class="badge {{ $emp['couleur'] }}">{{ $emp['moyenne'] }}/20</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>Légende</h2>
            <p style="font-size:11px; color:#6b7280; margin:0;">
                🟢 Vert ≥ 14/20 — Excellent &nbsp;&nbsp;|&nbsp;&nbsp;
                🟠 Orange 10-13/20 — Satisfaisant &nbsp;&nbsp;|&nbsp;&nbsp;
                🔴 Rouge &lt; 10/20 — Critique
            </p>
        </div>
        @else
        <div class="card">
            <p style="text-align:center; color:#9ca3af;">Aucune donnée disponible pour cette équipe.</p>
        </div>
        @endif

        <div class="footer">
            Document généré automatiquement par l'application de gestion de présence Le Pharaon.
        </div>
    </div>
</body>
</html>
