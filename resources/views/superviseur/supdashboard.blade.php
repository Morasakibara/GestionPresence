@extends('layouts.dashboard')

@section('content_shell', 'flush')

@section('breadcrumb', 'Vue d\'ensemble')

@section('header')
<div class="flex w-full flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="console-title">Tableau de bord</h1>
        <p class="console-subtitle">Équipe {{ $equipe ?? 'non définie' }} — {{ $teamMemberCount ?? 0 }} membre(s) · {{ now()->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('superviseur.showFollowPresence') }}" class="btn-gold inline-flex items-center gap-2 text-sm">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="9.25" cy="8.5" r="3" /><path d="M3.75 20c0-3 2.45-5 5.5-5s5.5 2 5.5 5" />
        </svg>
        Suivre les présences
    </a>
</div>
@endsection

@section('tabs')
<a href="#sup-overview" class="console-tab is-active" aria-current="page">Vue d'ensemble</a>
<a href="#sup-evaluations" class="console-tab">Évaluations</a>
<a href="#sup-notes" class="console-tab">Notes par membre</a>
<a href="#sup-notifications" class="console-tab">Notifications</a>
@endsection

@section('content')
@php
    $notesParEmploye = $notesParEmploye ?? [];
    $membresChart = collect($notesParEmploye)
        ->map(fn ($e) => ['nom' => $e['nom'] ?? '—', 'moyenne' => (float) ($e['moyenne'] ?? 0)])
        ->sortByDesc('moyenne')
        ->values();
@endphp

<div class="space-y-5">
    <!-- ═══ Statut de l'équipe ═══ -->
    <div id="sup-overview" class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-6">
        @include('partials.dashboard.kpi', ['label' => 'Membres', 'value' => $teamMemberCount ?? 0, 'meta' => 'Équipe ' . ($equipe ?? '—')])
        @include('partials.dashboard.kpi', ['label' => 'Présents', 'value' => $presentToday ?? 0, 'meta' => 'Voir le détail', 'tone' => 'success', 'onclick' => "toggleStatModal('modal-presents')"])
        @include('partials.dashboard.kpi', ['label' => 'Absents', 'value' => $absentToday ?? 0, 'meta' => 'Voir le détail', 'tone' => 'danger', 'onclick' => "toggleStatModal('modal-absents')"])
        @include('partials.dashboard.kpi', ['label' => 'Retards', 'value' => $lateToday ?? 0, 'meta' => 'Arrivées après 08:00', 'tone' => 'warning'])
        @include('partials.dashboard.kpi', ['label' => 'Arrivés', 'value' => $arrivesAujourdhui->count(), 'meta' => 'Voir le détail', 'tone' => 'info', 'onclick' => "toggleStatModal('modal-arrives')"])
        @include('partials.dashboard.kpi', ['label' => 'Partis', 'value' => $partisAujourdhui->count(), 'meta' => 'Voir le détail', 'onclick' => "toggleStatModal('modal-partis')"])
    </div>

    <!-- ═══ Évaluations + statut ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="sup-evaluations" class="console-card lg:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Évolution des évaluations de l'équipe</h2>
                    <p class="console-card-sub">Note moyenne sur 6 mois — équipe {{ $equipe ?? '' }}</p>
                </div>
                <span class="badge badge-gold">Moyenne /20</span>
            </div>
            <div class="console-card-body">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <button type="button" onclick="exporterGraphiquePNG()" class="btn-secondary inline-flex items-center gap-1.5 text-xs">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Exporter PNG
                    </button>
                    <a href="{{ route('superviseur.evaluations.evolution.export') }}" class="btn-gold inline-flex items-center gap-1.5 text-xs">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Exporter CSV
                    </a>
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
                        <h2 class="console-card-title">Statut du jour</h2>
                        <p class="console-card-sub">Répartition de l'équipe</p>
                    </div>
                </div>
                <div class="console-card-body">
                    <div class="relative h-40">
                        <canvas id="supStatutChart"></canvas>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-xl font-extrabold text-[#080808]">{{ $teamMemberCount ?? 0 }}</span>
                            <span class="text-[0.6875rem] uppercase tracking-wide text-gray-400">Membres</span>
                        </div>
                    </div>
                    <div class="mt-4 space-y-1">
                        <div class="console-panel-row">
                            <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-green-600"></span>Présents</span>
                            <span class="text-sm font-semibold text-[#080808]">{{ $presentToday ?? 0 }}</span>
                        </div>
                        <div class="console-panel-row">
                            <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-red-500"></span>Absents</span>
                            <span class="text-sm font-semibold text-[#080808]">{{ $absentToday ?? 0 }}</span>
                        </div>
                        <div class="console-panel-row">
                            <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-amber-500"></span>Retards</span>
                            <span class="text-sm font-semibold text-[#080808]">{{ $lateToday ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="console-card">
                <div class="console-card-head">
                    <div>
                        <h2 class="console-card-title">Accès rapides</h2>
                        <p class="console-card-sub">Gestion de l'équipe</p>
                    </div>
                </div>
                <div class="console-card-body space-y-2">
                    <a href="{{ route('superviseur.showAddMember') }}" class="console-quick group">
                        <span class="console-quick-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10" cy="8.25" r="3.25" /><path d="M4 20c0-3.1 2.7-5.25 6-5.25" /><path d="M18 14.5v5M15.5 17h5" /></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-[#080808]">Ajout de membre</span>
                            <span class="block text-xs text-gray-500">Intégrer un employé à l'équipe</span>
                        </span>
                    </a>
                    <a href="{{ route('superviseur.generateReport2') }}" class="console-quick group">
                        <span class="console-quick-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 3.5h7l4.5 4.5v12a1 1 0 0 1-1 1h-10.5a1 1 0 0 1-1-1v-15.5a1 1 0 0 1 1-1z" /><path d="M13.5 3.5V8H18" /></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-[#080808]">Générer Rapports</span>
                            <span class="block text-xs text-gray-500">Évaluer et exporter les bilans</span>
                        </span>
                    </a>
                    <a href="{{ route('superviseur.rendements') }}" class="console-quick group">
                        <span class="console-quick-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 19V5M4 19h16" /><path d="M7 15l3.5-4 3 2.5L20 7.5" /></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-[#080808]">Rendements</span>
                            <span class="block text-xs text-gray-500">Fiches de rendement de l'équipe</span>
                        </span>
                    </a>
                </div>
            </section>
        </div>
    </div>

    <!-- ═══ Notes par membre + notifications ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="sup-notes" class="console-card lg:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Notes par membre</h2>
                    <p class="console-card-sub">Moyenne sur 6 mois — classement décroissant</p>
                </div>
                @if(!empty($notesParEmploye))
                    <a href="{{ route('superviseur.comparatif.export') }}" class="btn-gold inline-flex items-center gap-1.5 text-xs">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Exporter PDF
                    </a>
                @endif
            </div>
            <div class="console-card-body">
                @if($membresChart->isNotEmpty())
                    <div class="h-56"><canvas id="notesMembresChart"></canvas></div>
                @else
                    <div class="console-empty">
                        <p class="text-sm text-gray-400">Aucune évaluation enregistrée sur les 6 derniers mois.</p>
                    </div>
                @endif
            </div>
        </section>

        <section id="sup-notifications" class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Dernières notifications</h2>
                    <p class="console-card-sub">Alertes de l'équipe</p>
                </div>
                <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">Voir toutes →</a>
            </div>
            <div class="console-card-body">
                @forelse(Auth::user()->notifications->take(4) as $notification)
                    <div class="console-panel-row items-start">
                        <p class="flex items-start gap-2 pr-2 text-sm {{ $notification->read_at ? 'text-gray-500' : 'font-medium text-gray-700' }}">
                            <span class="console-dot mt-1.5 {{ $notification->read_at ? 'bg-gray-300' : 'bg-pharaoh-gold' }}"></span>
                            {{ isset($notification->data['message']) ? $notification->data['message'] : 'Notification' }}
                        </p>
                        <span class="whitespace-nowrap text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="console-empty">
                        <p class="text-sm text-gray-400">Aucune notification pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <!-- ═══ Tableau comparatif ═══ -->
    @if(!empty($notesParEmploye))
        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Notes par membre (6 mois)</h2>
                    <p class="console-card-sub">Filtrer par couleur d'évaluation ou trier par moyenne</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-medium text-gray-500">Filtrer :</span>
                    <button type="button" onclick="filtrerTableau('all')" class="badge badge-gold filter-btn active" data-filter="all">Tous</button>
                    <button type="button" onclick="filtrerTableau('vert')" class="badge badge-success filter-btn" data-filter="vert">Vert</button>
                    <button type="button" onclick="filtrerTableau('orange')" class="badge badge-warning filter-btn" data-filter="orange">Orange</button>
                    <button type="button" onclick="filtrerTableau('rouge')" class="badge badge-danger filter-btn" data-filter="rouge">Rouge</button>
                    <span class="ml-2 text-xs font-medium text-gray-500">Trier :</span>
                    <button type="button" onclick="trierTableau('nom')" class="badge filter-btn" data-sort="nom">Nom</button>
                    <button type="button" onclick="trierTableau('moyenne-desc')" class="badge filter-btn" data-sort="moyenne-desc">Moyenne ↓</button>
                    <button type="button" onclick="trierTableau('moyenne-asc')" class="badge filter-btn" data-sort="moyenne-asc">Moyenne ↑</button>
                </div>

                <div class="table-scroll overflow-hidden rounded-xl border border-gray-200/70">
                    <table id="tableauComparatif" class="min-w-full divide-y divide-gray-200">
                        <thead class="table-head">
                            <tr>
                                <th scope="col">Employé</th>
                                @foreach(($notesParEmploye[0]['moisListe'] ?? []) as $m)
                                    <th scope="col" class="text-center">{{ $m['label'] }}</th>
                                @endforeach
                                <th scope="col" class="text-center">Moyenne</th>
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
                                                <span class="badge {{ $cell['couleur'] === 'vert' ? 'badge-success' : ($cell['couleur'] === 'rouge' ? 'badge-danger' : 'badge-warning') }}">{{ $cell['note'] }}/20</span>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center">
                                        <span class="badge {{ $emp['couleur'] === 'vert' ? 'badge-success' : ($emp['couleur'] === 'rouge' ? 'badge-danger' : 'badge-warning') }}">{{ $emp['moyenne'] }}/20</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif
</div>

<!-- ═══════════ MODALES DE DÉTAIL ═══════════ -->
<div id="modal-presents" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'modal-presents')">
    <div class="mx-4 w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl" onclick="event.stopPropagation()">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-[#080808]">Personnes présentes</h3>
            <button type="button" onclick="toggleStatModal('modal-presents')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="max-h-64 space-y-1 overflow-y-auto">
            @forelse($presentsAujourdhui as $p)
                <div class="flex items-center justify-between rounded-lg bg-green-50 px-3 py-2">
                    <span class="text-sm font-semibold text-gray-800">{{ $p->nom }}</span>
                    <span class="text-xs font-bold text-green-700">
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

<div id="modal-absents" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'modal-absents')">
    <div class="mx-4 w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl" onclick="event.stopPropagation()">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-[#080808]">Personnes absentes</h3>
            <button type="button" onclick="toggleStatModal('modal-absents')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Fermer">
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

<div id="modal-arrives" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'modal-arrives')">
    <div class="mx-4 w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl" onclick="event.stopPropagation()">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-[#080808]">Heures d'arrivée</h3>
            <button type="button" onclick="toggleStatModal('modal-arrives')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="max-h-64 space-y-1 overflow-y-auto">
            @forelse($arrivesAujourdhui as $arr)
                <div class="flex items-center justify-between rounded-lg bg-emerald-50 px-3 py-2">
                    <span class="text-sm font-semibold text-gray-800">{{ $arr->nom }}</span>
                    <span class="text-xs font-bold text-emerald-700">{{ $arr->heureArrivee ? \Carbon\Carbon::parse($arr->heureArrivee)->format('H:i') : '—' }}</span>
                </div>
            @empty
                <p class="py-4 text-center text-sm text-gray-400">Aucune arrivée enregistrée</p>
            @endforelse
        </div>
    </div>
</div>

<div id="modal-partis" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 backdrop-blur-sm" onclick="closeStatModal(event, 'modal-partis')">
    <div class="mx-4 w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl" onclick="event.stopPropagation()">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-bold text-[#080808]">Heures de départ</h3>
            <button type="button" onclick="toggleStatModal('modal-partis')" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="max-h-64 space-y-1 overflow-y-auto">
            @forelse($partisAujourdhui as $par)
                <div class="flex items-center justify-between rounded-lg bg-orange-50 px-3 py-2">
                    <span class="text-sm font-semibold text-gray-800">{{ $par->nom }}</span>
                    <span class="text-xs font-bold text-orange-600">{{ $par->heureDepart ? \Carbon\Carbon::parse($par->heureDepart)->format('H:i') : '—' }}</span>
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
    function toggleStatModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;

        const isOpen = !modal.classList.contains('hidden');

        ['modal-presents', 'modal-absents', 'modal-arrives', 'modal-partis'].forEach(function (other) {
            const el = document.getElementById(other);
            if (el) {
                el.classList.add('hidden');
                el.classList.remove('flex');
            }
        });

        if (!isOpen) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }

    function closeStatModal(event, id) {
        if (event.target === event.currentTarget) toggleStatModal(id);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        ['modal-presents', 'modal-absents', 'modal-arrives', 'modal-partis'].forEach(function (other) {
            const el = document.getElementById(other);
            if (el && !el.classList.contains('hidden')) {
                el.classList.add('hidden');
                el.classList.remove('flex');
                document.body.style.overflow = '';
            }
        });
    });

    // ═══ Export PNG du graphique ═══
    function exporterGraphiquePNG() {
        const canvas = document.getElementById('evaluationEvolChart');
        if (!canvas) return;

        const a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = 'evolution_evaluations_equipe_' + new Date().toISOString().slice(0, 10) + '.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    // ═══ Filtrage et tri du tableau comparatif ═══
    function filtrerTableau(couleur) {
        document.querySelectorAll('#tableauComparatif tbody tr[data-couleur]').forEach(function (row) {
            row.style.display = (couleur === 'all' || row.dataset.couleur === couleur) ? '' : 'none';
        });
        document.querySelectorAll('.filter-btn[data-filter]').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.filter === couleur);
        });
    }

    function trierTableau(cle) {
        const tbody = document.querySelector('#tableauComparatif tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr[data-couleur]'));
        rows.sort(function (a, b) {
            if (cle === 'nom') return a.dataset.nom.localeCompare(b.dataset.nom);
            if (cle === 'moyenne-desc') return parseFloat(b.dataset.moyenne) - parseFloat(a.dataset.moyenne);
            if (cle === 'moyenne-asc') return parseFloat(a.dataset.moyenne) - parseFloat(b.dataset.moyenne);
            return 0;
        });
        rows.forEach(function (row) { tbody.appendChild(row); });
        document.querySelectorAll('.filter-btn[data-sort]').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.sort === cle);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Évolution des évaluations
        const ctx = document.getElementById('evaluationEvolChart');
        if (ctx) {
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
                        tooltip: { callbacks: { label: context => 'Moyenne : ' + context.parsed.y + '/20' } }
                    },
                    scales: {
                        y: { min: 0, max: 20, ticks: { stepSize: 4 } },
                        x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 0 } }
                    }
                }
            });
        }

        // Statut du jour (donut)
        const ctxStatut = document.getElementById('supStatutChart');
        if (ctxStatut) {
            new Chart(ctxStatut, {
                type: 'doughnut',
                data: {
                    labels: ['Présents', 'Absents', 'Retards'],
                    datasets: [{
                        data: [{{ (int) ($presentToday ?? 0) }}, {{ (int) ($absentToday ?? 0) }}, {{ (int) ($lateToday ?? 0) }}],
                        backgroundColor: ['#2E8B57', '#D64545', '#D97706'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false } } }
            });
        }

        // Notes par membre (barres horizontales)
        const ctxNotes = document.getElementById('notesMembresChart');
        if (ctxNotes) {
            const membres = @json($membresChart ?? []);
            const couleurs = membres.map(m => m.moyenne >= 14 ? '#2E8B57' : (m.moyenne >= 10 ? '#D97706' : '#D64545'));

            new Chart(ctxNotes, {
                type: 'bar',
                data: {
                    labels: membres.map(m => m.nom),
                    datasets: [{
                        label: 'Moyenne /20',
                        data: membres.map(m => m.moyenne),
                        backgroundColor: couleurs,
                        borderRadius: 6,
                        maxBarThickness: 18
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ctx.parsed.x + '/20' } }
                    },
                    scales: {
                        x: { min: 0, max: 20, ticks: { stepSize: 4 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        y: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endpush
