<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Présence</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 10px; text-align: left; }
    </style>
</head>
<body>
    <h1>Rapport de Présence</h1>
    <p>Période: {{ request('start_date') }} - {{ request('end_date') }}</p>
    <table>
        <thead>
            <tr>
                <th>Nom de l'employé</th>
                <th>Total Présence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $data)
            <tr>
                <td>{{ $data->employer_nom }}</td>
                <td>{{ $data->total_presence }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
