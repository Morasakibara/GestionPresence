<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique de présence</title>
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
        .section {
            background-color: #f3f4f6;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .section h2 {
            font-size: 16px;
            font-weight: 600;
            color: #115293;
            margin: 0 0 12px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .section p {
            margin: 6px 0;
            color: #4b5563;
            font-size: 14px;
        }
        .section strong {
            color: #1f2937;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 9999px;
            background-color: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
        }
        .table-container {
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
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
            <h1>Historique de présence</h1>
            <p>Employé: {{ $employe ? $employe->nom : 'Employé #' . $presence->employerID }} ({{ $employe->email ?? '' }})</p>
            <p>Date: {{ date('d/m/Y', strtotime($presence->date)) }}</p>
            <p>Généré le: {{ $generatedDate }}</p>
        </div>

        <div class="section">
            <h2>Récapitulatif du pointage</h2>
            <p><strong>Arrivée:</strong> {{ $presence->heureArrivee ? date('d/m/Y H:i', strtotime($presence->heureArrivee)) : '—' }}
                @if($presence->localisation_validee_arrivee) (localisation validée ✅) @else (non validée) @endif</p>
            <p><strong>Départ:</strong> {{ $presence->heureDepart ? date('d/m/Y H:i', strtotime($presence->heureDepart)) : '—' }}
                @if($presence->localisation_validee_depart) (localisation validée ✅) @else (non validée) @endif</p>
            <p><strong>Statut:</strong> {{ ucfirst($presence->status ?? '—') }}</p>
            @if($presence->suspect)
                <p><strong>Suspicion:</strong> <span class="badge">Suspecte</span></p>
                <p><strong>Motif:</strong> {{ $presence->motif_suspicion ?? 'Non renseigné' }}</p>
                @if($presence->distance_km !== null || $presence->vitesse_kmh !== null)
                    <p><strong>Distance:</strong> {{ $presence->distance_km !== null ? number_format($presence->distance_km, 2, ',', ' ') . ' km' : '—' }}
                    <strong>Vitesse:</strong> {{ $presence->vitesse_kmh !== null ? number_format($presence->vitesse_kmh, 1, ',', ' ') . ' km/h' : '—' }}</p>
                @endif
            @else
                <p><strong>Suspicion:</strong> Aucune (présence normale)</p>
            @endif
            @if($presence->commentaire_contestation)
                <p><strong>Contestation:</strong> « {{ $presence->commentaire_contestation }} » ({{ $presence->conteste_le ? date('d/m/Y H:i', strtotime($presence->conteste_le)) : '' }})</p>
            @endif
            @if($presence->reponse_contestation)
                <p><strong>Réponse admin:</strong> {{ $presence->reponse_contestation === 'accordé' ? '✅ Acceptée' : '❌ Refusée' }}
                    @if($presence->commentaire_reponse_contestation) — « {{ $presence->commentaire_reponse_contestation }} » @endif
                    ({{ $presence->reponse_contestation_le ? date('d/m/Y H:i', strtotime($presence->reponse_contestation_le)) : '' }})</p>
            @endif
        </div>

        @if(count($traitements) > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Changement</th>
                            <th>Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($traitements as $t)
                        <tr>
                            <td>{{ $t->created_at ? date('d/m/Y H:i', strtotime($t->created_at)) : '' }}</td>
                            <td>{{ ucfirst($t->statut_avant) }} → {{ ucfirst($t->statut_apres) }}</td>
                            <td>{{ $t->commentaire ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="section">
                <h2>Historique des statuts</h2>
                <p>Aucun changement de statut enregistré.</p>
            </div>
        @endif

        <div class="footer">
            <p>© {{ date('Y') }} 3HCIG COOP-CA. Ce document est généré automatiquement et est confidentiel.</p>
        </div>
    </div>
</body>
</html>
