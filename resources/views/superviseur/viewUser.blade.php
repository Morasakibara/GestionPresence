@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Détails de l'utilisateur</span>
</div>
@endsection

@section('navigation')
<a href="{{ route('superviseur.supdashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('superviseur.showAddMember') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Ajout membre</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Suivre présences</a>
<a href="{{ route('superviseur.generateReport2') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapports</a>
<a href="{{ route('notifications.index') }}" class="relative rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">{{ Auth::user()->unreadNotifications->count() }}</span>
    @endif
</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('superviseur.supdashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('superviseur.showAddMember') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Ajout membre</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">Suivre présences</a>
<a href="{{ route('superviseur.generateReport2') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapports</a>
<a href="{{ route('notifications.index') }}" class="relative block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute top-2 right-2 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">{{ Auth::user()->unreadNotifications->count() }}</span>
    @endif
</a>
@endsection

@section('content')

<!-- Profil utilisateur -->
<div class="pharaoh-card overflow-hidden mb-6">
    <!-- En-tête -->
    <div class="bg-gradient-to-r from-pharaoh-black to-pharaoh-bronze-dark px-6 py-6 text-white">
        <div class="flex flex-col items-center space-y-4 sm:flex-row sm:space-x-6 sm:space-y-0">
            <div class="flex-shrink-0">
                @if($utilisateur->avatar)
                    <img src="{{ asset('storage/avatars/'.$utilisateur->avatar) }}" alt="{{ $utilisateur->nom }}" class="h-20 w-20 rounded-full border-2 border-pharaoh-gold object-cover shadow-lg sm:h-24 sm:w-24">
                @else
                    <div class="h-20 w-20 rounded-full bg-pharaoh-gold/20 border-2 border-pharaoh-gold flex items-center justify-center text-2xl font-bold text-pharaoh-gold sm:h-24 sm:w-24">
                        {{ strtoupper(substr($utilisateur->nom, 0, 2)) }}
                    </div>
                @endif
            </div>
            <div class="text-center sm:text-left">
                <h2 class="text-xl font-bold">{{ $utilisateur->nom }}</h2>
                <p class="text-pharaoh-gold-light text-sm">{{ $utilisateur->email }}</p>
                <div class="mt-2">
                    @if(strtolower($utilisateur->role) == 'superviseur')
                        <span class="inline-flex items-center rounded-full bg-pharaoh-gold/20 px-2.5 py-0.5 text-xs font-medium text-pharaoh-gold-light">Superviseur</span>
                    @elseif(strtolower($utilisateur->role) == 'employer')
                        <span class="inline-flex items-center rounded-full bg-green-500/20 px-2.5 py-0.5 text-xs font-medium text-green-300">Employé</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-medium text-white">{{ $utilisateur->role }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu -->
    <div class="px-6 py-6">
        <!-- Statistiques -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#080808] mb-4">Statistiques de présence</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="stat-card">
                    <div class="stat-card-icon bg-pharaoh-gold/10 text-pharaoh-gold">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="stat-card-value text-pharaoh-gold">{{ $totalPresences }}</div>
                    <div class="stat-card-label">Total présences</div>
                </div>
            </div>
        </div>

        <!-- Graphique -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#080808] mb-4">Présences du mois</h3>
            <div class="pharaoh-card p-4" style="height: 300px;">
                <canvas id="presenceChart"></canvas>
            </div>
        </div>

        <!-- Géolocalisation -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-[#080808] mb-4">Dernières localisations</h3>
            @php
                $lastPresence = App\Models\Presence::where('employerID', $utilisateur->id)
                    ->whereNotNull('latitude_arrivee')
                    ->orderBy('date', 'desc')
                    ->first();
            @endphp

            @if($lastPresence)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="pharaoh-card p-4">
                        <h4 class="font-semibold text-sm text-[#080808] mb-3 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            Dernière arrivée
                        </h4>
                        <div class="space-y-1 text-sm">
                            <p class="text-gray-600"><span class="font-medium text-gray-800">Date:</span> {{ $lastPresence->date }}</p>
                            <p class="text-gray-600"><span class="font-medium text-gray-800">Heure:</span> {{ $lastPresence->heureArrivee ? $lastPresence->heureArrivee->format('H:i:s') : 'N/A' }}</p>
                            <p class="text-gray-600">
                                <span class="font-medium text-gray-800">Statut:</span>
                                <span class="{{ $lastPresence->localisation_validee_arrivee ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $lastPresence->localisation_validee_arrivee ? '✓ Validée' : '✗ Non validée' }}
                                </span>
                            </p>
                            @if($lastPresence->latitude_arrivee && $lastPresence->longitude_arrivee)
                                <a href="https://www.google.com/maps?q={{ $lastPresence->latitude_arrivee }},{{ $lastPresence->longitude_arrivee }}" target="_blank" class="inline-flex items-center text-sm text-pharaoh-gold hover:text-pharaoh-bronze-dark mt-2">
                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Voir sur la carte
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="pharaoh-card p-4">
                        <h4 class="font-semibold text-sm text-[#080808] mb-3 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                            Dernier départ
                        </h4>
                        <div class="space-y-1 text-sm">
                            <p class="text-gray-600"><span class="font-medium text-gray-800">Date:</span> {{ $lastPresence->date }}</p>
                            <p class="text-gray-600"><span class="font-medium text-gray-800">Heure:</span> {{ $lastPresence->heureDepart ? $lastPresence->heureDepart->format('H:i:s') : 'N/A' }}</p>
                            <p class="text-gray-600">
                                <span class="font-medium text-gray-800">Statut:</span>
                                <span class="{{ $lastPresence->localisation_validee_depart ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $lastPresence->localisation_validee_depart ? '✓ Validée' : '✗ Non validée' }}
                                </span>
                            </p>
                            @if($lastPresence->latitude_depart && $lastPresence->longitude_depart)
                                <a href="https://www.google.com/maps?q={{ $lastPresence->latitude_depart }},{{ $lastPresence->longitude_depart }}" target="_blank" class="inline-flex items-center text-sm text-pharaoh-gold hover:text-pharaoh-bronze-dark mt-2">
                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Voir sur la carte
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if($lastPresence->workplaceLocation)
                    <div class="mt-4 pharaoh-card p-4">
                        <h4 class="font-semibold text-sm text-[#080808] mb-1 flex items-center gap-2">
                            <svg class="h-4 w-4 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                            Lieu de travail
                        </h4>
                        <p class="text-sm text-gray-600">{{ $lastPresence->workplaceLocation->nom }}</p>
                    </div>
                @endif
            @else
                <div class="pharaoh-card p-8 text-center">
                    <svg class="h-12 w-12 mx-auto text-gray-300 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                    <p class="text-sm text-gray-500">Aucune donnée de géolocalisation disponible.</p>
                </div>
            @endif
        </div>

        <!-- Bouton retour -->
        <div class="flex justify-end">
            <a href="{{ route('superviseur.showFollowPresence') }}" class="btn-press inline-flex items-center rounded-lg bg-pharaoh-gold/10 px-4 py-2 text-sm font-semibold text-pharaoh-bronze-dark transition-colors hover:bg-pharaoh-gold/20">
                <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
                Retour
            </a>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function() {
        var ctx = document.getElementById('presenceChart');
        if (!ctx) return;
        var presenceData = @json($presenceStats);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: presenceData.labels,
                datasets: [{
                    label: 'Présences',
                    data: presenceData.data,
                    borderColor: '#D39B23',
                    backgroundColor: 'rgba(211, 155, 35, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#D39B23',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#080808',
                        bodyColor: '#555',
                        borderColor: '#E7E7E7',
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: {
                            label: function(ctx) { return 'Présences: ' + ctx.raw; }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxRotation: 45 } },
                    y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 } }
                }
            }
        });
    }, 100);
});
</script>
@endsection
