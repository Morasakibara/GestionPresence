@extends('layouts.dashboard')

@section('header')
<div>
    <span>Détails de l'utilisateur</span>
    <p class="console-subtitle">Profil, statistiques de présence et dernières localisations.</p>
</div>
@endsection

@section('content')

<!-- Profil utilisateur -->
<div class="console-card mb-6 overflow-hidden">
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
                <h2 class="font-display text-[1.375rem] font-semibold leading-tight text-white">{{ $utilisateur->nom }}</h2>
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
            <h3 class="console-card-title mb-4">Statistiques de présence</h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
                @include('partials.dashboard.kpi', ['label' => 'Total présences', 'value' => $totalPresences, 'meta' => 'Jours pointés', 'tone' => 'gold'])
            </div>
        </div>

        <!-- Graphique -->
        <div class="mb-6">
            <div class="console-card">
                <div class="console-card-head">
                    <div>
                        <h3 class="console-card-title">Présences du mois</h3>
                        <p class="console-card-sub">Jours pointés sur la période</p>
                    </div>
                </div>
                <div class="console-card-body">
                    <div class="h-[300px]"><canvas id="presenceChart" role="img" aria-label="Présences du mois"></canvas></div>
                </div>
            </div>
        </div>

        <!-- Géolocalisation -->
        <div class="mb-6">
            <h3 class="console-card-title mb-4">Dernières localisations</h3>
            @php
                $lastPresence = App\Models\Presence::where('employerID', $utilisateur->id)
                    ->whereNotNull('latitude_arrivee')
                    ->orderBy('date', 'desc')
                    ->first();
            @endphp

            @if($lastPresence)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="console-card p-4">
                        <h4 class="mb-3 flex items-center gap-2 text-sm font-semibold text-[#080808]">
                            <span class="console-dot bg-green-500"></span>
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

                    <div class="console-card p-4">
                        <h4 class="mb-3 flex items-center gap-2 text-sm font-semibold text-[#080808]">
                            <span class="console-dot bg-red-500"></span>
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
                    <div class="console-card mt-4 p-4">
                        <h4 class="mb-1 flex items-center gap-2 text-sm font-semibold text-[#080808]">
                            <svg class="h-4 w-4 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                            Lieu de travail
                        </h4>
                        <p class="text-sm text-gray-600">{{ $lastPresence->workplaceLocation->nom }}</p>
                    </div>
                @endif
            @else
                <div class="console-empty">
                    <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                    <p class="text-sm text-gray-500">Aucune donnée de géolocalisation disponible.</p>
                </div>
            @endif
        </div>

        <!-- Bouton retour -->
        <div class="flex justify-end">
            <a href="{{ route('superviseur.showFollowPresence') }}" class="btn-soft">
                <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
                Retour
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/pharaoh-charts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const presenceData = @json($presenceStats);

    PharaohCharts.line(document.getElementById('presenceChart'), {
        labels: presenceData.labels,
        datasets: [{ label: 'Présences', data: presenceData.data }]
    }, {
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function (ctx) { return 'Présences : ' + ctx.parsed.y; }
                }
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0, stepSize: 1 } }
        }
    });
});
</script>
@endpush
