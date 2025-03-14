@extends('layouts.app')

@section('title', 'Détails de l\'utilisateur')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Détails de l'utilisateur</h1>
        <p class="mt-2 text-sm text-gray-600">Informations et statistiques de présence</p>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
        <!-- En-tête avec avatar et infos principales -->
        <div class="bg-3hcig-blue-dark px-6 py-4 text-white">
            <div class="flex flex-col items-center space-y-4 sm:flex-row sm:space-x-6 sm:space-y-0">
                <div class="flex-shrink-0">
                    <img src="{{ asset('storage/avatars/'.($utilisateur->avatar ?: 'default.png')) }}"
                         alt="{{ $utilisateur->nom }}"
                         class="h-24 w-24 rounded-full border-2 border-white object-cover shadow-sm sm:h-32 sm:w-32">
                </div>
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-bold">{{ $utilisateur->nom }}</h2>
                    <p class="text-3hcig-blue-light">{{ $utilisateur->email }}</p>
                    <div class="mt-2">
                        @if(strtolower($utilisateur->role) == 'superviseur')
                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                Superviseur
                            </span>
                        @elseif(strtolower($utilisateur->role) == 'employer')
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                Employé
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                {{ $utilisateur->role }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu détaillé -->
        <div class="px-6 py-5">
            <!-- Statistiques de présence -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900">Statistiques de présence</h3>
                <div class="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow-sm ring-1 ring-gray-200">
                        <dt class="truncate text-sm font-medium text-gray-500">Total des présences</dt>
                        <dd class="mt-1 text-3xl font-semibold text-3hcig-blue">{{ $totalPresences }}</dd>
                    </div>
                    <!-- Ajoutez d'autres statistiques ici si nécessaire -->
                </div>
            </div>

            <!-- Graphique des présences -->
            <div class="mt-8">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Présences du mois</h3>
                <div class="relative h-80 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
                    <canvas id="presenceChart"></canvas>
                </div>
                <p class="mt-2 text-sm text-gray-500">Graphique des présences quotidiennes pour le mois en cours.</p>
            </div>

            <!-- Géolocalisation -->
            <div class="mt-8">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Dernières localisations enregistrées</h3>
                <div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
                    @php
                        $lastPresence = App\Models\Presence::where('employerID', $utilisateur->id)
                            ->whereNotNull('latitude_arrivee')
                            ->orderBy('date', 'desc')
                            ->first();
                    @endphp

                    @if($lastPresence)
                        <div class="px-4 py-5 sm:p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="font-medium text-gray-700 mb-2">Dernière arrivée</h4>
                                    <p class="text-sm text-gray-600 mb-1">
                                        <span class="font-medium">Date:</span> {{ $lastPresence->date }}
                                    </p>
                                    <p class="text-sm text-gray-600 mb-1">
                                        <span class="font-medium">Heure:</span> {{ $lastPresence->heureArrivee ? $lastPresence->heureArrivee->format('H:i:s') : 'N/A' }}
                                    </p>
                                    <p class="text-sm text-gray-600 mb-1">
                                        <span class="font-medium">Statut:</span>
                                        <span class="{{ $lastPresence->localisation_validee_arrivee ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $lastPresence->localisation_validee_arrivee ? 'Position validée' : 'Position non validée' }}
                                        </span>
                                    </p>
                                    @if($lastPresence->latitude_arrivee && $lastPresence->longitude_arrivee)
                                        <a href="https://www.google.com/maps?q={{ $lastPresence->latitude_arrivee }},{{ $lastPresence->longitude_arrivee }}"
                                        target="_blank"
                                        class="inline-flex items-center text-sm text-3hcig-blue hover:underline mt-2">
                                            <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Voir sur la carte
                                        </a>
                                    @endif
                                </div>

                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="font-medium text-gray-700 mb-2">Dernier départ</h4>
                                    <p class="text-sm text-gray-600 mb-1">
                                        <span class="font-medium">Date:</span> {{ $lastPresence->date }}
                                    </p>
                                    <p class="text-sm text-gray-600 mb-1">
                                        <span class="font-medium">Heure:</span> {{ $lastPresence->heureDepart ? $lastPresence->heureDepart->format('H:i:s') : 'N/A' }}
                                    </p>
                                    <p class="text-sm text-gray-600 mb-1">
                                        <span class="font-medium">Statut:</span>
                                        <span class="{{ $lastPresence->localisation_validee_depart ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $lastPresence->localisation_validee_depart ? 'Position validée' : 'Position non validée' }}
                                        </span>
                                    </p>
                                    @if($lastPresence->latitude_depart && $lastPresence->longitude_depart)
                                        <a href="https://www.google.com/maps?q={{ $lastPresence->latitude_depart }},{{ $lastPresence->longitude_depart }}"
                                        target="_blank"
                                        class="inline-flex items-center text-sm text-3hcig-blue hover:underline mt-2">
                                            <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Voir sur la carte
                                        </a>
                                    @endif
                                </div>
                            </div>

                            @if($lastPresence->workplaceLocation)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <h4 class="font-medium text-gray-700 mb-2">Lieu de travail</h4>
                                    <p class="text-sm text-gray-600">{{ $lastPresence->workplaceLocation->nom }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="px-4 py-5 sm:p-6">
                            <p class="text-sm text-gray-500">Aucune donnée de géolocalisation disponible pour cet employé.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex justify-end">
                <a href="{{ route('superviseur.showFollowPresence') }}" class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
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
                    borderColor: '#1976D2', // 3hcig-blue
                    backgroundColor: 'rgba(25, 118, 210, 0.1)', // 3hcig-blue with opacity
                    borderWidth: 2,
                    pointBackgroundColor: '#1976D2',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.2, // légère courbe pour un rendu plus doux
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 6
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1f2937', // gray-800
                        bodyColor: '#4b5563', // gray-600
                        borderColor: '#e5e7eb', // gray-200
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: {
                            title: function(tooltipItems) {
                                return 'Jour ' + tooltipItems[0].label;
                            },
                            label: function(context) {
                                return 'Présence: ' + context.raw + (context.raw <= 1 ? ' fois' : ' fois');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Jour du mois'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 4],
                            color: '#e5e7eb' // gray-200
                        },
                        ticks: {
                            precision: 0,
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Nombre de présences'
                        }
                    }
                }
            }
        });
    }, 100);
});
</script>
@endsection
