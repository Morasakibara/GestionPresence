@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Bilan de présence</h1>
    <div class="row">
        <div class="col-md-6">
            <h2>Statistiques du mois en cours</h2>
            <p>Total des présences : {{ $totalPresences }}</p>
            <p>Total des absences : {{ $totalAbsences }}</p>
        </div>
        <div class="col-md-6">
            <h2>Graphique de présence</h2>
            <canvas id="presenceChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('presenceChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: @json($presenceData),
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Répartition des présences et absences'
                }
            }
        }
    });
</script>
@endsection