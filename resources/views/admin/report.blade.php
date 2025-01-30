@if (Auth::check())
    @extends('layouts.app')      
@endif

@section('content')
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Présence</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 10px; text-align: left; }
        input{margin: 50px}
        button{margin-top: 15px;border: 1px solid gray;background-color: gray;font-family: Dejavu sans, sans-serif;padding: 5px;border-radius: 2px}
        button:hover{background-color: white;}
       /* #block-item{position: absolute;display: grid;justify-content: center;width: 100%;align-content: center;border: 100px;}*/
    </style>
</head>
<body>
   <div class="bloc-item">
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
            @foreach($presences as $data)
            <tr>
                <td>{{ $data->employer_nom }}</td>
                <td>{{ $data->total_presence }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <form action="{{ route('admin.exportReport') }}" method="POST">
        @csrf
        <input type="hidden" name="start_date" value="{{ $startDate }}">
        <input type="hidden" name="end_date" value="{{ $endDate }}">
        <button type="submit">Exporter en PDF</button>
    </form>
   </div>
</body>
</html>
@endsection