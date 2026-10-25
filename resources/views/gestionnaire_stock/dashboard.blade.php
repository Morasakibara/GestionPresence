@extends('layouts.dashboard')

@section('content_shell', 'flush')

@section('breadcrumb', 'Gestion du stock')

@section('header')
<div class="flex w-full flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="console-title">Tableau de bord</h1>
        <p class="console-subtitle">Stock de T-shirts et papier d'impression — {{ now()->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('gestionnaire.tshirts') }}" class="btn-gold inline-flex items-center gap-2 text-sm">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
        </svg>
        Mettre à jour le stock
    </a>
</div>
@endsection

@section('tabs')
<a href="#stock-overview" class="console-tab is-active" aria-current="page">Vue d'ensemble</a>
<a href="#stock-tshirts" class="console-tab">T-Shirts</a>
<a href="#stock-papier" class="console-tab">Papier</a>
<a href="#stock-alertes" class="console-tab">Alertes</a>
@endsection

@section('content')
@php
    $totalAlertesTshirts = $tshirtsEnAlerte->count();
    $totalAlertesPapier = $papiersEnAlerte->count();
    $totalAlertes = $totalAlertesTshirts + $totalAlertesPapier;
    $totalMetres = (float) $papiers->sum('metres_restants');
    $totalReferences = $tshirts->count() + $papiers->count();
    $totalImprimantes = $papiers->count();

    // Graphique T-shirts : références triées par quantité croissante.
    $chartTshirts = $tshirts
        ->map(fn ($t) => [
            'label' => trim($t->couleur . ' · ' . $t->taille),
            'quantite' => (int) $t->quantite,
            'alerte' => $t->enAlerte(),
        ])
        ->sortBy('quantite')
        ->values();
@endphp

<div class="space-y-5">
    <!-- ═══ Indicateurs clés ═══ -->
    <div id="stock-overview" class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-6">
        @include('partials.dashboard.kpi', ['label' => 'Total T-Shirts', 'value' => number_format($totalTshirts, 0, ',', ' '), 'meta' => 'Pièces en stock', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'Références', 'value' => $tshirts->count(), 'meta' => 'Couleurs et tailles'])
        @include('partials.dashboard.kpi', ['label' => 'Imprimantes', 'value' => $totalImprimantes, 'meta' => 'Bacs papier suivis'])
        @include('partials.dashboard.kpi', ['label' => 'Papier restant', 'value' => number_format($totalMetres, 1, ',', ' '), 'meta' => 'Mètres disponibles', 'tone' => 'info'])
        @include('partials.dashboard.kpi', ['label' => 'Alertes T-shirts', 'value' => $totalAlertesTshirts, 'meta' => 'Seuils atteints', 'tone' => 'danger'])
        @include('partials.dashboard.kpi', ['label' => 'Alertes papier', 'value' => $totalAlertesPapier, 'meta' => 'Rouleaux faibles', 'tone' => 'warning'])
    </div>

    <!-- ═══ Stock T-shirts + répartition des alertes ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="stock-tshirts" class="console-card lg:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Stock de T-shirts par référence</h2>
                    <p class="console-card-sub">Quantités disponibles, seuils d'alerte en rouge</p>
                </div>
                <span class="badge badge-gold">Pièces</span>
            </div>
            <div class="console-card-body">
                @if($chartTshirts->isNotEmpty())
                    <div class="h-56"><canvas id="stockTshirtsChart"></canvas></div>
                @else
                    <div class="console-empty">
                        <p class="text-sm text-gray-400">Aucune référence T-shirt enregistrée.</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Répartition des alertes</h2>
                    <p class="console-card-sub">Références sous le seuil</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="relative h-40">
                    <canvas id="stockAlertesChart"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-extrabold text-[#080808]">{{ $totalAlertes }}</span>
                        <span class="text-[0.625rem] uppercase tracking-wide text-gray-400">Alertes</span>
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <div class="console-panel-row">
                        <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-red-500"></span>T-shirts</span>
                        <span class="text-sm font-semibold text-[#080808]">{{ $totalAlertesTshirts }}</span>
                    </div>
                    <div class="console-panel-row">
                        <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-amber-500"></span>Papier</span>
                        <span class="text-sm font-semibold text-[#080808]">{{ $totalAlertesPapier }}</span>
                    </div>
                    <div class="console-panel-row">
                        <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-green-600"></span>Références suivies</span>
                        <span class="text-sm font-semibold text-[#080808]">{{ $totalReferences }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- ═══ Niveau papier + alertes + accès rapides ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="stock-papier" class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Niveau de papier</h2>
                    <p class="console-card-sub">Reste par rapport au stock initial</p>
                </div>
                <a href="{{ route('gestionnaire.papier') }}" class="text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">Gérer →</a>
            </div>
            <div class="console-card-body">
                @forelse($papiers as $p)
                    @php
                        $pourcentage = min(100, max(0, $p->pourcentageRestant()));
                        $barre = $p->enAlerte() ? 'bg-red-500' : ($pourcentage < 35 ? 'bg-amber-500' : 'bg-pharaoh-gold');
                    @endphp
                    <div class="py-2">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <span class="truncate text-sm font-medium text-[#080808]">{{ $p->imprimante }}</span>
                            <span class="whitespace-nowrap text-xs text-gray-500">
                                {{ number_format($p->metres_restants, 1) }} / {{ number_format($p->metres_total, 1) }} m
                            </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full {{ $barre }}" style="width: {{ $pourcentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Aucun stock de papier enregistré</p>
                @endforelse
            </div>
        </section>

        <section id="stock-alertes" class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Alertes en cours</h2>
                    <p class="console-card-sub">{{ $totalAlertes }} référence(s) à réapprovisionner</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="console-scroll">
                    @foreach($tshirtsEnAlerte as $t)
                        <div class="console-panel-row">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="console-dot bg-red-500"></span>
                                <span class="truncate text-sm text-gray-700">T-Shirt {{ $t->couleur }} {{ $t->taille }}</span>
                            </span>
                            <span class="whitespace-nowrap text-xs font-semibold text-red-600">{{ $t->quantite }} / seuil {{ $t->seuil_alerte }}</span>
                        </div>
                    @endforeach
                    @foreach($papiersEnAlerte as $p)
                        <div class="console-panel-row">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="console-dot bg-amber-500"></span>
                                <span class="truncate text-sm text-gray-700">Papier {{ $p->imprimante }}</span>
                            </span>
                            <span class="whitespace-nowrap text-xs font-semibold text-orange-600">{{ number_format($p->metres_restants, 1) }} m / seuil {{ $p->seuil_alerte }} m</span>
                        </div>
                    @endforeach
                    @if($totalAlertes === 0)
                        <p class="py-6 text-center text-sm text-gray-400">Aucune alerte — tous les stocks sont au-dessus du seuil</p>
                    @endif
                </div>
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Accès rapides</h2>
                    <p class="console-card-sub">Gestes du quotidien</p>
                </div>
            </div>
            <div class="console-card-body space-y-2">
                <a href="{{ route('gestionnaire.tshirts') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Gérer les T-Shirts</span>
                        <span class="block text-xs text-gray-500">Ajouts, tailles et seuils</span>
                    </span>
                </a>
                <a href="{{ route('gestionnaire.papier') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5h9M6 8h12M5 11.5h14v6a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 15.5h6" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Gérer le Papier</span>
                        <span class="block text-xs text-gray-500">Rouleaux par imprimante</span>
                    </span>
                </a>
                <a href="{{ route('notifications.index') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Notifications</span>
                        <span class="block text-xs text-gray-500">Alertes de seuil et rappels</span>
                    </span>
                </a>
            </div>
        </section>
    </div>

    <!-- ═══ Détail des stocks ═══ -->
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">T-Shirts en stock</h2>
                    <p class="console-card-sub">{{ $tshirts->count() }} référence(s) · {{ number_format($totalTshirts, 0, ',', ' ') }} pièce(s)</p>
                </div>
                <a href="{{ route('gestionnaire.tshirts') }}" class="text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">Voir tout →</a>
            </div>
            <div class="console-card-body">
                @if($tshirts->isEmpty())
                    <p class="py-6 text-center text-sm text-gray-400">Aucun T-shirt en stock</p>
                @else
                    <div class="table-scroll overflow-hidden rounded-xl border border-gray-200/70">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="table-head">
                                <tr>
                                    <th>Couleur</th>
                                    <th>Taille</th>
                                    <th class="text-right">Quantité</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody class="table-body">
                                @foreach($tshirts->take(10) as $t)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-[#080808]">{{ $t->couleur }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $t->taille }}</td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold text-[#080808]">{{ $t->quantite }}</td>
                                        <td class="px-4 py-3">
                                            <span class="badge {{ $t->enAlerte() ? 'badge-danger' : 'badge-success' }}">{{ $t->enAlerte() ? 'Alerte' : 'OK' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Papier d'impression</h2>
                    <p class="console-card-sub">{{ $totalImprimantes }} imprimante(s) · {{ number_format($totalMetres, 1, ',', ' ') }} m disponibles</p>
                </div>
                <a href="{{ route('gestionnaire.papier') }}" class="text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">Voir tout →</a>
            </div>
            <div class="console-card-body">
                @if($papiers->isEmpty())
                    <p class="py-6 text-center text-sm text-gray-400">Aucun stock de papier enregistré</p>
                @else
                    <div class="table-scroll overflow-hidden rounded-xl border border-gray-200/70">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="table-head">
                                <tr>
                                    <th>Imprimante</th>
                                    <th class="text-right">Reste (m)</th>
                                    <th class="text-right">Total (m)</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody class="table-body">
                                @foreach($papiers as $p)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-[#080808]">{{ $p->imprimante }}</td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold text-[#080808]">{{ number_format($p->metres_restants, 1) }}</td>
                                        <td class="px-4 py-3 text-right text-sm text-gray-500">{{ number_format($p->metres_total, 1) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="badge {{ $p->enAlerte() ? 'badge-danger' : 'badge-success' }}">{{ $p->enAlerte() ? 'Alerte' : 'OK' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Stock de T-shirts par référence (barres horizontales)
        const ctxTshirts = document.getElementById('stockTshirtsChart');
        if (ctxTshirts) {
            const refs = @json($chartTshirts);
            new Chart(ctxTshirts, {
                type: 'bar',
                data: {
                    labels: refs.map(r => r.label),
                    datasets: [{
                        label: 'Pièces en stock',
                        data: refs.map(r => r.quantite),
                        backgroundColor: refs.map(r => r.alerte ? '#D64545' : '#D39B23'),
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
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.parsed.x + ' pièce(s)' + (refs[ctx.dataIndex].alerte ? ' — sous le seuil' : '')
                            }
                        }
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                    }
                }
            });
        }

        // Répartition des alertes (donut)
        const ctxAlertes = document.getElementById('stockAlertesChart');
        if (ctxAlertes) {
            const alertesTshirts = {{ $totalAlertesTshirts }};
            const alertesPapier = {{ $totalAlertesPapier }};
            const total = alertesTshirts + alertesPapier;

            new Chart(ctxAlertes, {
                type: 'doughnut',
                data: {
                    labels: ['T-shirts', 'Papier'],
                    datasets: [{
                        data: total > 0 ? [alertesTshirts, alertesPapier] : [1, 0],
                        backgroundColor: total > 0 ? ['#D64545', '#D97706'] : ['#E5E7EB', '#F3F4F6'],
                        borderWidth: 0,
                        hoverOffset: total > 0 ? 6 : 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: { legend: { display: false }, tooltip: { enabled: total > 0 } }
                }
            });
        }
    });
</script>
@endpush
