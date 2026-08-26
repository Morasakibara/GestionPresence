@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Tableau de bord du superviseur de l'équipe {{ $equipe ?? 'Non définie' }}</span>
    <!-- Indicateur de notifications -->
    <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center px-2 py-1 text-sm font-medium text-pharaoh-gold hover:bg-gray-100 rounded-md">
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
<a href="{{ route('superviseur.supdashboard') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('superviseur.showAddMember') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Ajout de membre</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Suivre les présences</a>
<a href="{{ route('superviseur.generateReport2') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Générer Rapports</a>
<a href="{{ route('notifications.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white relative">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('superviseur.supdashboard') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('superviseur.showAddMember') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Ajout de membre</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Suivre les présences</a>
<a href="{{ route('superviseur.generateReport2') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Générer Rapports</a>
<a href="{{ route('notifications.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white relative">
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
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
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

        <!-- Présents aujourd'hui (clic modal) -->
        <div class="stat-card cursor-pointer hover:ring-2 hover:ring-green-400 transition-all" onclick="toggleStatModal('modal-presents')">
            <div class="stat-card-icon bg-green-50 text-green-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-card-value" data-count="present">{{ $presentToday ?? 0 }}</div>
            <div class="stat-card-label">Présents aujourd'hui</div>
            <div class="text-xs text-green-600 mt-1 font-medium">Cliquez pour voir les détails ↓</div>
        </div>

        <!-- Absents aujourd'hui (clic modal) -->
        <div class="stat-card cursor-pointer hover:ring-2 hover:ring-red-400 transition-all" onclick="toggleStatModal('modal-absents')">
            <div class="stat-card-icon bg-red-50 text-red-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-card-value" data-count="absent">{{ $absentToday ?? 0 }}</div>
            <div class="stat-card-label">Absents aujourd'hui</div>
            <div class="text-xs text-red-500 mt-1 font-medium">Cliquez pour voir les détails ↓</div>
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

        <!-- Arrivés aujourd'hui (clic modal) -->
        <div class="stat-card cursor-pointer hover:ring-2 hover:ring-emerald-400 transition-all" onclick="toggleStatModal('modal-arrives')">
            <div class="stat-card-icon bg-emerald-50 text-emerald-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7l4 4z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $arrivesAujourdhui->count() }}</div>
            <div class="stat-card-label">Arrivés aujourd'hui</div>
            <div class="text-xs text-emerald-600 mt-1 font-medium">Cliquez pour voir les détails ↓</div>
        </div>

        <!-- Partis aujourd'hui (clic modal) -->
        <div class="stat-card cursor-pointer hover:ring-2 hover:ring-purple-400 transition-all" onclick="toggleStatModal('modal-partis')">
            <div class="stat-card-icon bg-purple-50 text-purple-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4l4 4z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $partisAujourdhui->count() }}</div>
            <div class="stat-card-label">Partis aujourd'hui</div>
            <div class="text-xs text-purple-600 mt-1 font-medium">Cliquez pour voir les détails ↓</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════ -->
<!-- MODALS CLIQUABLES (overlay + close on click outside) -->
<!-- ═══════════════════════════════════════════════════ -->

<!-- Modal Présents -->
<div id="modal-presents" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'modal-presents')">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-sm mx-4 p-5 transform transition-all" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">🟢 Personnes présentes</h3>
            <button onclick="toggleStatModal('modal-presents')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-64 overflow-y-auto space-y-1">
            @forelse($presentsAujourdhui as $p)
                <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-green-50 {{ !$loop->last ? 'border-b border-green-100' : '' }}">
                    <span class="text-sm font-semibold text-gray-800">{{ $p->nom }}</span>
                    <span class="text-xs text-green-700 font-bold">
                        {{ $p->heureArrivee ? \Carbon\Carbon::parse($p->heureArrivee)->format('H:i') : '—' }}
                        @if($p->heureDepart) → {{ \Carbon\Carbon::parse($p->heureDepart)->format('H:i') }}@endif
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-4 text-center">Aucun employé présent</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Absents -->
<div id="modal-absents" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'modal-absents')">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-sm mx-4 p-5 transform transition-all" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">🔴 Personnes absentes</h3>
            <button onclick="toggleStatModal('modal-absents')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-64 overflow-y-auto space-y-1">
            @forelse($absentsAujourdhui as $a)
                <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-red-50 {{ !$loop->last ? 'border-b border-red-100' : '' }}">
                    <span class="text-sm font-semibold text-gray-800">{{ $a->nom }}</span>
                    <span class="text-xs text-red-600 font-bold">Absent</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-4 text-center">Tout le monde est présent 🎉</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Arrivés -->
<div id="modal-arrives" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'modal-arrives')">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-sm mx-4 p-5 transform transition-all" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">📥 Heures d'arrivée</h3>
            <button onclick="toggleStatModal('modal-arrives')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-64 overflow-y-auto space-y-1">
            @forelse($arrivesAujourdhui as $arr)
                <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-emerald-50 {{ !$loop->last ? 'border-b border-emerald-100' : '' }}">
                    <span class="text-sm font-semibold text-gray-800">{{ $arr->nom }}</span>
                    <span class="text-xs text-emerald-700 font-bold">
                        {{ $arr->heureArrivee ? \Carbon\Carbon::parse($arr->heureArrivee)->format('H:i') : '—' }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-4 text-center">Aucune arrivée enregistrée</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal Partis -->
<div id="modal-partis" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'modal-partis')">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-sm mx-4 p-5 transform transition-all" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">📤 Heures de départ</h3>
            <button onclick="toggleStatModal('modal-partis')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-64 overflow-y-auto space-y-1">
            @forelse($partisAujourdhui as $par)
                <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-purple-50 {{ !$loop->last ? 'border-b border-purple-100' : '' }}">
                    <span class="text-sm font-semibold text-gray-800">{{ $par->nom }}</span>
                    <span class="text-xs text-purple-700 font-bold">
                        {{ $par->heureDepart ? \Carbon\Carbon::parse($par->heureDepart)->format('H:i') : '—' }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-4 text-center">Aucun départ enregistré</p>
            @endforelse
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
            <div class="rounded-full bg-pharaoh-green/10 p-3 text-pharaoh-green">
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
        <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-pharaoh-gold hover:text-pharaoh-bronze-dark">
            Voir toutes
        </a>
    </div>
    <div class="bg-white rounded-2xl border border-gray-200/70 shadow-card overflow-hidden">
        @foreach(Auth::user()->notifications->take(3) as $notification)
            <div class="border-l-4 {{ $notification->read_at ? 'border-gray-300' : 'border-pharaoh-gold' }} p-4 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
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

        <!-- Exports PNG/CSV -->
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <button type="button" onclick="exporterGraphiquePNG()" class="btn-secondary">
                <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Exporter PNG
            </button>
            <a href="{{ route('superviseur.evaluations.evolution.export') }}" class="btn-gold">
                <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exporter CSV
            </a>
        </div>

        <div class="h-64">
            <canvas id="evaluationEvolChart"></canvas>
        </div>

        @php
            $moyenneEvol = $statsEvolution['moyenne'] ?? 0;
            $tendanceEvol = $statsEvolution['tendance'] ?? 'stable';
            $deltaEvol = $statsEvolution['delta'] ?? 0;
        @endphp
        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-200/70 bg-gray-50 p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Moyenne globale (6 mois)</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-[#080808]">{{ number_format($moyenneEvol, 1, ',', '') }}</span>
                    <span class="text-sm text-gray-500">/20</span>
                </div>
            </div>
            <div class="rounded-xl border border-gray-200/70 bg-gray-50 p-4">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Tendance (dernier mois)</div>
                <div class="mt-1 flex items-center gap-2">
                    @if($tendanceEvol === 'hausse')
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-sm font-bold text-green-700">▲ Hausse {{ $deltaEvol > 0 ? '+' . number_format($deltaEvol, 1, ',', '') : '' }} pt</span>
                    @elseif($tendanceEvol === 'baisse')
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-sm font-bold text-red-700">▼ Baisse {{ number_format(abs($deltaEvol), 1, ',', '') }} pt</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-sm font-bold text-gray-600">— Stable</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tableau comparatif des notes par membre -->
        @if(!empty($notesParEmploye))
        <div class="mt-6">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold text-[#080808]">Notes par membre (6 mois)</h3>
                <a href="{{ route('superviseur.comparatif.export') }}" class="btn-gold text-xs">
                    <svg class="-ml-0.5 mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Exporter PDF
                </a>
            </div>
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <span class="text-xs font-medium text-gray-500">Filtrer :</span>
                <button onclick="filtrerTableau('all')" class="badge badge-gold filter-btn active" data-filter="all">Tous</button>
                <button onclick="filtrerTableau('vert')" class="badge badge-green filter-btn" data-filter="vert">🟢 Vert</button>
                <button onclick="filtrerTableau('orange')" class="badge badge-orange filter-btn" data-filter="orange">🟠 Orange</button>
                <button onclick="filtrerTableau('rouge')" class="badge badge-red filter-btn" data-filter="rouge">🔴 Rouge</button>
                <span class="ml-2 text-xs font-medium text-gray-500">Trier :</span>
                <button onclick="trierTableau('nom')" class="badge filter-btn" data-sort="nom">Nom</button>
                <button onclick="trierTableau('moyenne-desc')" class="badge filter-btn" data-sort="moyenne-desc">Moyenne ↓</button>
                <button onclick="trierTableau('moyenne-asc')" class="badge filter-btn" data-sort="moyenne-asc">Moyenne ↑</button>
            </div>
            <div class="table-wrap">
                <table id="tableauComparatif" class="min-w-full divide-y divide-gray-200">
                    <thead class="table-head">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Employé</th>
                            @foreach(($notesParEmploye[0]['moisListe'] ?? []) as $m)
                                <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">{{ $m['label'] }}</th>
                            @endforeach
                            <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Moyenne</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        @foreach($notesParEmploye as $emp)
                        <tr data-couleur="{{ $emp['couleur'] }}" data-moyenne="{{ $emp['moyenne'] }}" data-nom="{{ $emp['nom'] }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $emp['nom'] }}</td>
                            @foreach($emp['moisListe'] as $m)
                                @php $cell = $emp['notes'][$m['mois']] ?? null; @endphp
                                <td class="px-4 py-3 text-center">
                                    @if($cell && $cell['note'] > 0)
                                        <span class="badge {{ $cell['couleur'] === 'vert' ? 'badge-green' : ($cell['couleur'] === 'rouge' ? 'badge-red' : 'badge-orange') }}">{{ $cell['note'] }}/20</span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-center">
                                <span class="badge {{ $emp['couleur'] === 'vert' ? 'badge-green' : ($emp['couleur'] === 'rouge' ? 'badge-red' : 'badge-orange') }}">{{ $emp['moyenne'] }}/20</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ═══════════════════════════════════════════════
    // MODALS CLIQUABLES
    // ═══════════════════════════════════════════════
    function toggleStatModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        const isOpen = !modal.classList.contains('hidden');
        if (isOpen) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        } else {
            // Fermer les autres modals ouverts
            document.querySelectorAll('[id^="modal-presents"], [id^="modal-absents"], [id^="modal-arrives"], [id^="modal-partis"]').forEach(m => {
                m.classList.add('hidden');
                m.classList.remove('flex');
            });
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeStatModal(event, id) {
        if (event.target === event.currentTarget) {
            toggleStatModal(id);
        }
    }

    // Fermer avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id^="modal-presents"], [id^="modal-absents"], [id^="modal-arrives"], [id^="modal-partis"]').forEach(m => {
                if (!m.classList.contains('hidden')) {
                    m.classList.add('hidden');
                    m.classList.remove('flex');
                    document.body.style.overflow = '';
                }
            });
        }
    });

    // ═══════════════════════════════════════════════
    // GRAPHIQUE ÉVOLUTION
    // ═══════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('evaluationEvolChart');
        if (!ctx) return;

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
                    y: { min: 0, max: 20, ticks: { stepSize: 4 } },
                    x: { ticks: { maxRotation: 45, minRotation: 0 } }
                }
            }
        });

        // --- Filtrage et tri du tableau comparatif ---
        window.filtrerTableau = function (couleur) {
            const rows = document.querySelectorAll('#tableauComparatif tbody tr[data-couleur]');
            rows.forEach(row => {
                row.style.display = (couleur === 'all' || row.dataset.couleur === couleur) ? '' : 'none';
            });
            document.querySelectorAll('.filter-btn[data-filter]').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.filter === couleur);
            });
        };

        window.trierTableau = function (cle) {
            const tbody = document.querySelector('#tableauComparatif tbody');
            const rows = Array.from(tbody.querySelectorAll('tr[data-couleur]'));
            rows.sort((a, b) => {
                if (cle === 'nom') return a.dataset.nom.localeCompare(b.dataset.nom);
                if (cle === 'moyenne-desc') return parseFloat(b.dataset.moyenne) - parseFloat(a.dataset.moyenne);
                if (cle === 'moyenne-asc') return parseFloat(a.dataset.moyenne) - parseFloat(b.dataset.moyenne);
                return 0;
            });
            rows.forEach(row => tbody.appendChild(row));
            document.querySelectorAll('.filter-btn[data-sort]').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.sort === cle);
            });
        };

        // Export PNG du graphique
        window.exporterGraphiquePNG = function () {
            const canvas = document.getElementById('evaluationEvolChart');
            if (!canvas) return;
            const url = canvas.toDataURL('image/png');
            const a = document.createElement('a');
            a.href = url;
            a.download = 'evolution_evaluations_equipe_' + new Date().toISOString().slice(0, 10) + '.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };
    });
</script>
@endpush
