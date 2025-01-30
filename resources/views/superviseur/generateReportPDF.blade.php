<h1>Rapport d'équipe - {{ auth()->user()->equipe }}</h1>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Total de Présences (mois en cours)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reports  as $report)
            <tr>
                <td>{{ $employe['nom'] }}</td>
                <td>{{ $employe['totalPresences'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
