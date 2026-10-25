@extends('layouts.dashboard')

@section('content_shell', 'flush')

@section('breadcrumb', 'Vue d\'ensemble')

@section('header')
<div class="flex w-full flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="console-title">Tableau de bord</h1>
        <p class="console-subtitle">Pilotage de l'entreprise — indicateurs du {{ now()->format('d/m/Y') }}</p>
    </div>
    @if(Auth::user()->isSuperAdmin())
        <a href="{{ route('admin.metrics') }}" class="btn-secondary inline-flex items-center gap-2 text-sm">
            <svg class="h-4 w-4 text-pharaoh-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 17.5l5-6 3.5 3 4-5.5 4 4" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 20.5h17" />
            </svg>
            Métriques système
        </a>
    @endif
</div>
@endsection

@section('tabs')
<a href="#admin-overview" class="console-tab is-active" aria-current="page">Vue d'ensemble</a>
<a href="#admin-caisses" class="console-tab">Caisses</a>
<a href="#admin-evaluations" class="console-tab">Évaluations</a>
<a href="#admin-stock" class="console-tab">Stock</a>
<a href="#admin-annulations" class="console-tab">Annulations</a>
@endsection

@section('content')
@php
    $caisses = $caisses ?? [];
    $caJour = collect($caisses)->sum('entrees');
    $soldeJour = collect($caisses)->sum('net');
    $totalAlertes = ($alertesStock ?? 0);
@endphp

<div class="space-y-5">
    <!-- ═══ Indicateurs clés ═══ -->
    <div id="admin-overview" class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-6">
        @include('partials.dashboard.kpi', ['label' => 'Employés', 'value' => $totalEmployees ?? 0, 'meta' => 'Effectif total'])
        @include('partials.dashboard.kpi', ['label' => 'Superviseurs', 'value' => $totalSupervisors ?? 0, 'meta' => 'Postes encadrés'])
        @include('partials.dashboard.kpi', ['label' => 'Présents', 'value' => $presentToday ?? 0, 'meta' => 'Voir le détail', 'tone' => 'success', 'onclick' => "toggleStatModal('admin-modal-presents')"])
        @include('partials.dashboard.kpi', ['label' => 'Absents', 'value' => $absentToday ?? 0, 'meta' => 'Voir le détail', 'tone' => 'danger', 'onclick' => "toggleStatModal('admin-modal-absents')"])
        @include('partials.dashboard.kpi', ['label' => 'CA du jour', 'value' => number_format($caJour, 0, ',', ' '), 'meta' => 'FCFA encaissés', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'Retards', 'value' => $monthlyLates ?? 0, 'meta' => 'Ce mois-ci', 'tone' => 'warning'])
    </div>

    <!-- ═══ Graphiques principaux ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section class="console-card lg:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Chiffre d'affaires — 7 derniers jours</h2>
                    <p class="console-card-sub">Entrées et sorties consolidées des caisses</p>
                </div>
                <a href="{{ route('admin.caisse') }}" class="text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">Voir tout →</a>
            </div>
            <div class="console-card-body">
                <div class="h-56"><canvas id="caisseConsolideChart"></canvas></div>
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Bilan du mois</h2>
                    <p class="console-card-sub">Présences, absences et retards</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="relative h-44">
                    <canvas id="adminMonthlyChart"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-xl font-extrabold text-[#080808]">{{ $monthlyPresences ?? 0 }}</span>
                        <span class="text-[0.6875rem] uppercase tracking-wide text-gray-400">Présences</span>
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <div class="console-panel-row">
                        <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-green-600"></span>Présences</span>
                        <span class="text-sm font-semibold text-[#080808]">{{ $monthlyPresences ?? 0 }}</span>
                    </div>
                    <div class="console-panel-row">
                        <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-red-500"></span>Absences</span>
                        <span class="text-sm font-semibold text-[#080808]">{{ $monthlyAbsences ?? 0 }}</span>
                    </div>
                    <div class="console-panel-row">
                        <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-amber-500"></span>Retards</span>
                        <span class="text-sm font-semibold text-[#080808]">{{ $monthlyLates ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- ═══ Évaluations + pointage ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="admin-evaluations" class="console-card lg:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Évolution des évaluations</h2>
                    <p class="console-card-sub">Note moyenne sur 6 mois{{ $equipeSelectionnee ? ' — équipe ' . e($equipeSelectionnee) : ' — toute l\'entreprise' }}</p>
                </div>
                <span class="badge badge-gold">Moyenne /20</span>
            </div>
            <div class="console-card-body">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <label for="equipe" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Équipe</label>
                        <select name="equipe" id="equipe" onchange="this.form.submit()" class="select-field py-2 text-sm">
                            <option value="">Toute l'entreprise</option>
                            @foreach($equipes ?? [] as $eq)
                                <option value="{{ $eq }}" {{ $equipeSelectionnee === $eq ? 'selected' : '' }}>{{ $eq }}</option>
                            @endforeach
                        </select>
                    </form>
                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" onclick="exporterGraphiquePNG()" class="btn-secondary inline-flex items-center gap-1.5 text-xs">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Exporter PNG
                        </button>
                        <a href="{{ route('admin.evaluations.evolution.export', ['equipe' => $equipeSelectionnee]) }}" class="btn-gold inline-flex items-center gap-1.5 text-xs">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Exporter CSV
                        </a>
                    </div>
                </div>

                <div class="h-56"><canvas id="evaluationEvolChart"></canvas></div>

                @php
                    $moyenneEvol = $statsEvolution['moyenne'] ?? 0;
                    $tendanceEvol = $statsEvolution['tendance'] ?? 'stable';
                    $deltaEvol = $statsEvolution['delta'] ?? 0;
                @endphp
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-200/70 bg-gray-50 px-4 py-3">
                        <div class="text-[0.6875rem] font-semibold uppercase tracking-wide text-gray-500">Moyenne globale (6 mois)</div>
                        <div class="mt-1 flex items-baseline gap-1">
                            <span class="text-xl font-bold text-[#080808]">{{ number_format($moyenneEvol, 1, ',', '') }}</span>
                            <span class="text-xs text-gray-500">/20</span>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200/70 bg-gray-50 px-4 py-3">
                        <div class="text-[0.6875rem] font-semibold uppercase tracking-wide text-gray-500">Tendance (dernier mois)</div>
                        <div class="mt-1">
                            @if($tendanceEvol === 'hausse')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-700">▲ Hausse {{ $deltaEvol > 0 ? '+' . number_format($deltaEvol, 1, ',', '') : '' }} pt</span>
                            @elseif($tendanceEvol === 'baisse')
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-700">▼ Baisse {{ number_format(abs($deltaEvol), 1, ',', '') }} pt</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-bold text-gray-600">— Stable</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="space-y-4">
            <section class="console-card">
                <div class="console-card-head">
                    <div>
                        <h2 class="console-card-title">Pointage du jour</h2>
                        <p class="console-card-sub">Arrivées et départs enregistrés</p>
                    </div>
                </div>
                <div class="console-card-body">
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="toggleStatModal('admin-modal-arrives')" class="rounded-xl border border-gray-200/70 bg-gray-50 px-3 py-3 text-left transition-colors hover:border-emerald-300 hover:bg-emerald-50/40">
                            <span class="block text-[0.6875rem] font-semibold uppercase tracking-wide text-gray-500">Arrivés</span>
                            <span class="mt-1 block text-xl font-extrabold text-emerald-700">{{ $arrivesAujourdhui->count() }}</span>
                        </button>
                        <button type="button" onclick="toggleStatModal('admin-modal-partis')" class="rounded-xl border border-gray-200/70 bg-gray-50 px-3 py-3 text-left transition-colors hover:border-orange-300 hover:bg-orange-50/40">
                            <span class="block text-[0.6875rem] font-semibold uppercase tracking-wide text-gray-500">Partis</span>
                            <span class="mt-1 block text-xl font-extrabold text-orange-600">{{ $partisAujourdhui->count() }}</span>
                        </button>
                    </div>

                    <div class="console-scroll mt-4">
                        @forelse($presentsAujourdhui->take(6) as $p)
                            <div class="console-panel-row">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="console-dot bg-green-600"></span>
                                    <span class="truncate text-sm text-gray-700">{{ $p->nom }}</span>
                                </span>
                                <span class="whitespace-nowrap text-xs font-semibold text-gray-500">
                                    {{ $p->heureArrivee ? \Carbon\Carbon::parse($p->heureArrivee)->format('H:i') : '—' }}
                                    @if($p->heureDepart) → {{ \Carbon\Carbon::parse($p->heureDepart)->format('H:i') }}@endif
                                </span>
                            </div>
                        @empty
                            <p class="py-4 text-center text-sm text-gray-400">Aucun employé présent</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="console-card">
                <div class="console-card-head">
                    <div>
                        <h2 class="console-card-title">Accès rapides</h2>
                        <p class="console-card-sub">Actions les plus utilisées</p>
                    </div>
                </div>
                <div class="console-card-body space-y-2">
                    <a href="{{ route('admin.addEmployee') }}" class="console-quick group">
                        <span class="console-quick-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-[#080808]">Ajouter employé</span>
                            <span class="block text-xs text-gray-500">Nouvelle inscription au système</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.showEmployeeList') }}" class="console-quick group">
                        <span class="console-quick-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9.25" cy="8.5" r="3" /><path d="M3.75 20c0-3 2.45-5 5.5-5s5.5 2 5.5 5" /></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-[#080808]">Liste des employés</span>
                            <span class="block text-xs text-gray-500">Consulter et gérer l'effectif</span>
                        </span>
                    </a>
                    <a href="{{ route('admin.generateReport') }}" class="console-quick group">
                        <span class="console-quick-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 3.5h7l4.5 4.5v12a1 1 0 0 1-1 1h-10.5a1 1 0 0 1-1-1v-15.5a1 1 0 0 1 1-1z" /><path d="M13.5 3.5V8H18" /></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-[#080808]">Générer Bilan</span>
                            <span class="block text-xs text-gray-500">Créer un rapport détaillé</span>
                        </span>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="console-quick group">
                        <span class="console-quick-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18 15.5V11a6 6 0 1 0-12 0v4.5L4.5 18h15z" /><path d="M9.75 18a2.25 2.25 0 0 0 4.5 0" /></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-[#080808]">Notifications</span>
                            <span class="block text-xs text-gray-500">Alertes d'absence et de retard</span>
                        </span>
                    </a>
                </div>
            </section>
        </div>
    </div>

    <!-- ═══ État des caisses ═══ -->
    @if(!empty($caisses))
        <section id="admin-caisses" class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">État des caisses — {{ now()->format('d/m/Y') }}</h2>
                    <p class="console-card-sub">Chiffre d'affaires journalier par poste · solde net {{ number_format($soldeJour, 0, ',', ' ') }} FCFA</p>
                </div>
                <a href="{{ route('admin.caisse') }}" class="text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">Voir tout →</a>
            </div>
            <div class="console-card-body">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach($caisses as $label => $c)
                        <div class="rounded-xl border border-gray-200/70 bg-gray-50/60 p-4">
                            <div class="mb-3 flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FBF3E6] text-[#885910]">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 8.5A2.5 2.5 0 0 1 6 6h11.5A2.5 2.5 0 0 1 20 8.5v8A2.5 2.5 0 0 1 17.5 19H6a2.5 2.5 0 0 1-2.5-2.5z" /><circle cx="16" cy="12.5" r="1.2" /></svg>
                                </span>
                                <span class="text-sm font-semibold text-[#080808]">{{ $c['nom'] }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="rounded-lg bg-white px-2 py-2 text-center">
                                    <div class="text-sm font-bold text-green-700">{{ number_format($c['entrees'], 0, ',', ' ') }}</div>
                                    <div class="text-[0.625rem] uppercase tracking-wide text-gray-400">Entrées</div>
                                </div>
                                <div class="rounded-lg bg-white px-2 py-2 text-center">
                                    <div class="text-sm font-bold text-red-600">{{ number_format($c['sorties'], 0, ',', ' ') }}</div>
                                    <div class="text-[0.625rem] uppercase tracking-wide text-gray-400">Sorties</div>
                                </div>
                                <div class="rounded-lg bg-white px-2 py-2 text-center">
                                    <div class="text-sm font-bold text-pharaoh-bronze">{{ number_format($c['net'], 0, ',', ' ') }}</div>
                                    <div class="text-[0.625rem] uppercase tracking-wide text-gray-400">Net</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- ═══ État du stock ═══ -->
    @if($stockTshirts->isNotEmpty() || $stockPapiers->isNotEmpty())
        <section id="admin-stock" class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">État du stock</h2>
                    <p class="console-card-sub">
                        {{ $totalStockTshirts }} T-shirts · {{ $stockPapiers->count() }} imprimante(s)
                        @if($totalAlertes > 0)
                            <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-[0.6875rem] font-bold text-red-700">{{ $totalAlertes }} alerte(s)</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.stock') }}" class="text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">Voir tout →</a>
            </div>
            <div class="console-card-body">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @if($stockTshirts->isNotEmpty())
                        <div class="overflow-hidden rounded-xl border border-gray-200/70">
                            <div class="bg-[#080808] px-4 py-2.5">
                                <span class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#FACE4A]">T-Shirts</span>
                            </div>
                            <div class="table-scroll">
                                <table class="min-w-full">
                                    <thead class="table-head">
                                        <tr><th>Couleur</th><th>Taille</th><th class="text-right">Qté</th><th>Statut</th></tr>
                                    </thead>
                                    <tbody class="table-body">
                                        @foreach($stockTshirts->take(5) as $t)
                                            <tr>
                                                <td class="px-4 py-2.5 text-sm">{{ $t->couleur }}</td>
                                                <td class="px-4 py-2.5 text-sm">{{ $t->taille }}</td>
                                                <td class="px-4 py-2.5 text-right text-sm font-semibold">{{ $t->quantite }}</td>
                                                <td class="px-4 py-2.5">
                                                    <span class="badge {{ $t->quantite <= $t->seuil_alerte ? 'badge-danger' : 'badge-success' }}">{{ $t->quantite <= $t->seuil_alerte ? 'Alerte' : 'Correct' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if($stockPapiers->isNotEmpty())
                        <div class="overflow-hidden rounded-xl border border-gray-200/70">
                            <div class="bg-[#080808] px-4 py-2.5">
                                <span class="text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-[#FACE4A]">Papier</span>
                            </div>
                            <div class="table-scroll">
                                <table class="min-w-full">
                                    <thead class="table-head">
                                        <tr><th>Imprimante</th><th class="text-right">Reste (m)</th><th class="text-right">%</th><th>Statut</th></tr>
                                    </thead>
                                    <tbody class="table-body">
                                        @foreach($stockPapiers as $p)
                                            <tr>
                                                <td class="px-4 py-2.5 text-sm">{{ $p->imprimante }}</td>
                                                <td class="px-4 py-2.5 text-right text-sm font-semibold">{{ number_format($p->metres_restants, 1) }}</td>
                                                <td class="px-4 py-2.5 text-right text-sm">{{ $p->pourcentageRestant() }}%</td>
                                                <td class="px-4 py-2.5">
                                                    <span class="badge {{ $p->metres_restants <= $p->seuil_alerte ? 'badge-danger' : 'badge-success' }}">{{ $p->metres_restants <= $p->seuil_alerte ? 'Alerte' : 'Correct' }}</span>
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
        </section>
    @endif

    <!-- ═══ Annulations ═══ -->
    @if($annulations->isNotEmpty())
        <section id="admin-annulations" class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Historique des annulations</h2>
                    <p class="console-card-sub">30 derniers jours — {{ $annulations->count() }} commande(s) annulée(s)</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-xl border border-gray-200/70 bg-gray-50 px-4 py-3">
                        <div class="text-[0.6875rem] font-semibold uppercase tracking-wide text-gray-500">Commandes annulées</div>
                        <div class="mt-1 text-lg font-bold text-[#080808]">{{ $annulations->count() }}</div>
                    </div>
                    <div class="rounded-xl border border-gray-200/70 bg-gray-50 px-4 py-3">
                        <div class="text-[0.6875rem] font-semibold uppercase tracking-wide text-gray-500">Remboursements</div>
                        <div class="mt-1 text-lg font-bold text-orange-600">{{ number_format($totalRemboursements, 0, ',', ' ') }} <span class="text-xs font-medium text-gray-400">FCFA</span></div>
                    </div>
                    <div class="rounded-xl border border-gray-200/70 bg-gray-50 px-4 py-3">
                        <div class="text-[0.6875rem] font-semibold uppercase tracking-wide text-gray-500">Total annulé</div>
                        <div class="mt-1 text-lg font-bold text-red-600">{{ number_format($totalAnnulationsMontant, 0, ',', ' ') }} <span class="text-xs font-medium text-gray-400">FCFA</span></div>
                    </div>
                </div>

                <div class="table-scroll overflow-hidden rounded-xl border border-gray-200/70">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="table-head">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Superviseur</th>
                                <th class="text-right">Montant</th>
                                <th class="text-right">Payé (remboursé)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($annulations as $ann)
                                <tr class="bg-red-50/30 hover:bg-red-50/60">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-600">#{{ $ann->id }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $ann->date }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $ann->type }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $ann->superviseur_nom ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-500 line-through">{{ number_format($ann->montant, 0, ',', ' ') }} FCFA</td>
                                    <td class="px-4 py-3 text-right text-sm font-medium {{ $ann->montant_paye > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                                        @if($ann->montant_paye > 0)
                                            -{{ number_format($ann->montant_paye, 0, ',', ' ') }} FCFA
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
        </section>
    @endif

    <!-- ═══ Dernières notifications ═══ -->
    @if(Auth::user()->notifications->count() > 0)
        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Dernières notifications</h2>
                    <p class="console-card-sub">Alertes récentes de l'entreprise</p>
                </div>
                <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">Voir toutes →</a>
            </div>
            <div class="console-card-body divide-y divide-gray-100">
                @foreach(Auth::user()->notifications->take(3) as $notification)
                    <div class="flex items-start justify-between gap-3 py-3 first:pt-0 last:pb-0">
                        <p class="flex items-start gap-2 text-sm {{ $notification->read_at ? 'text-gray-500' : 'font-medium text-gray-700' }}">
                            <span class="console-dot mt-1.5 {{ $notification->read_at ? 'bg-gray-300' : 'bg-pharaoh-gold' }}"></span>
                            {{ isset($notification->data['message']) ? $notification->data['message'] : 'Notification' }}
                        </p>
                        <span class="whitespace-nowrap text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

<!-- ═══════════ MODALES DE DÉTAIL ═══════════ -->
<div id="admin-modal-presents" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'admin-modal-presents')">
    <div class="mx-4 w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl" onclick="event.stopPropagation()">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-[#080808]">Personnes présentes</h3>
            <button type="button" onclick="toggleStatModal('admin-modal-presents')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="max-h-64 space-y-1 overflow-y-auto">
            @forelse($presentsAujourdhui as $p)
                <div class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-2">
                    <span class="text-sm font-semibold text-gray-800">{{ $p->nom }}</span>
                    <span class="text-xs font-bold text-blue-700">
                        {{ $p->heureArrivee ? \Carbon\Carbon::parse($p->heureArrivee)->format('H:i') : '—' }}
                        @if($p->heureDepart) → {{ \Carbon\Carbon::parse($p->heureDepart)->format('H:i') }}@endif
                    </span>
                </div>
            @empty
                <p class="py-4 text-center text-sm text-gray-400">Aucun employé présent</p>
            @endforelse
        </div>
    </div>
</div>

<div id="admin-modal-absents" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'admin-modal-absents')">
    <div class="mx-4 w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl" onclick="event.stopPropagation()">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-[#080808]">Personnes absentes</h3>
            <button type="button" onclick="toggleStatModal('admin-modal-absents')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="max-h-64 space-y-1 overflow-y-auto">
            @forelse($absentsAujourdhui as $a)
                <div class="flex items-center justify-between rounded-lg bg-red-50 px-3 py-2">
                    <span class="text-sm font-semibold text-gray-800">{{ $a->nom }}</span>
                    <span class="text-xs font-bold text-red-600">Absent</span>
                </div>
            @empty
                <p class="py-4 text-center text-sm text-gray-400">Tout le monde est présent</p>
            @endforelse
        </div>
    </div>
</div>

<div id="admin-modal-arrives" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'admin-modal-arrives')">
    <div class="mx-4 w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl" onclick="event.stopPropagation()">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-[#080808]">Heures d'arrivée</h3>
            <button type="button" onclick="toggleStatModal('admin-modal-arrives')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="max-h-64 space-y-1 overflow-y-auto">
            @forelse($arrivesAujourdhui as $arr)
                <div class="flex items-center justify-between rounded-lg bg-emerald-50 px-3 py-2">
                    <span class="text-sm font-semibold text-gray-800">{{ $arr->nom }}</span>
                    <span class="text-xs font-bold text-emerald-700">{{ \Carbon\Carbon::parse($arr->heureArrivee)->format('H:i') }}</span>
                </div>
            @empty
                <p class="py-4 text-center text-sm text-gray-400">Aucune arrivée enregistrée</p>
            @endforelse
        </div>
    </div>
</div>

<div id="admin-modal-partis" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'admin-modal-partis')">
    <div class="mx-4 w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl" onclick="event.stopPropagation()">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-[#080808]">Heures de départ</h3>
            <button type="button" onclick="toggleStatModal('admin-modal-partis')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="max-h-64 space-y-1 overflow-y-auto">
            @forelse($partisAujourdhui as $par)
                <div class="flex items-center justify-between rounded-lg bg-orange-50 px-3 py-2">
                    <span class="text-sm font-semibold text-gray-800">{{ $par->nom }}</span>
                    <span class="text-xs font-bold text-orange-600">{{ \Carbon\Carbon::parse($par->heureDepart)->format('H:i') }}</span>
                </div>
            @empty
                <p class="py-4 text-center text-sm text-gray-400">Aucun départ enregistré</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ═══ Modales de détail ═══
    window.toggleStatModal = function (id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        const isOpen = !modal.classList.contains('hidden');

        document.querySelectorAll('[id^="admin-modal-"]').forEach(m => {
            m.classList.add('hidden');
            m.classList.remove('flex');
        });

        if (!isOpen) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    };

    window.closeStatModal = function (event, id) {
        if (event.target === event.currentTarget) toggleStatModal(id);
    };

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('[id^="admin-modal-"]').forEach(m => {
            if (!m.classList.contains('hidden')) {
                m.classList.add('hidden');
                m.classList.remove('flex');
                document.body.style.overflow = '';
            }
        });
    });

    // ═══ Export PNG du graphique d'évaluations ═══
    window.exporterGraphiquePNG = function () {
        const canvas = document.getElementById('evaluationEvolChart');
        if (!canvas) return;

        const a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = 'evolution_evaluations_' + new Date().toISOString().slice(0, 10) + '.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Chiffre d'affaires consolidé (7 jours)
        const ctxCaisse = document.getElementById('caisseConsolideChart');
        if (ctxCaisse) {
            const caisseData = @json($caisseChart ?? ['labels' => [], 'entrees' => [], 'sorties' => []]);
            new Chart(ctxCaisse, {
                type: 'bar',
                data: {
                    labels: caisseData.labels,
                    datasets: [
                        { label: 'Entrées', data: caisseData.entrees, backgroundColor: '#D39B23', borderRadius: 6, maxBarThickness: 22 },
                        { label: 'Sorties', data: caisseData.sorties, backgroundColor: '#111111', borderRadius: 6, maxBarThickness: 22 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 11 } } },
                        tooltip: { callbacks: { label: ctx => ctx.dataset.label + ' : ' + Number(ctx.parsed.y).toLocaleString() + ' FCFA' } }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { callback: v => Number(v).toLocaleString() } }
                    }
                }
            });
        }

        // Bilan du mois (donut)
        const ctxMonthly = document.getElementById('adminMonthlyChart');
        if (ctxMonthly) {
            new Chart(ctxMonthly, {
                type: 'doughnut',
                data: {
                    labels: ['Présences', 'Absences', 'Retards'],
                    datasets: [{
                        data: [{{ (int) ($monthlyPresences ?? 0) }}, {{ (int) ($monthlyAbsences ?? 0) }}, {{ (int) ($monthlyLates ?? 0) }}],
                        backgroundColor: ['#2E8B57', '#D64545', '#D97706'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        // Évolution des évaluations (6 mois)
        const ctxEval = document.getElementById('evaluationEvolChart');
        if (ctxEval) {
            const evolution = @json($evolutionEvaluations ?? ['labels' => [], 'notes' => [], 'couleurs' => []]);
            new Chart(ctxEval, {
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
                        tooltip: { callbacks: { label: context => 'Moyenne : ' + context.parsed.y + '/20' } }
                    },
                    scales: {
                        y: { min: 0, max: 20, ticks: { stepSize: 4 } },
                        x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 0 } }
                    }
                }
            });
        }
    });
</script>
@endpush
