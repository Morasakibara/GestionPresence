@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Tableau de bord du superviseur de l'équipe {{ $equipe ?? 'Non définie' }}</span>
    <!-- Indicateur de notifications -->
    <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center px-2 py-1 text-sm font-medium text-3hcig-blue hover:bg-gray-100 rounded-md">
        <svg class="h-6 w-6 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        Notifications
        @if(Auth::user()->unreadNotifications->count() > 0)
            <span class="absolute -top-1 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
                {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
            </span>
        @endif
    </a>
</div>
@endsection

@section('navigation')
<!-- Current: "bg-3hcig-blue text-white", Default: "text-gray-300 hover:bg-3hcig-blue hover:text-white" -->
<a href="{{ route('superviseur.supdashboard') }}" class="rounded-md bg-3hcig-blue px-3 py-2 text-sm font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('superviseur.showAddMember') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Ajout de membre</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Suivre les présences</a>
<a href="{{ route('superviseur.generateReport2') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Générer Rapports</a>
<a href="{{ route('notifications.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white relative">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('superviseur.supdashboard') }}" class="block rounded-md bg-3hcig-blue px-3 py-2 text-base font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('superviseur.showAddMember') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Ajout de membre</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Suivre les présences</a>
<a href="{{ route('superviseur.generateReport2') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Générer Rapports</a>
<a href="{{ route('notifications.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white relative">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute top-2 right-2 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
@endsection

@section('content')
<!-- Statut de l'équipe aujourd'hui -->
<div class="mb-6">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-[#080808]">Statut de l'équipe aujourd'hui</h2>
            <p class="text-sm text-gray-500">Présence et discipline des membres de votre équipe</p>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Nombre de membres -->
        <div class="stat-card">
            <div class="stat-card-icon bg-blue-50 text-blue-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $teamMemberCount ?? 0 }}</div>
            <div class="stat-card-label">Membres d'équipe</div>
        </div>

        <!-- Présents aujourd'hui -->
        <div class="stat-card">
            <div class="stat-card-icon bg-green-50 text-green-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $presentToday ?? 0 }}</div>
            <div class="stat-card-label">Présents aujourd'hui</div>
        </div>

        <!-- Absents aujourd'hui -->
        <div class="stat-card">
            <div class="stat-card-icon bg-red-50 text-red-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $absentToday ?? 0 }}</div>
            <div class="stat-card-label">Absents aujourd'hui</div>
        </div>

        <!-- Retards aujourd'hui -->
        <div class="stat-card">
            <div class="stat-card-icon bg-yellow-50 text-yellow-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $lateToday ?? 0 }}</div>
            <div class="stat-card-label">Retards aujourd'hui</div>
        </div>
    </div>
</div>

<!-- Accès rapides -->
<div class="mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-3">Accès rapides</h2>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('superviseur.showAddMember') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow">
            <div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Ajout de membre à l'équipe</h2>
            <p class="mt-1 text-sm text-gray-500">Ajouter un nouveau membre à votre équipe</p>
        </a>

        <a href="{{ route('superviseur.showFollowPresence') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow">
            <div class="rounded-full bg-3hcig-green/10 p-3 text-3hcig-green">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Suivre les présences</h2>
            <p class="mt-1 text-sm text-gray-500">Superviser les présences de votre équipe</p>
        </a>

        <a href="{{ route('superviseur.generateReport2') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow">
            <div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Générer Rapports</h2>
            <p class="mt-1 text-sm text-gray-500">Créer des rapports détaillés sur votre équipe</p>
        </a>

        <a href="{{ route('notifications.index') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow relative">
            <div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                @if(Auth::user()->unreadNotifications->count() > 0)
                    <span class="absolute top-2 right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
                        {{ Auth::user()->unreadNotifications->count() }}
                    </span>
                @endif
            </div>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Notifications</h2>
            <p class="mt-1 text-sm text-gray-500">Voir les alertes d'absence et retards</p>
        </a>
    </div>
</div>

<!-- Dernières notifications -->
@if(Auth::user()->notifications->count() > 0)
<div class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-gray-800">Dernières notifications</h2>
        <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-3hcig-blue hover:text-3hcig-blue-dark">
            Voir toutes
        </a>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200/70 shadow-card overflow-hidden">
        @foreach(Auth::user()->notifications->take(3) as $notification)
            <div class="border-l-4 {{ $notification->read_at ? 'border-gray-300' : 'border-3hcig-blue' }} p-4 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                <div class="flex justify-between">
                    <p class="text-sm text-gray-700 {{ $notification->read_at ? '' : 'font-medium' }}">
                        {{ isset($notification->data['message']) ? $notification->data['message'] : 'Notification' }}
                    </p>
                    <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Évolution des évaluations de l'équipe (6 mois) -->
<div class="mb-6">
    <div class="pharaoh-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-[#080808]">Évolution des évaluations de l'équipe</h2>
                <p class="text-sm text-gray-500">Note moyenne de l'équipe {{ $equipe ?? '' }} sur 6 mois</p>
            </div>
            <span class="badge badge-gold">Moyenne /20</span>
        </div>
        <div class="h-64">
            <canvas id="evaluationEvolChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('evaluationEvolChart');
        if (!ctx) {
            return;
        }
        const evolution = @json($evolutionEvaluations ?? ['labels' => [], 'notes' => [], 'couleurs' => []]);
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: evolution.labels,
                datasets: [{
                    label: 'Note moyenne /20',
                    data: evolution.notes,
                    borderColor: '#D39B23',
                    backgroundColor: 'rgba(211, 155, 35, 0.10)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: evolution.couleurs,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'Moyenne : ' + context.parsed.y + '/20';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 20,
                        ticks: { stepSize: 4 }
                    },
                    x: {
                        ticks: { maxRotation: 45, minRotation: 0 }
                    }
                }
            }
        });
    });
</script>
@endpush

