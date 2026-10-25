@extends('layouts.dashboard')

@section('title', 'Rapport d\'équipe')
@section('breadcrumb', 'Rapport d\'équipe')

@section('header')
    <div>
        <span>Rapport d'équipe</span>
        <p class="console-subtitle">Présence et rendement de votre équipe (mois en cours).</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('export.pdf') }}" class="btn-gold">
            <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exporter en PDF
        </a>
        <a href="{{ route('superviseur.evaluations.export.pdf', ['mois' => now()->format('Y-m')]) }}" class="btn-secondary">
            <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            PDF Évaluations
        </a>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
@php
    $couleurBadge = [
        'vert' => 'badge-success',
        'orange' => 'badge-warning',
        'rouge' => 'badge-danger',
    ];

    // Couleurs stables pour chaque membre du graphique de comparaison
    $palette = ['#D39B23', '#2E8B57', '#D97706', '#9333ea', '#D64545', '#0891b2', '#ca8a04', '#db2777', '#3B82C4', '#65a30d'];
    $chartLabels = $reports[0]['historique'] ?? [];
    $chartLabels = array_map(fn ($h) => $h['label'], $chartLabels);
    $chartDatasets = [];
    foreach ($reports as $idx => $report) {
        $chartDatasets[] = [
            'label' => $report['name'],
            'data' => array_map(fn ($h) => (float) $h['note'], $report['historique']),
            'color' => $palette[$idx % count($palette)],
        ];
    }

    $reportsEquipe = collect($reports);
    $totalPresencesEquipe = $reportsEquipe->sum(fn ($r) => (int) ($r['totalPresences'] ?? 0));
    $notesEquipe = $reportsEquipe->pluck('evaluation_note')->filter(fn ($n) => $n !== null);
    $moyenneEquipe = $notesEquipe->count() ? round((float) $notesEquipe->avg(), 1) : 0;
    $toneMoyenne = $moyenneEquipe >= 14 ? 'success' : ($moyenneEquipe >= 10 ? 'warning' : 'danger');
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        @include('partials.dashboard.kpi', ['label' => 'Membres suivis', 'value' => $reportsEquipe->count(), 'meta' => 'Dans votre équipe', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'Présences du mois', 'value' => $totalPresencesEquipe, 'meta' => 'Cumul de l\'équipe', 'tone' => 'default'])
        @include('partials.dashboard.kpi', ['label' => 'Note moyenne', 'value' => $moyenneEquipe . '/20', 'meta' => 'Seuils : vert ≥ 14 · orange 10-13 · rouge < 10', 'tone' => $toneMoyenne])
    </div>

    @if(count($reports) > 0)
    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Évolution des évaluations de l'équipe (6 mois)</h2>
                <p class="console-card-sub">Comparaison de la note /20 de chaque membre, mois par mois.</p>
            </div>
            <span class="badge badge-gold">0 → 20</span>
        </div>
        <div class="console-card-body">
            <div class="relative h-64 sm:h-72">
                <canvas id="teamEvaluationChart" role="img" aria-label="Évolution des évaluations de l'équipe sur six mois"></canvas>
            </div>
            <p class="mt-3 text-xs text-gray-500">Échelle de 0 à 20. 🟢 Vert ≥ 14 · 🟠 Orange 10-13 · 🔴 Rouge &lt; 10</p>
        </div>
    </div>
    @endif

    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Détail par employé</h2>
                <p class="console-card-sub">Présences, heures, évaluation et réalisations du mois en cours.</p>
            </div>
        </div>

        <div class="table-scroll">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-head">
                    <tr>
                        <th scope="col">Nom de l'employé</th>
                        <th scope="col">Total de Présences (Mois en cours)</th>
                        <th scope="col">Total Heures</th>
                        <th scope="col">Évaluation</th>
                        <th scope="col">Réalisations (rendement)</th>
                    </tr>
                </thead>
                <tbody class="table-body divide-y divide-gray-200 bg-white">
                    @forelse($reports as $report)
                    <tr class="align-top">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $report['name'] }}</div>
                            @if(isset($report['employerID']) && $report['employerID'])
                            <div class="mt-1">
                                <a href="{{ route('superviseur.evaluation.bulletin', ['id' => $report['employerID'], 'mois' => now()->format('Y-m')]) }}" class="inline-flex items-center gap-1 text-xs font-medium text-pharaoh-gold hover:text-pharaoh-gold-light" title="Télécharger le bulletin individuel du mois">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Bulletin PDF
                                </a>
                            </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span class="badge badge-gold">{{ $report['totalPresences'] }}</span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm font-semibold text-[#885910]">{{ $report['totalHeures'] ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge {{ $couleurBadge[$report['evaluation_couleur']] ?? $couleurBadge['orange'] }}">
                                {{ $report['evaluation_note'] }}/20
                            </span>
                            @if($report['evaluation_manuelle'])
                                <span class="ml-1 text-xs text-gray-500">(manuelle)</span>
                            @endif
                            <div class="mt-1 max-w-xs text-xs text-gray-600">{{ $report['evaluation_commentaire'] }}</div>

                            <!-- Formulaire d'évaluation manuelle (directeur / directeur adjoint) -->
                            <form action="{{ route('superviseur.storeEvaluation') }}" method="POST" class="mt-2 space-y-1">
                                @csrf
                                <input type="hidden" name="employerID" value="{{ $report['employerID'] ?? '' }}">
                                <input type="hidden" name="mois" value="{{ now()->format('Y-m') }}">
                                <div class="flex flex-wrap items-center gap-1">
                                    <input type="number" name="note" min="0" max="20" step="0.5" value="{{ $report['evaluation_note'] }}" aria-label="Note sur 20" class="w-16 rounded-lg border border-gray-300 px-2 py-1 text-xs focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold">
                                    <select name="couleur" aria-label="Couleur de l'évaluation" class="rounded-lg border border-gray-300 px-1 py-1 text-xs focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold">
                                        <option value="vert" {{ $report['evaluation_couleur'] === 'vert' ? 'selected' : '' }}>🟢 Vert</option>
                                        <option value="orange" {{ $report['evaluation_couleur'] === 'orange' ? 'selected' : '' }}>🟠 Orange</option>
                                        <option value="rouge" {{ $report['evaluation_couleur'] === 'rouge' ? 'selected' : '' }}>🔴 Rouge</option>
                                    </select>
                                </div>
                                <input type="text" name="commentaire" placeholder="Commentaire (optionnel)" aria-label="Commentaire d'évaluation" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold">
                                <button type="submit" class="btn-gold px-2 py-1 text-xs">Enregistrer l'évaluation</button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            @if(count($report['rendements']) > 0)
                                <ul class="list-inside list-disc space-y-1 text-sm text-gray-700">
                                    @foreach($report['rendements'] as $rendement)
                                        <li>{{ \Illuminate\Support\Str::limit($rendement, 180) }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-sm text-gray-400">Aucune fiche de rendement remplie.</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-sm font-semibold text-gray-900">Aucun rapport disponible</h3>
                                <p class="mt-1 text-sm text-gray-500">Aucune donnée de présence pour le mois en cours.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('teamEvaluationChart');
        if (!ctx) {
            return;
        }
        const labels = @json($chartLabels);
        const datasets = @json($chartDatasets).map(d => ({
            label: d.label,
            data: d.data,
            borderColor: d.color,
            backgroundColor: d.color,
            borderWidth: 2,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: d.color,
            pointBorderColor: '#ffffff',
            pointBorderWidth: 1.5
        }));

        new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ' : ' + context.parsed.y + '/20';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 20,
                        ticks: { stepSize: 4 },
                        title: { display: true, text: 'Note sur 20' }
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
