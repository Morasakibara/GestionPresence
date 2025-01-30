<!-- resources/views/user/presence-report.blade.php -->
@extends('layouts.app')

@section('content')
<style>
    /* styles/presence-report.css */

/* Police et styles de base */
body {
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f4f4f4;
}

/* Container principal */
.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

/* Titre */
h1 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 30px;
}

/* Canvas pour le graphique */
#presenceChart {
    width: 100%;
    max-height: 400px;
    margin-bottom: 30px;
}

/* Informations de présence */
.presence-info {
    display: flex;
    justify-content: space-around;
    margin-bottom: 20px;
}

.presence-info p {
    font-size: 18px;
    margin: 10px 0;
}

.presence-info strong {
    color: #3498db;
}

/* Styles pour les couleurs du graphique */
.chart-legend {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.legend-item {
    display: flex;
    align-items: center;
    margin: 0 10px;
}

.legend-color {
    width: 20px;
    height: 20px;
    margin-right: 5px;
    border-radius: 50%;
}

.legend-color.presence {
    background-color: #3498db;
}

.legend-color.absence {
    background-color: #e74c3c;
}

/* Media queries pour la responsivité */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }

    h1 {
        font-size: 24px;
    }

    .presence-info {
        flex-direction: column;
        align-items: center;
    }

    #presenceChart {
        max-height: 300px;
    }
}

@media (max-width: 480px) {
    h1 {
        font-size: 20px;
    }

    .presence-info p {
        font-size: 16px;
    }

    #presenceChart {
        max-height: 250px;
    }
}
</style>
<div class="container p-4 mx-auto">
    <h1 class="mb-4 text-xl font-bold">Bilan de présence du mois</h1>

    <canvas id="presenceChart" class="mb-4"></canvas>

    <div class="chart-legend">
        <div class="legend-item">
            <div class="legend-color presence"></div>
            <span>Présences</span>
        </div>
        <div class="legend-item">
            <div class="legend-color absence"></div>
            <span>Absences</span>
        </div>
    </div>

    <div class="presence-info">
        <p>Total de présences : <strong>{{ $totalPresences }}</strong></p>
        <p>Total d'absences : <strong>{{ $totalAbsences }}</strong></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('presenceChart').getContext('2d');
        const data = {
            labels: @json($presences->pluck('date')->toArray()), // Récupère les jours du mois
            datasets: [
                {
                    label: 'Présences',
                    data: @json($presences->where('status', 'présent')->pluck('date')->countBy()->toArray()), // Comptes les jours où l'utilisateur est présent
                    backgroundColor: 'blue',
                },
                {
                    label: 'Absences',
                    data: @json($presences->where('status', 'absent')->pluck('date')->countBy()->toArray()), // Comptes les jours où l'utilisateur est absent
                    backgroundColor: 'red',
                }
            ]
        };
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</div>
@endsection
