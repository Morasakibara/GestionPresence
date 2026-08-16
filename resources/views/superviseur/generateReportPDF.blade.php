<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport de présence - Équipe {{ $equipe }}</title>
    <style>
        /* Styles de base */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.5;
            color: #1f2937; /* gray-800 */
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        
        /* En-tête */
        .header {
            background-color: #115293; /* 3hcig-blue-dark */
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
        
        /* Tableau */
        .table-container {
            margin-bottom: 24px;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }
        thead {
            background-color: #115293; /* 3hcig-blue-dark */
            color: white;
        }
        th {
            text-align: left;
            padding: 12px 16px;
            font-size: 12px;
            font-weight: medium;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        td {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb; /* gray-200 */
            font-size: 14px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb; /* gray-50 */
        }
        tr:hover {
            background-color: #f3f4f6; /* gray-100 */
        }
        
        /* Badge pour les présences */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 500;
            line-height: 1;
            border-radius: 9999px;
            background-color: rgba(25, 118, 210, 0.1); /* 3hcig-blue avec opacité */
            color: #115293; /* 3hcig-blue-dark */
        }
        
        /* Résumé */
        .summary {
            background-color: #f3f4f6; /* gray-100 */
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 24px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .summary h2 {
            font-size: 18px;
            font-weight: 600;
            color: #115293; /* 3hcig-blue-dark */
            margin-top: 0;
            margin-bottom: 12px;
        }
        .summary p {
            margin: 8px 0;
            color: #4b5563; /* gray-600 */
        }
        .summary strong {
            color: #1f2937; /* gray-800 */
        }
        
        /* Signature */
        .signature-section {
            margin-top: 40px;
            margin-bottom: 24px;
        }
        .signature-section p {
            font-size: 14px;
            color: #4b5563; /* gray-600 */
            margin-bottom: 8px;
        }
        .signature-line {
            height: 0;
            border-top: 1px solid #9ca3af; /* gray-400 */
            width: 200px;
            margin-top: 60px;
        }
        .signature-name {
            font-size: 14px;
            margin-top: 8px;
        }
        
        /* Message absence de données */
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280; /* gray-500 */
            background-color: #f9fafb; /* gray-50 */
            border-radius: 6px;
            margin-bottom: 24px;
        }
        .no-data svg {
            width: 48px;
            height: 48px;
            margin: 0 auto 16px;
            display: block;
            color: #9ca3af; /* gray-400 */
        }
        .no-data h3 {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #374151; /* gray-700 */
        }
        
        /* Pied de page */
        .footer {
            margin-top: 40px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb; /* gray-200 */
            font-size: 12px;
            color: #6b7280; /* gray-500 */
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rapport de présence - Équipe {{ $equipe }}</h1>
            <p>Généré le: {{ $date }}</p>
            <p>Superviseur: {{ $superviseur }}</p>
        </div>

        @if(count($reports) > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nom de l'employé</th>
                            <th>Total présences (Mois en cours)</th>
                            <th>Évaluation</th>
                            <th>Réalisations (rendement)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>{{ $report['name'] }}</td>
                            <td><span class="badge">{{ $report['totalPresences'] }}</span></td>
                            <td>
                                <span class="badge" style="{{ $report['evaluation_couleur'] === 'vert' ? 'background-color: rgba(22,163,74,0.12); color:#15803d;' : ($report['evaluation_couleur'] === 'rouge' ? 'background-color: rgba(220,38,38,0.12); color:#b91c1c;' : 'background-color: rgba(249,115,22,0.12); color:#c2410c;') }}">
                                    {{ $report['evaluation_note'] }}/20
                                </span>
                            </td>
                            <td style="font-size:11px; max-width:280px;">
                                @if(count($report['rendements']) > 0)
                                    @foreach($report['rendements'] as $rendement)
                                        • {{ \Illuminate\Support\Str::limit($rendement, 140) }}<br>
                                    @endforeach
                                @else
                                    <span style="color:#9ca3af;">Aucune fiche remplie</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="summary">
                <h2>Résumé du rapport</h2>
                <p><strong>Nombre d'employés:</strong> {{ count($reports) }}</p>
                <p><strong>Présences totales:</strong> {{ array_sum(array_column($reports, 'totalPresences')) }}</p>
                <p><strong>Moyenne de présences par employé:</strong> 
                    {{ count($reports) > 0 ? round(array_sum(array_column($reports, 'totalPresences')) / count($reports), 1) : 0 }}
                </p>
                <p><strong>Légende évaluation:</strong> <span style="color:#15803d;">🟢 Vert ≥ 14/20</span> ·
                    <span style="color:#c2410c;">🟠 Orange 10-13/20</span> · <span style="color:#b91c1c;">🔴 Rouge &lt; 10/20</span></p>
            </div>
        @else
            <div class="no-data">
                <h3>Aucune donnée disponible</h3>
                <p>Aucune donnée de présence n'est disponible pour le mois en cours.</p>
            </div>
        @endif

        <div class="signature-section">
            <p>Signature du superviseur:</p>
            <div class="signature-line"></div>
            <div class="signature-name">{{ $superviseur }}</div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Le Pharaon. Ce document est généré automatiquement et est confidentiel.</p>
        </div>
    </div>
</body>
</html>