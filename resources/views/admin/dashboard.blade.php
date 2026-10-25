@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Tableau de bord de l'administrateur</span>
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

@section('content')
@if(Auth::user()->isSuperAdmin())
<!-- Développement — réservé au super administrateur -->
<div class="mb-6 flex items-center justify-between gap-4 rounded-2xl border border-pharaoh-gold/40 bg-pharaoh-gold/5 px-5 py-4">
    <div>
        <p class="text-sm font-semibold text-[#080808]">Outils de développement</p>
        <p class="text-xs text-gray-500">Métriques système (erreurs, requêtes, uptime) — visible uniquement par le super administrateur.</p>
    </div>
    <a href="{{ route('admin.metrics') }}" class="btn-gold btn-press whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold text-white">
        Métriques système
    </a>
</div>
@endif
<!-- Vue d'ensemble -->
<div class="mb-6">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-[#080808]">Vue d'ensemble du système</h2>
            <p class="text-sm text-gray-500">Indicateurs clés de l'entreprise aujourd'hui</p>
        </div>
    </div>    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <!-- Total des employés -->
        <div class="stat-card">
            <div class="stat-card-icon bg-blue-50 text-blue-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $totalEmployees ?? 0 }}</div>
            <div class="stat-card-label">Total des employés</div>
        </div>

        <!-- Total des superviseurs -->
        <div class="stat-card">
            <div class="stat-card-icon bg-green-50 text-green-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $totalSupervisors ?? 0 }}</div>
            <div class="stat-card-label">Total des superviseurs</div>
        </div>

        <!-- Présents aujourd'hui (clic modal) -->
        <div class="stat-card cursor-pointer hover:ring-2 hover:ring-blue-400 transition-all" onclick="toggleStatModal('admin-modal-presents')">
            <div class="stat-card-icon bg-blue-50 text-blue-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-card-value" data-count="present">{{ $presentToday ?? 0 }}</div>
            <div class="stat-card-label">Présents aujourd'hui</div>
            <div class="text-xs text-blue-600 mt-1 font-medium">Cliquez pour voir les détails ↓</div>
        </div>

        <!-- Absents aujourd'hui (clic modal) -->
        <div class="stat-card cursor-pointer hover:ring-2 hover:ring-red-400 transition-all" onclick="toggleStatModal('admin-modal-absents')">
            <div class="stat-card-icon bg-red-50 text-red-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-card-value" data-count="absent">{{ $absentToday ?? 0 }}</div>
            <div class="stat-card-label">Absents aujourd'hui</div>
            <div class="text-xs text-red-500 mt-1 font-medium">Cliquez pour voir les détails ↓</div>
        </div>

        <!-- Arrivés aujourd'hui (clic modal) -->
        <div class="stat-card cursor-pointer hover:ring-2 hover:ring-emerald-400 transition-all" onclick="toggleStatModal('admin-modal-arrives')">
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
        <div class="stat-card cursor-pointer hover:ring-2 hover:ring-purple-400 transition-all" onclick="toggleStatModal('admin-modal-partis')">
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

<!-- ═══════════ MODALS CLIQUABLES ADMIN ═══════════ -->
<div id="admin-modal-presents" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'admin-modal-presents')">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-sm mx-4 p-5" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">🟢 Personnes présentes</h3>
            <button onclick="toggleStatModal('admin-modal-presents')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-64 overflow-y-auto space-y-1">
            @forelse($presentsAujourdhui as $p)
                <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-blue-50 {{ !$loop->last ? 'border-b border-blue-100' : '' }}">
                    <span class="text-sm font-semibold text-gray-800">{{ $p->nom }}</span>
                    <span class="text-xs text-blue-700 font-bold">
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

<div id="admin-modal-absents" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'admin-modal-absents')">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-sm mx-4 p-5" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">🔴 Personnes absentes</h3>
            <button onclick="toggleStatModal('admin-modal-absents')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
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

<div id="admin-modal-arrives" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'admin-modal-arrives')">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-sm mx-4 p-5" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">📥 Heures d'arrivée</h3>
            <button onclick="toggleStatModal('admin-modal-arrives')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-64 overflow-y-auto space-y-1">
            @forelse($arrivesAujourdhui as $arr)
                <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-emerald-50 {{ !$loop->last ? 'border-b border-emerald-100' : '' }}">
                    <span class="text-sm font-semibold text-gray-800">{{ $arr->nom }}</span>
                    <span class="text-xs text-emerald-700 font-bold">{{ \Carbon\Carbon::parse($arr->heureArrivee)->format('H:i') }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-4 text-center">Aucune arrivée enregistrée</p>
            @endforelse
        </div>
    </div>
</div>

<div id="admin-modal-partis" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'admin-modal-partis')">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-sm mx-4 p-5" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-900">📤 Heures de départ</h3>
            <button onclick="toggleStatModal('admin-modal-partis')" class="p-1 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-64 overflow-y-auto space-y-1">
            @forelse($partisAujourdhui as $par)
                <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-purple-50 {{ !$loop->last ? 'border-b border-purple-100' : '' }}">
                    <span class="text-sm font-semibold text-gray-800">{{ $par->nom }}</span>
                    <span class="text-xs text-purple-700 font-bold">{{ \Carbon\Carbon::parse($par->heureDepart)->format('H:i') }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 py-4 text-center">Aucun départ enregistré</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Statistiques mensuelles -->
<div class="mb-6">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-[#080808]">Statistiques mensuelles</h2>
            <p class="text-sm text-gray-500">Présences, absences et retards du mois en cours</p>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Total des présences ce mois -->
        <div class="stat-card">
            <div class="stat-card-icon bg-blue-50 text-blue-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $monthlyPresences ?? 0 }}</div>
            <div class="stat-card-label">Présences ce mois</div>
        </div>

        <!-- Total des absences ce mois -->
        <div class="stat-card">
            <div class="stat-card-icon bg-red-50 text-red-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $monthlyAbsences ?? 0 }}</div>
            <div class="stat-card-label">Absences ce mois</div>
        </div>

        <!-- Total des retards ce mois -->
        <div class="stat-card">
            <div class="stat-card-icon bg-yellow-50 text-yellow-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="stat-card-value">{{ $monthlyLates ?? 0 }}</div>
            <div class="stat-card-label">Retards ce mois</div>
        </div>
    </div>
</div>

<!-- Accès rapides -->
<div class="mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-3">Accès rapides</h2>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.addEmployee') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow">
            <div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Ajouter employé</h2>
            <p class="mt-1 text-sm text-gray-500">Ajouter un nouvel employé au système</p>
        </a>

        <a href="{{ route('admin.showEmployeeList') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow">
            <div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Liste des employés</h2>
            <p class="mt-1 text-sm text-gray-500">Voir et gérer tous les employés</p>
        </a>

        <a href="{{ route('admin.generateReport') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow">
            <div class="rounded-full bg-pharaoh-green/10 p-3 text-pharaoh-green">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Générer Bilan</h2>
            <p class="mt-1 text-sm text-gray-500">Créer des rapports détaillés</p>
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

<!-- ═══════════ ÉTAT DES CAISSES ═══════════ -->
@if(!empty($caisses) && count($caisses) > 0)
<div class="mb-6">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-[#080808]">État des caisses — {{ now()->format('d/m/Y') }}</h2>
            <p class="text-sm text-gray-500">Chiffre d'affaire journalier par poste</p>
        </div>
        <a href="{{ route('admin.caisse') }}" class="text-sm font-medium text-pharaoh-gold hover:text-pharaoh-bronze">Voir tout →</a>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-4">
        @foreach($caisses as $label => $c)
            <div class="pharaoh-card p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="rounded-full bg-pharaoh-gold/10 p-2 text-pharaoh-gold">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <span class="text-sm font-semibold text-[#080808]">{{ $c['nom'] }}</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-lg bg-green-50 p-3 text-center"><div class="text-lg font-bold text-green-700">{{ number_format($c['entrees'], 0, ',', '.') }}</div><div class="text-[11px] text-green-600">Entrées</div></div>
                    <div class="rounded-lg bg-red-50 p-3 text-center"><div class="text-lg font-bold text-red-600">{{ number_format($c['sorties'], 0, ',', '.') }}</div><div class="text-[11px] text-red-500">Sorties</div></div>
                    <div class="rounded-lg bg-[#FBF3E6] p-3 text-center"><div class="text-lg font-bold text-pharaoh-gold">{{ number_format($c['net'], 0, ',', '.') }}</div><div class="text-[11px] text-pharaoh-bronze">Net (FCFA)</div></div>
                </div>
            </div>
        @endforeach
    </div>
    <!-- Graphique CA consolidé 7 jours -->
    <div class="pharaoh-card p-6">
        <h3 class="text-base font-semibold text-[#080808] mb-3">Chiffre d'affaire — 7 derniers jours</h3>
        <div class="h-48"><canvas id="caisseConsolideChart"></canvas></div>
    </div>
</div>
@endif

<!-- ═══════════ ÉTAT DU STOCK ═══════════ -->
@if($stockTshirts->isNotEmpty() || $stockPapiers->isNotEmpty())
<div class="mb-6">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-[#080808]">État du stock</h2>
            <p class="text-sm text-gray-500">{{ $totalStockTshirts }} T-shirts · {{ $stockPapiers->count() }} imprimante(s) @if($alertesStock > 0)<span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700">{{ $alertesStock }} alerte(s)</span>@endif</p>
        </div>
        <a href="{{ route('admin.stock') }}" class="text-sm font-medium text-pharaoh-gold hover:text-pharaoh-bronze">Voir tout →</a>
    </div>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @if($stockTshirts->isNotEmpty())
            <div class="pharaoh-card overflow-hidden">
                <div class="px-5 py-3 bg-[#080808]"><span class="text-xs font-semibold uppercase tracking-wider text-[#FACE4A]">T-Shirts</span></div>
                <div class="table-scroll"><table class="min-w-full"><thead class="table-head"><tr><th>Couleur</th><th>Taille</th><th class="text-right">Qté</th><th>Statut</th></tr></thead>
                    <tbody class="table-body">@foreach($stockTshirts->take(5) as $t)<tr><td class="px-5 py-2.5 text-sm">{{ $t->couleur }}</td><td class="px-5 py-2.5 text-sm">{{ $t->taille }}</td><td class="px-5 py-2.5 text-sm text-right font-semibold">{{ $t->quantite }}</td><td class="px-5 py-2.5"><span class="badge {{ $t->quantite <= $t->seuil_alerte ? 'badge-danger' : 'badge-success' }}">{{ $t->quantite <= $t->seuil_alerte ? 'Alerte' : 'Correct' }}</span></td></tr>@endforeach</tbody>
                </table></div>
            </div>
        @endif
        @if($stockPapiers->isNotEmpty())
            <div class="pharaoh-card overflow-hidden">
                <div class="px-5 py-3 bg-[#080808]"><span class="text-xs font-semibold uppercase tracking-wider text-[#FACE4A]">Papier</span></div>
                <div class="table-scroll"><table class="min-w-full"><thead class="table-head"><tr><th>Imprimante</th><th class="text-right">Reste (m)</th><th class="text-right">%</th><th>Statut</th></tr></thead>
                    <tbody class="table-body">@foreach($stockPapiers as $p)<tr><td class="px-5 py-2.5 text-sm">{{ $p->imprimante }}</td><td class="px-5 py-2.5 text-sm text-right font-semibold">{{ number_format($p->metres_restants, 1) }}</td><td class="px-5 py-2.5 text-sm text-right">{{ $p->pourcentageRestant() }}%</td><td class="px-5 py-2.5"><span class="badge {{ $p->metres_restants <= $p->seuil_alerte ? 'badge-danger' : 'badge-success' }}">{{ $p->metres_restants <= $p->seuil_alerte ? 'Alerte' : 'Correct' }}</span></td></tr>@endforeach</tbody>
                </table></div>
            </div>
        @endif
    </div>
</div>
@endif

<!-- ═══════════ HISTORIQUE ANNULATIONS (30j) ═══════════ -->
@if($annulations->isNotEmpty())
<div class="mb-6">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-[#080808]">Historique des annulations</h2>
            <p class="text-sm text-gray-500">30 derniers jours — {{ $annulations->count() }} commande(s) annulée(s)</p>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-4">
        <div class="stat-card">
            <div class="stat-card-icon bg-red-50 text-red-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div class="stat-card-value">{{ $annulations->count() }}</div>
            <div class="stat-card-label">Commandes annulées</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon bg-orange-50 text-orange-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="stat-card-value">{{ number_format($totalRemboursements, 0, ',', '.') }}</div>
            <div class="stat-card-label">Remboursements (FCFA)</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon bg-gray-50 text-gray-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <div class="stat-card-value">{{ number_format($totalAnnulationsMontant, 0, ',', '.') }}</div>
            <div class="stat-card-label">Total annulé (FCFA)</div>
        </div>
    </div>
    <div class="pharaoh-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#080808] text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Superviseur</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Montant</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Payé (remboursé)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($annulations as $ann)
                    <tr class="bg-red-50/30 hover:bg-red-50/50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-600">#{{ $ann->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $ann->date }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $ann->type }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $ann->superviseur_nom ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-500 line-through">{{ number_format($ann->montant, 0, ',', '.') }} FCFA</td>
                        <td class="px-4 py-3 text-sm text-right font-medium {{ $ann->montant_paye > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                            @if($ann->montant_paye > 0)
                                -{{ number_format($ann->montant_paye, 0, ',', '.') }} FCFA
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

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

<!-- Évolution des évaluations (6 mois) -->
<div class="mb-6">
    <div class="pharaoh-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-[#080808]">Évolution des évaluations</h2>
                <p class="text-sm text-gray-500">Note moyenne sur 6 mois{{ $equipeSelectionnee ? ' — équipe ' . e($equipeSelectionnee) : ' — toute l\'entreprise' }}</p>
            </div>
            <span class="badge badge-gold">Moyenne /20</span>
        </div>

        <!-- Filtre par équipe + exports -->
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <label for="equipe" class="text-sm font-medium text-gray-700">Équipe :</label>
                <select name="equipe" id="equipe" onchange="this.form.submit()" class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold">
                    <option value="">Toute l'entreprise</option>
                    @foreach($equipes ?? [] as $eq)
                        <option value="{{ $eq }}" {{ $equipeSelectionnee === $eq ? 'selected' : '' }}>{{ $eq }}</option>
                    @endforeach
                </select>
            </form>
            <button type="button" onclick="exporterGraphiquePNG()" class="btn-secondary">
                <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Exporter PNG
            </button>
            <a href="{{ route('admin.evaluations.evolution.export', ['equipe' => $equipeSelectionnee]) }}" class="btn-gold">
                <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exporter CSV
            </a>
        </div>

        <div class="h-64">
            <canvas id="evaluationEvolChart"></canvas>
        </div>

        <!-- Moyenne globale + tendance -->
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

        // Export PNG du graphique (S3)
        window.exporterGraphiquePNG = function () {
            const canvas = document.getElementById('evaluationEvolChart');
            if (!canvas) {
                return;
            }
            const url = canvas.toDataURL('image/png');
            const a = document.createElement('a');
            a.href = url;
            a.download = 'evolution_evaluations_' + new Date().toISOString().slice(0, 10) + '.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };
    });

        // ═══════════ MODALS CLIQUABLES ═══════════
        window.toggleStatModal = function(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            if (!modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            } else {
                document.querySelectorAll('[id^="admin-modal-"]').forEach(m => {
                    m.classList.add('hidden'); m.classList.remove('flex');
                });
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        };
        window.closeStatModal = function(event, id) {
            if (event.target === event.currentTarget) toggleStatModal(id);
        };
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('[id^="admin-modal-"]').forEach(m => {
                    if (!m.classList.contains('hidden')) {
                        m.classList.add('hidden'); m.classList.remove('flex');
                        document.body.style.overflow = '';
                    }
                });
            }
        });

        // Graphique CA consolidé 7 jours
        const ctxCaisse = document.getElementById('caisseConsolideChart');
        if (ctxCaisse) {
            const caisseData = @json($caisseChart ?? ['labels' => [], 'entrees' => [], 'sorties' => []]);
            new Chart(ctxCaisse, {
                type: 'bar',
                data: {
                    labels: caisseData.labels,
                    datasets: [
                        { label: 'Entrées', data: caisseData.entrees, backgroundColor: '#2E8B57', borderRadius: 4 },
                        { label: 'Sorties', data: caisseData.sorties, backgroundColor: '#D64545', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' FCFA' } } },
                    plugins: { legend: { position: 'top' } }
                }
            });
        }
    });
</script>
@endpush

