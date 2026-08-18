@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Tableau de bord de l'administrateur</span>
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
<a href="{{ route('admin.dashboard') }}" class="rounded-md bg-3hcig-blue px-3 py-2 text-sm font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('admin.addEmployee') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Ajouter employé</a>
<a href="{{ route('admin.generateReport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Générer Bilan</a>
<a href="{{ route('admin.showEmployeeList') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Liste des employés</a>
<a href="{{ route('notifications.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white relative">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
<a href="{{ route('admin.workplace-locations.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">
    Lieux de travail
</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('admin.dashboard') }}" class="block rounded-md bg-3hcig-blue px-3 py-2 text-base font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('admin.addEmployee') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Ajouter employé</a>
<a href="{{ route('admin.generateReport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Générer Bilan</a>
<a href="{{ route('admin.showEmployeeList') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Liste des employés</a>
<a href="{{ route('notifications.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white relative">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute top-2 right-2 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
<a href="{{ route('admin.workplace-locations.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">
    Lieux de travail
</a>
@endsection

@section('content')
<!-- Vue d'ensemble -->
<div class="mb-6">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-[#080808]">Vue d'ensemble du système</h2>
            <p class="text-sm text-gray-500">Indicateurs clés de l'entreprise aujourd'hui</p>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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

        <!-- Présents aujourd'hui -->
        <div class="stat-card">
            <div class="stat-card-icon bg-blue-50 text-blue-600">
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
            <div class="rounded-full bg-3hcig-green/10 p-3 text-3hcig-green">
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
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                    <tbody class="table-body">@foreach($stockTshirts->take(5) as $t)<tr><td class="px-5 py-2.5 text-sm">{{ $t->couleur }}</td><td class="px-5 py-2.5 text-sm">{{ $t->taille }}</td><td class="px-5 py-2.5 text-sm text-right font-semibold">{{ $t->quantite }}</td><td class="px-5 py-2.5"><span class="badge {{ $t->quantite <= $t->seuil_alerte ? 'badge-danger' : 'badge-success' }}">{{ $t->quantite <= $t->seuil_alerte ? '⚠' : '✓' }}</span></td></tr>@endforeach</tbody>
                </table></div>
            </div>
        @endif
        @if($stockPapiers->isNotEmpty())
            <div class="pharaoh-card overflow-hidden">
                <div class="px-5 py-3 bg-[#080808]"><span class="text-xs font-semibold uppercase tracking-wider text-[#FACE4A]">Papier</span></div>
                <div class="table-scroll"><table class="min-w-full"><thead class="table-head"><tr><th>Imprimante</th><th class="text-right">Reste (m)</th><th class="text-right">%</th><th>Statut</th></tr></thead>
                    <tbody class="table-body">@foreach($stockPapiers as $p)<tr><td class="px-5 py-2.5 text-sm">{{ $p->imprimante }}</td><td class="px-5 py-2.5 text-sm text-right font-semibold">{{ number_format($p->metres_restants, 1) }}</td><td class="px-5 py-2.5 text-sm text-right">{{ $p->pourcentageRestant() }}%</td><td class="px-5 py-2.5"><span class="badge {{ $p->metres_restants <= $p->seuil_alerte ? 'badge-danger' : 'badge-success' }}">{{ $p->metres_restants <= $p->seuil_alerte ? '⚠' : '✓' }}</span></td></tr>@endforeach</tbody>
                </table></div>
            </div>
        @endif
    </div>
</div>
@endif

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
</script>
@endpush

