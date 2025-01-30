@if (Auth::check())
    @extends('layouts.app')
@endif

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport d'équipe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Rapport d'équipe</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nom de l'employé</th>
                    <th>Total de Présences (Mois en cours)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                    <tr>
                        <td>{{ $report['name'] }}</td>
                        <td>{{ $report['totalPresences'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('export.pdf') }}" class="btn btn-primary">Exporter en PDF</a>
    </div>
</body>
</html>
@endsection