@extends('layouts.app')

@section('content')
<style>
    /* General styles */
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f4f4f4;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Card styles */
.card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.card-header {
    background-color: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    font-weight: bold;
    font-size: 1.2em;
}

.card-body {
    padding: 20px;
}

/* Avatar styles */
.avatar-container {
    text-align: center;
    margin-bottom: 20px;
}

.avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* User info styles */
.user-info p {
    margin-bottom: 10px;
}

.user-info strong {
    font-weight: bold;
    margin-right: 5px;
}

/* Chart container */
.chart-container {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
    padding: 20px 0;
}

/* Responsive styles */
@media (max-width: 768px) {
    .card-body {
        padding: 15px;
    }

    .avatar {
        width: 120px;
        height: 120px;
    }

    .chart-container {
        padding: 10px 0;
    }
}

@media (max-width: 480px) {
    .card-header {
        font-size: 1.1em;
    }

    .avatar {
        width: 100px;
        height: 100px;
    }

    .user-info p {
        font-size: 0.9em;
    }
}
</style>
<div class="container">
    <div class="card">
        <div class="card-header">
            Détails de l'utilisateur : {{ $utilisateur->nom }}
        </div>
        <div class="card-body">
            <div class="avatar-container">
                <img src="{{ asset('storage/avatars/' . $utilisateur->avatar) }}" class="avatar" alt="Avatar">
            </div>
            <div class="user-info">
                <p><strong>Nom :</strong> {{ $utilisateur->nom }}</p>
                <p><strong>Email :</strong> {{ $utilisateur->email }}</p>

                @if($utilisateur->role == 'employer' || $utilisateur->role == 'superviseur')
                    <p><strong>Rôle :</strong> {{ $utilisateur->role }}</p>
                    <p><strong>Total des présences :</strong> {{ $totalPresences }}</p>
                    <p><strong>Statistiques de présence (ce mois) :</strong></p>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="presenceChart"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    
    setTimeout(function() {
        var ctx = document.getElementById('presenceChart').getContext('2d');
    var presenceData = @json($presenceStats);

    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: presenceData.labels,
            datasets: [{
                label: 'Présences',
                data: presenceData.data,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    }, 100);
});
</script>
@endsection