<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques des présences suspectes</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.5;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .container { width: 100%; padding: 20px; }
        .company-name { font-size: 22px; font-weight: bold; color: #115293; margin-bottom: 20px; }
        .header {
            background-color: #115293; color: white; padding: 20px; margin-bottom: 24px; border-radius: 6px;
        }
        .header h1 { font-size: 24px; font-weight: bold; margin: 0 0 8px 0; }
        .header p { margin: 4px 0; font-size: 14px; color: rgba(255, 255, 255, 0.9); }
        .cards { width: 100%; margin-bottom: 24px; }
        .card {
            display: inline-block; width: 22%; vertical-align: top; background-color: #f9fafb;
            border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; margin-right: 2%;
        }
        .card:last-child { margin-right: 0; }
        .card .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; }
        .card .value { font-size: 22px; font-weight: bold; color: #111827; margin-top: 4px; }
        .section { margin-bottom: 24px; }
        .section h2 {
            font-size: 14px; font-weight: bold; color: #115293; border-bottom: 2px solid #115293;
            padding-bottom: 6px; margin-bottom: 12px;
        }
        table { width: 100%; border-collapse: collapse; background-color: white; margin-bottom: 12px; }
        thead { background-color: #115293; color: white; }
        th { text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 12px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .badge { display: inline-block; padding: 3px 7px; font-size: 10px; font-weight: 500; border-radius: 9999px; }
        .badge-nouveau { background-color: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .badge-examiné { background-color: rgba(25, 118, 210, 0.1); color: #115293; }
        .badge-justifié { background-color: rgba(22, 163, 74, 0.1); color: #15803d; }
        .badge-rejeté { background-color: rgba(107, 114, 128, 0.1); color: #374151; }
        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="company-name">3HCIG COOP-CA</div>

        <div class="header">
            <h1>Statistiques des présences suspectes</h1>
            <p>Périmètre: Ensemble de l'entreprise</p>
            <p>Généré le: {{ $generatedDate }}</p>
        </div>

        <div class="cards">
            <div class="card">
                <div class="label">Suspectes</div>
                <div class="value">{{ $totalSuspectes }}</div>
            </div>
            <div class="card">
                <div class="label">Contestations</div>
                <div class="value">{{ $totalContestations }}</div>
            </div>
            <div class="card">
                <div class="label">Employés bloqués</div>
                <div class="value">{{ $employesBloques->count() }}</div>
            </div>
            <div class="card">
                <div class="label">Non traitées</div>
                <div class="value">{{ $parStatut['nouveau'] ?? 0 }}</div>
            </div>
        </div>

        <div class="section">
            <h2>Répartition par statut de traitement</h2>
            <table>
                <thead><tr><th>Statut</th><th>Nombre</th></tr></thead>
                <tbody>
                    @foreach(['nouveau' => 'En attente', 'examiné' => 'Examinées', 'justifié' => 'Justifiées', 'rejeté' => 'Rejetées'] as $cle => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td><span class="badge badge-{{ $cle }}">{{ $parStatut[$cle] ?? 0 }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Motifs de suspicion</h2>
            <table>
                <thead><tr><th>Motif</th><th>Nombre</th></tr></thead>
                <tbody>
                    @foreach($motifCounts as $label => $count)
                        <tr><td>{{ $label }}</td><td>{{ $count }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Contestations des employés</h2>
            <table>
                <thead><tr><th>État</th><th>Nombre</th></tr></thead>
                <tbody>
                    <tr><td>En attente</td><td>{{ $contestationsEnAttente }}</td></tr>
                    <tr><td>Acceptées</td><td>{{ $contestationsAccordees }}</td></tr>
                    <tr><td>Refusées</td><td>{{ $contestationsRefusees }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Détail par employé</h2>
            @if($detailParEmploye->isEmpty())
                <p style="color:#6b7280;">Aucune présence suspecte.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Employé</th><th>Total</th><th>En attente</th>
                            <th>Examinées</th><th>Justifiées</th><th>Rejetées</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detailParEmploye as $ligne)
                            <tr>
                                <td>{{ $ligne->nom }}</td>
                                <td><b>{{ $ligne->total }}</b></td>
                                <td>{{ $ligne->nouveau }}</td>
                                <td>{{ $ligne->examine }}</td>
                                <td>{{ $ligne->justifie }}</td>
                                <td>{{ $ligne->rejete }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} 3HCIG COOP-CA. Ce document est généré automatiquement et est confidentiel.</p>
        </div>
    </div>
</body>
</html>
