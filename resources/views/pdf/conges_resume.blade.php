<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 11px; color: #111; }
        .header { background: #080808; color: white; padding: 15px 20px; margin-bottom: 20px; border-radius: 8px; }
        .header h1 { font-size: 18px; color: #D39B23; margin-bottom: 4px; }
        .header p { font-size: 11px; color: #aaa; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #080808; color: #D39B23; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e5e5; font-size: 10px; }
        .stats { display: flex; gap: 12px; margin-bottom: 15px; }
        .stat-box { flex: 1; text-align: center; padding: 12px; border-radius: 8px; border: 1px solid #e5e5e5; }
        .stat-value { font-size: 24px; font-weight: bold; color: #080808; }
        .stat-label { font-size: 9px; color: #888; margin-top: 2px; }
        .section-title { font-size: 13px; font-weight: bold; margin: 15px 0 8px; padding: 6px 10px; background: #FBF3E6; border-left: 4px solid #D39B23; border-radius: 0 6px 6px 0; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 2px solid #D39B23; font-size: 9px; color: #888; text-align: center; }
        .badge-green { background: #edf6f1; color: #2E8B57; padding: 2px 8px; border-radius: 10px; font-weight: bold; font-size: 9px; }
        .badge-orange { background: #fef3c7; color: #D97706; padding: 2px 8px; border-radius: 10px; font-weight: bold; font-size: 9px; }
        .badge-red { background: #fbeeec; color: #D64545; padding: 2px 8px; border-radius: 10px; font-weight: bold; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Résumé des Congés & Absences</h1>
        <p>Le Pharaon · Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #D97706">{{ $stats['en_attente'] }}</div>
            <div class="stat-label">⏳ En attente</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #2E8B57">{{ $stats['approuves'] }}</div>
            <div class="stat-label">✅ Approuvés</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #D64545">{{ $stats['refuses'] }}</div>
            <div class="stat-label">❌ Refusés</div>
        </div>
    </div>

    <div class="section-title">🏆 Top 10 — Employés avec le plus de congés</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employé</th>
                <th>Total demandes</th>
                <th>Approuvées</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topConges as $i => $emp)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $emp['nom'] }}</strong></td>
                <td>{{ $emp['total'] }}</td>
                <td>{{ $emp['approuves'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">📜 Dernières demandes</div>
    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Type</th>
                <th>Période</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentes as $c)
            <tr>
                <td>{{ $c->employe->nom ?? 'N/A' }}</td>
                <td>{{ $c->typeLabel() }}</td>
                <td>{{ $c->date_debut->format('d/m') }} → {{ $c->date_fin->format('d/m') }}</td>
                <td>
                    @if($c->statut === 'approuve')
                        <span class="badge-green">✅ Approuvé</span>
                    @elseif($c->statut === 'refuse')
                        <span class="badge-red">❌ Refusé</span>
                    @else
                        <span class="badge-orange">⏳ En attente</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Document généré automatiquement — {{ $admin->nom ?? 'Admin' }} · Le Pharaon
    </div>
</body>
</html>
