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
        .section-title { font-size: 13px; font-weight: bold; color: #080808; margin: 15px 0 8px; padding: 6px 10px; background: #FBF3E6; border-left: 4px solid #D39B23; border-radius: 0 6px 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #080808; color: #D39B23; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e5e5; font-size: 10px; }
        tr:nth-child(even) { background: #fafafa; }
        .badge-green { background: #edf6f1; color: #2E8B57; padding: 2px 8px; border-radius: 10px; font-weight: bold; }
        .badge-orange { background: #fef3c7; color: #D97706; padding: 2px 8px; border-radius: 10px; font-weight: bold; }
        .badge-red { background: #fbeeec; color: #D64545; padding: 2px 8px; border-radius: 10px; font-weight: bold; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 2px solid #D39B23; font-size: 9px; color: #888; text-align: center; }
        .stats { display: flex; gap: 15px; margin-bottom: 15px; }
        .stat-box { flex: 1; text-align: center; padding: 10px; border-radius: 8px; border: 1px solid #e5e5e5; }
        .stat-value { font-size: 22px; font-weight: bold; color: #080808; }
        .stat-label { font-size: 9px; color: #888; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏖️ Planning des Congés Approuvés</h1>
        <p>{{ $moisLabel }} · Le Pharaon · Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="stat-value">{{ $conges->count() }}</div>
            <div class="stat-label">Total demandes approuvées</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $conges->sum(fn($c) => $c->dureeJours()) }}</div>
            <div class="stat-label">Total jours</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $conges->pluck('employer_id')->unique()->count() }}</div>
            <div class="stat-label">Employés concernés</div>
        </div>
    </div>

    @foreach($semaines as $semaine)
    <div class="section-title">
        Semaine {{ $semaine['debut']->format('d/m') }} — {{ $semaine['fin']->format('d/m/Y') }}
    </div>
    @if($semaine['conges']->isEmpty())
        <p style="padding: 8px 10px; color: #888; font-size: 10px;">Aucun congé cette semaine.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>Employé</th>
                <th>Type</th>
                <th>Du</th>
                <th>Au</th>
                <th>Jours</th>
                <th>Motif</th>
            </tr>
        </thead>
        <tbody>
            @foreach($semaine['conges'] as $c)
            <tr>
                <td><strong>{{ $c->employe->nom ?? 'N/A' }}</strong></td>
                <td>{{ $c->typeLabel() }}</td>
                <td>{{ $c->date_debut->format('d/m') }}</td>
                <td>{{ $c->date_fin->format('d/m') }}</td>
                <td><strong>{{ $c->dureeJours() }}</strong></td>
                <td>{{ \Illuminate\Support\Str::limit($c->motif, 60) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endforeach

    <div class="footer">
        Document généré automatiquement — {{ $admin->nom ?? 'Admin' }} · Le Pharaon · {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
