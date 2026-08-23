@extends('layouts.app')

@section('header', 'Rapport de Présence & Rendement')

@section('navigation')
<a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('admin.generateReport') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Rapport</a>
<a href="{{ route('admin.showEmployeeList') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Employés</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('admin.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('admin.generateReport') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white" aria-current="page">Rapport</a>
@endsection

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

<!-- En-tête période -->
<div class="flex flex-wrap items-center justify-between mb-6 gap-4">
    <div>
        <p class="text-sm text-gray-500">Période</p>
        <p class="text-lg font-bold text-[#080808]">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>
    <div class="flex gap-2">
        <form action="{{ route('admin.evaluations.export') }}" method="GET">
            <input type="hidden" name="mois" value="{{ substr($startDate, 0, 7) }}">
            <button type="submit" class="btn-gold btn-press inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Export Évaluations
            </button>
        </form>
        <form action="{{ route('admin.exportReport') }}" method="POST">
            @csrf
            <input type="hidden" name="start_date" value="{{ $startDate }}">
            <input type="hidden" name="end_date" value="{{ $endDate }}">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border-2 border-[#080808] bg-[#080808] px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export PDF
            </button>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-2 gap-4 mb-6 sm:grid-cols-5">
    <div class="stat-card border border-white/20 bg-gradient-to-br from-white/90 to-white/70">
        <div class="stat-card-value">{{ $totalEmployes }}</div>
        <div class="stat-card-label">Employés actifs</div>
    </div>
    <div class="stat-card border border-white/20 bg-gradient-to-br from-white/90 to-white/70">
        <div class="stat-card-icon bg-pharaoh-gold/10 text-pharaoh-gold">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
        </div>
        <div class="stat-card-value">{{ $noteMoyenne }}/20</div>
        <div class="stat-card-label">Note moyenne</div>
    </div>
    <div class="stat-card border border-white/20 bg-gradient-to-br from-white/90 to-white/70">
        <div class="stat-card-value">{{ $totalPresences }}</div>
        <div class="stat-card-label">Jours de présence</div>
    </div>
    <div class="stat-card border border-emerald-200/30 bg-gradient-to-br from-emerald-50/90 to-emerald-50/70">
        <div class="stat-card-value text-emerald-600">{{ $verts }}</div>
        <div class="stat-card-label flex items-center gap-1.5">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            <span>Évaluations vertes</span>
        </div>
    </div>
    <div class="stat-card border border-red-200/30 bg-gradient-to-br from-red-50/90 to-red-50/70">
        <div class="stat-card-value text-red-600">{{ $rouges }}</div>
        <div class="stat-card-label flex items-center gap-1.5">
            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
            <span>Évaluations rouges</span>
        </div>
    </div>
</div>

<!-- Graphiques -->
@if(count($reportData) > 0)
<div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
    <!-- Graphique évolution évaluations -->
    <div class="card">
        <h2 class="text-lg font-bold text-[#080808] mb-1 flex items-center gap-2">
            <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            <span>Évolution des évaluations (6 mois)</span>
        </h2>
        <p class="text-sm text-gray-500 mb-4 ml-7">Comparaison de la note /20 par employé</p>
        <canvas id="companyEvaluationChart" height="120"></canvas>
        <p class="mt-3 text-xs text-gray-400 flex items-center flex-wrap gap-3">
            <span>Échelle 0-20 :</span>
            <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-green-500"></span> Vert ≥ 14</span>
            <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-yellow-500"></span> Orange 10-13</span>
            <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-red-500"></span> Rouge &lt; 10</span>
        </p>
    </div>

    <!-- Graphique répartition évaluations -->
    <div class="card">
        <h2 class="text-lg font-bold text-[#080808] mb-1 flex items-center gap-2">
            <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm0 0V12m0 0L8 8" />
            </svg>
            <span>Répartition des évaluations</span>
        </h2>
        <p class="text-sm text-gray-500 mb-4 ml-7">Répartition verte / orange / rouge</p>
        <canvas id="evaluationPieChart" height="160"></canvas>
    </div>
</div>
@endif

<!-- Tableau détaillé -->
<div class="card">
    <h2 class="text-lg font-bold text-[#080808] mb-4 flex items-center gap-2">
        <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <span>Détails par employé</span>
    </h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#080808] text-white">
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
                                    <input type="number" name="note" min="0" max="20" step="0.5" value="{{ $data->evaluation_note }}" class="w-16 rounded-lg border border-gray-300 px-2 py-1 text-xs focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold/20" title="Note /20">
                                    <select name="couleur" class="rounded-lg border border-gray-300 px-1 py-1 text-xs focus:border-pharaoh-gold">
                                        <option value="vert" {{ $data->evaluation_couleur === 'vert' ? 'selected' : '' }}>Vert</option>
                                        <option value="orange" {{ $data->evaluation_couleur === 'orange' ? 'selected' : '' }}>Orange</option>
                                        <option value="rouge" {{ $data->evaluation_couleur === 'rouge' ? 'selected' : '' }}>Rouge</option>
                                    </select>
                                </div>
                                <input type="text" name="commentaire" placeholder="Commentaire..." class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs mb-1 focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold/20">
                                <button type="submit" class="rounded-lg bg-pharaoh-gold px-3 py-1 text-xs font-semibold text-white hover:bg-pharaoh-gold-light transition-colors">Enregistrer</button>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Graphique évolution
    const ctxLine = document.getElementById('companyEvaluationChart');
    if (ctxLine) {
        const labels = @json($chartLabels);
        const datasets = @json($chartDatasets).map(d => ({
            label: d.label,
            data: d.data,
            borderColor: d.color,
            backgroundColor: d.color + '33',
            borderWidth: 2.5,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 7,
            pointBackgroundColor: d.color,
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            fill: false,
        }));

        new Chart(ctxLine, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, padding: 16, font: { size: 11 } } },
                    tooltip: {
                        backgroundColor: '#080808',
                        titleFont: { size: 13 },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ctx.dataset.label + ' : ' + ctx.parsed.y + '/20'
                        }
                    }
                },
                scales: {
                    y: { min: 0, max: 20, ticks: { stepSize: 4, font: { size: 11 } }, title: { display: true, text: 'Note /20', font: { size: 12 } } },
                    x: { ticks: { maxRotation: 45, font: { size: 11 } } }
                }
            }
        });
    }

    // Graphique pie/doughnut
    const ctxPie = document.getElementById('evaluationPieChart');
    if (ctxPie) {
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Vert (≥14)', 'Orange (10-13)', 'Rouge (<10)'],
                datasets: [{
                    data: [{{ $verts }}, {{ $oranges }}, {{ $rouges }}],
                    backgroundColor: ['#2E8B57', '#D97706', '#D64545'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, font: { size: 12 } } },
                    tooltip: {
                        backgroundColor: '#080808',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ctx.label + ' : ' + ctx.parsed + ' employé(s)'
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
