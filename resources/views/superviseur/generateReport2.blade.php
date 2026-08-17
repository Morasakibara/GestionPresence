@extends('layouts.app')

@section('title', 'Rapport d\'équipe')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="page-heading mb-6">
        <div>
            <h1 class="page-heading-title">Rapport d'équipe</h1>
            <p class="page-heading-sub">Consultez les statistiques de présence et le rendement de votre équipe (mois en cours)</p>
        </div>
        <a href="{{ route('export.pdf') }}" class="btn-gold">
            <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Exporter en PDF
        </a>
    </div>

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
    @endphp

    @if(count($reports) > 0)
    <div class="mt-8 rounded-2xl border border-gray-200/70 bg-white p-6 shadow-card">
        <h2 class="mb-1 text-lg font-semibold text-[#080808]">Évolution des évaluations de l'équipe (6 mois)</h2>
        <p class="mb-4 text-sm text-gray-500">Comparaison de la note /20 de chaque membre, mois par mois.</p>
        <canvas id="teamEvaluationChart" height="110"></canvas>
        <p class="mt-3 text-xs text-gray-500">Échelle de 0 à 20. 🟢 Vert ≥ 14 · 🟠 Orange 10-13 · 🔴 Rouge &lt; 10</p>
    </div>
    @endif

    <div class="mt-8 table-wrap">
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
                                <a href="{{ route('superviseur.evaluation.bulletin', ['id' => $report['employerID'], 'mois' => now()->format('Y-m')]) }}" class="inline-flex items-center gap-1 text-xs font-medium text-3hcig-blue hover:text-3hcig-blue-light" title="Télécharger le bulletin individuel du mois">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    Bulletin PDF
                                </a>
                            </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <span class="badge badge-gold">{{ $report['totalPresences'] }}</span>
                            </div>
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
                            <div class="mt-1 text-xs text-gray-600 max-w-xs">{{ $report['evaluation_commentaire'] }}</div>

                            <!-- Formulaire d'évaluation manuelle (directeur / directeur adjoint) -->
                            <form action="{{ route('superviseur.storeEvaluation') }}" method="POST" class="mt-2 space-y-1">
                                @csrf
                                <input type="hidden" name="employerID" value="{{ $report['employerID'] ?? '' }}">
                                <input type="hidden" name="mois" value="{{ now()->format('Y-m') }}">
                                <div class="flex items-center gap-1">
                                    <input type="number" name="note" min="0" max="20" step="0.5" value="{{ $report['evaluation_note'] }}" class="w-16 rounded-lg border border-gray-300 px-2 py-1 text-xs focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold" title="Note sur 20">
                                    <select name="couleur" class="rounded-lg border border-gray-300 px-1 py-1 text-xs focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold">
                                        <option value="vert" {{ $report['evaluation_couleur'] === 'vert' ? 'selected' : '' }}>🟢 Vert</option>
                                        <option value="orange" {{ $report['evaluation_couleur'] === 'orange' ? 'selected' : '' }}>🟠 Orange</option>
                                        <option value="rouge" {{ $report['evaluation_couleur'] === 'rouge' ? 'selected' : '' }}>🔴 Rouge</option>
                                    </select>
                                </div>
                                <input type="text" name="commentaire" placeholder="Commentaire (optionnel)" class="w-full rounded-lg border border-gray-300 px-2 py-1 text-xs focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold">
                                <button type="submit" class="rounded-lg bg-pharaoh-gold px-2 py-1 text-xs font-semibold text-white transition-colors duration-150 hover:bg-pharaoh-gold-light">Enregistrer l'évaluation</button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            @if(count($report['rendements']) > 0)
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
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
                                    <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
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
@endsection
