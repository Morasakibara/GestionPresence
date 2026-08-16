<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bilan hebdomadaire des présences suspectes</title>
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
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #115293;
            margin-bottom: 16px;
        }
        .header {
            background-color: #115293;
            color: white;
            padding: 20px;
            margin-bottom: 24px;
            border-radius: 6px;
        }
        .header h1 {
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 8px 0;
        }
        .header p {
            margin: 4px 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
        }
        .summary {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .summary h2 {
            font-size: 16px;
            font-weight: 600;
            color: #115293;
            margin: 0 0 12px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary p {
            margin: 6px 0;
            color: #4b5563;
        }
        .summary strong {
            color: #1f2937;
        }
        .table-container {
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }
        thead {
            background-color: #115293;
            color: white;
        }
        th {
            text-align: left;
            padding: 10px 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="company-name">3HCIG COOP-CA</div>

        <div class="header">
            <h1>Bilan hebdomadaire des présences suspectes</h1>
            <p>Période: {{ $periode }}</p>
            <p>Généré le: {{ $generatedDate }}</p>
            <p>Administrateur: {{ $admin }}</p>
        </div>

        <div class="summary">
            <h2>Synthèse</h2>
            @foreach($lignes as $label => $value)
                <p><strong>{{ $label }}:</strong> {{ $value }}</p>
            @endforeach
        </div>

        @if(count($details) > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($details as $d)
                        <tr>
                            <td>{{ $d->employer_nom }}</td>
                            <td>{{ date('d/m/Y', strtotime($d->date)) }}</td>
                            <td>{{ $d->statut_traitement ?? 'nouveau' }}</td>
                            <td>{{ $d->motif_suspicion ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="footer">
            <p>© {{ date('Y') }} 3HCIG COOP-CA. Ce document est généré automatiquement et est confidentiel.</p>
        </div>
    </div>
</body>
</html>
