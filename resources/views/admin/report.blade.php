@extends('layouts.dashboard')

@section('title', 'Rapport de Présence')
@section('breadcrumb', 'Rapport de présence')

@section('header')
    <div>
        <span>Rapport de Présence & Rendement</span>
        <p class="console-subtitle">Période du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <form action="{{ route('admin.evaluations.export') }}" method="GET">
            <input type="hidden" name="mois" value="{{ substr($startDate, 0, 7) }}">
            <button type="submit" class="btn-gold btn-sm gap-1.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Export Évaluations
            </button>
        </form>
        <a href="{{ route('admin.evaluations.export.pdf', ['mois' => substr($startDate, 0, 7)]) }}"
           class="btn-secondary btn-sm gap-1.5">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            PDF Évaluations
        </a>
        <form action="{{ route('admin.exportReport') }}" method="POST">
            @csrf
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            <button type="submit" class="btn-dark btn-sm gap-1.5">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export PDF
            </button>
        </form>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
@php
    $couleurBadge = [
        'vert' => 'bg-emerald-100 text-emerald-800',
        'orange' => 'bg-amber-100 text-amber-800',
        'rouge' => 'bg-red-100 text-red-800',
    ];

    $palette = ['#D39B23', '#115293', '#2E8B57', '#D97706', '#D64545', '#0891b2', '#9333ea', '#dc2626', '#4f46e5', '#65a30d'];
    $chartLabels = isset($reportData[0]) && !empty($reportData[0]->historique)
        ? array_map(fn ($h) => $h['label'], $reportData[0]->historique)
        : [];
    $chartDatasets = [];
    foreach ($reportData as $idx => $data) {
        $chartDatasets[] = [
            'label' => $data->employer_nom,
            'data' => array_map(fn ($h) => (float) $h['note'], $data->historique),
            'color' => $palette[$idx % count($palette)],
        ];
    }

    // Stats globales
    $totalEmployes = $reportData->count();
    $noteMoyenne = $reportData->count() > 0 ? round($reportData->avg('evaluation_note'), 1) : 0;
    $totalPresences = $reportData->sum('total_presence');
    $verts = $reportData->filter(fn($d) => $d->evaluation_couleur === 'vert')->count();
    $oranges = $reportData->filter(fn($d) => $d->evaluation_couleur === 'orange')->count();
    $rouges = $reportData->filter(fn($d) => $d->evaluation_couleur === 'rouge')->count();
@endphp

<div class="space-y-5">
<!-- KPI Cards -->
<div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
    @include('partials.dashboard.kpi', ['label' => 'Employés actifs', 'value' => $totalEmployes, 'meta' => 'Période sélectionnée', 'tone' => 'gold'])
    @include('partials.dashboard.kpi', ['label' => 'Note moyenne', 'value' => $noteMoyenne . '/20', 'meta' => 'Moyenne équipe', 'tone' => $noteMoyenne >= 14 ? 'success' : ($noteMoyenne >= 10 ? 'warning' : 'danger')])
    @include('partials.dashboard.kpi', ['label' => 'Jours de présence', 'value' => $totalPresences, 'meta' => 'Cumul équipe'])
    @include('partials.dashboard.kpi', ['label' => 'Évaluations vertes', 'value' => $verts, 'meta' => 'Note ≥ 14', 'tone' => 'success'])
    @include('partials.dashboard.kpi', ['label' => 'Évaluations rouges', 'value' => $rouges, 'meta' => 'Note < 10', 'tone' => 'danger'])
</div>

<!-- Graphiques -->
@if(count($reportData) > 0)
<div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
    <!-- Graphique évolution évaluations -->
    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Évolution des évaluations (6 mois)</h2>
                <p class="console-card-sub">Comparaison de la note /20 par employé.</p>
            </div>
        </div>
        <div class="console-card-body">
            <div class="relative h-64 sm:h-72">
                <canvas id="companyEvaluationChart" role="img" aria-label="Évolution des évaluations sur six mois"></canvas>
            </div>
            <p class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-400">
                <span>Échelle 0-20 :</span>
                <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-green-500"></span> Vert ≥ 14</span>
                <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-yellow-500"></span> Orange 10-13</span>
                <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-red-500"></span> Rouge &lt; 10</span>
            </p>
        </div>
    </div>

    <!-- Graphique répartition évaluations -->
    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Répartition des évaluations</h2>
                <p class="console-card-sub">Répartition verte / orange / rouge.</p>
            </div>
        </div>
        <div class="console-card-body">
            <div class="relative h-64 sm:h-72">
                <canvas id="evaluationPieChart" role="img" aria-label="Répartition des évaluations"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Tableau détaillé -->
<div class="console-card">
    <div class="console-card-head">
        <div>
            <h2 class="console-card-title">Détails par employé</h2>
            <p class="console-card-sub">Présences, évaluations et fiches de rendement de la période.</p>
        </div>
    </div>
    <div class="console-card-body">
        <div class="table-scroll overflow-x-auto">
            <table class="w-full min-w-[52rem] text-sm">
                <thead class="table-head">
                    <tr>
                    <th class="px-4 py-3 text-left font-semibold">Employé</th>
                    <th class="px-4 py-3 text-center font-semibold">Présences</th>
                    <th class="px-4 py-3 text-center font-semibold">Heures</th>
                    <th class="px-4 py-3 text-center font-semibold">Évaluation</th>
                    <th class="px-4 py-3 text-left font-semibold">Rendements</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($reportData as $data)
                <tr class="hover:bg-pharaoh-gold/5 transition-colors">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-900">{{ $data->employer_nom }}</div>
                        <a href="{{ route('admin.evaluation.bulletin', ['id' => $data->employerID, 'mois' => substr($startDate, 0, 7)]) }}" class="text-xs text-pharaoh-gold hover:underline inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Bulletin PDF</span>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-[#080808]">{{ $data->total_presence }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $data->total_heures ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-bold {{ $couleurBadge[$data->evaluation_couleur] ?? $couleurBadge['orange'] }}">
                                {{ $data->evaluation_note }}/20
                            </span>
                            @if($data->evaluation_manuelle)
                                <span class="text-xs text-gray-400">(manuelle)</span>
                            @endif
                            <!-- Formulaire évaluation manuelle -->
                            <form action="{{ route('admin.storeEvaluation') }}" method="POST" class="w-full mt-1">
                                @csrf
                                <input type="hidden" name="employerID" value="{{ $data->employerID }}">
                                <input type="hidden" name="mois" value="{{ substr($startDate, 0, 7) }}">
                                <div class="flex items-center gap-1 mb-1">
                                    <input type="number" name="note" min="0" max="20" step="0.5" value="{{ $data->evaluation_note }}" class="input-field-sm w-16" title="Note /20">
                                    <select name="couleur" class="select-field-sm">
                                        <option value="vert" {{ $data->evaluation_couleur === 'vert' ? 'selected' : '' }}>Vert</option>
                                        <option value="orange" {{ $data->evaluation_couleur === 'orange' ? 'selected' : '' }}>Orange</option>
                                        <option value="rouge" {{ $data->evaluation_couleur === 'rouge' ? 'selected' : '' }}>Rouge</option>
                                    </select>
                                </div>
                                <input type="text" name="commentaire" placeholder="Commentaire..." class="input-field-sm mb-1 w-full">
                                <button type="submit" class="btn-gold btn-sm">Enregistrer</button>
                            </form>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if(count($data->rendements) > 0)
                            <ul class="space-y-1">
                                @foreach($data->rendements as $i => $rendement)
                                    @if($i < 3)
                                    <li class="flex items-start gap-1 text-xs text-gray-600">
                                        <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-pharaoh-gold flex-shrink-0"></span>
                                        {{ \Illuminate\Support\Str::limit($rendement, 120) }}
                                    </li>
                                    @endif
                                @endforeach
                                @if(count($data->rendements) > 3)
                                    <li class="text-xs text-pharaoh-gold font-medium">+{{ count($data->rendements) - 3 }} autres...</li>
                                @endif
                            </ul>
                        @else
                            <span class="text-xs text-gray-400 italic">Aucune fiche remplie</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="h-10 w-10 text-gray-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0L12 17l-8-4" />
                            </svg>
                            <span class="text-sm">Aucune donnée pour cette période.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script src="{{ asset('js/pharaoh-charts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Graphique évolution
    const datasets = @json($chartDatasets).map(d => ({
        label: d.label,
        data: d.data,
        borderColor: d.color,
        backgroundColor: d.color + '33',
        fill: false,
    }));

    PharaohCharts.line(document.getElementById('companyEvaluationChart'), {
        labels: @json($chartLabels),
        datasets: datasets
    }, {
        plugins: {
            tooltip: { callbacks: { label: ctx => ctx.dataset.label + ' : ' + ctx.parsed.y + '/20' } }
        },
        scales: {
            y: {
                min: 0,
                max: 20,
                ticks: { stepSize: 4 },
                title: { display: true, text: 'Note /20', color: PharaohCharts.colors().text }
            }
        }
    });

    // Graphique pie/doughnut
    PharaohCharts.doughnut(document.getElementById('evaluationPieChart'), {
        labels: ['Vert (≥14)', 'Orange (10-13)', 'Rouge (<10)'],
        datasets: [{
            data: [{{ $verts }}, {{ $oranges }}, {{ $rouges }}],
            backgroundColor: ['success', 'warning', 'danger']
        }]
    }, {
        cutout: '60%',
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: ctx => ctx.label + ' : ' + ctx.parsed + ' employé(s)' } }
        }
    });
});
</script>
@endpush
