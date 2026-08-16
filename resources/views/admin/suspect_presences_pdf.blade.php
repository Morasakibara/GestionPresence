<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Présences suspectes</title>
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
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #115293;
        }
        .header {
            background-color: #115293;
            color: white;
            padding: 20px;
            margin-bottom: 24px;
            border-radius: 6px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 8px 0;
        }
        .header p {
            margin: 4px 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
        }
        .table-container {
            margin-bottom: 24px;
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
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1;
            border-radius: 9999px;
            background-color: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
        }
        .statut {
            display: inline-block;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1;
            border-radius: 9999px;
        }
        .statut-nouveau { background-color: rgba(239, 68, 68, 0.1); color: #b91c1c; }
        .statut-examiné, .statut-justifié { background-color: rgba(25, 118, 210, 0.1); color: #115293; }
        .statut-rejeté { background-color: rgba(107, 114, 128, 0.1); color: #374151; }
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
            background-color: #f9fafb;
            border-radius: 6px;
            margin-bottom: 24px;
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
        <div class="brand-header">
            <div class="company-name">3HCIG COOP-CA</div>
        </div>

        <div class="header">
            <h1>Présences suspectes</h1>
            <p>Période:
                @if($startDate && $endDate)
                    {{ date('d/m/Y', strtotime($startDate)) }} au {{ date('d/m/Y', strtotime($endDate)) }}
                @else
                    Période complète
                @endif
            </p>
            @if($search)
                <p>Recherche: {{ $search }}</p>
            @endif
            @if(!empty($statut))
                <p>Statut: {{ ucfirst($statut) }}</p>
            @endif
            <p>Généré le: {{ $generatedDate }}</p>
            <p>Administrateur: {{ $admin }}</p>
        </div>

        @if(count($suspectPresences) > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Date</th>
                            <th>Arrivée</th>
                            <th>Départ</th>
                            <th>Statut</th>
                            <th>Distance (km)</th>
                            <th>Vitesse (km/h)</th>
                            <th>Motif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suspectPresences as $p)
                        <tr>
                            <td>{{ $p->employer_nom }}<br><small style="color:#6b7280;">{{ $p->employer_email }}</small></td>
                            <td>{{ date('d/m/Y', strtotime($p->date)) }}</td>
                            <td>{{ $p->heureArrivee ? date('H:i', strtotime($p->heureArrivee)) : '-' }}</td>
                            <td>{{ $p->heureDepart ? date('H:i', strtotime($p->heureDepart)) : '-' }}</td>
                            <td>
                                <span class="statut statut-{{ $p->statut_traitement ?? 'nouveau' }}">
                                    {{ $p->statut_traitement ?? 'nouveau' }}
                                </span>
                            </td>
                            <td>{{ $p->distance_km ? number_format($p->distance_km, 2, ',', '') : '-' }}</td>
                            <td>{{ $p->vitesse_kmh ? number_format($p->vitesse_kmh, 2, ',', '') : '-' }}</td>
                            <td>{{ $p->motif_suspicion ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="no-data">
                <h3>Aucune présence suspecte</h3>
                <p>Aucun pointage suspect pour les critères sélectionnés.</p>
            </div>
        @endif

        <div class="footer">
            <p>© {{ date('Y') }} 3HCIG COOP-CA. Ce document est généré automatiquement et est confidentiel.</p>
        </div>
    </div>
</body>
</html>
