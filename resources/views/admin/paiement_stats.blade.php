@extends('layouts.dashboard')

@section('header')
<div>
    <span>Statistiques des paiements</span>
    <p class="console-subtitle">Montants encaissés, partiels et restants, par poste de facturation.</p>
</div>
@endsection

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">

    {{-- ══════════ CARTES RÉSUMÉ ══════════ --}}
    <div class="mb-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        @include('partials.dashboard.kpi', ['label' => 'Payé', 'value' => number_format($stats['paye']['montant'], 0, ',', ' '), 'meta' => $stats['paye']['count'] . ' commande(s) — FCFA', 'tone' => 'success'])
        @include('partials.dashboard.kpi', ['label' => 'Partiel', 'value' => number_format($stats['partiel']['montant'], 0, ',', ' '), 'meta' => $stats['partiel']['count'] . ' commande(s) — reste ' . number_format($stats['partiel']['montant'] - $stats['partiel']['paye'], 0, ',', ' ') . ' FCFA', 'tone' => 'warning'])
        @include('partials.dashboard.kpi', ['label' => 'À payer', 'value' => number_format($stats['a_payer']['montant'], 0, ',', ' '), 'meta' => $stats['a_payer']['count'] . ' commande(s) en attente', 'tone' => 'danger'])
    </div>

    {{-- ══════════ CA PAR POSTE ══════════ --}}
    @if($caParPoste)
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
        @foreach($caParPoste as $poste => $data)
        <div class="console-card">
            <div class="console-card-head">
                <h3 class="console-card-title">{{ $poste }}</h3>
            </div>
            <div class="console-card-body">
                <div class="space-y-2 text-sm">
                    <div class="console-panel-row"><span class="text-gray-600">Total commandé</span><span class="font-semibold tabular-nums text-[#080808]">{{ number_format($data['total'], 0, ',', ' ') }} FCFA</span></div>
                    <div class="console-panel-row"><span class="text-gray-600">Encaissé</span><span class="font-semibold tabular-nums text-green-700">{{ number_format($data['paye'], 0, ',', ' ') }} FCFA</span></div>
                    <div class="console-panel-row"><span class="text-gray-600">Restant</span><span class="font-semibold tabular-nums text-red-600">{{ number_format($data['restant'], 0, ',', ' ') }} FCFA</span></div>
                </div>
                {{-- Barre de progression --}}
                @php($pct = $data['total'] > 0 ? round($data['paye'] / $data['total'] * 100) : 0)
                <div class="mt-3 h-2.5 w-full rounded-full bg-gray-200">
                    <div class="h-2.5 rounded-full bg-pharaoh-gold transition-all" style="width: {{ $pct }}%"></div>
                </div>
                <p class="mt-1 text-xs text-gray-400">{{ $pct }}% encaissé</p>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ══════════ GRAPHIQUES ══════════ --}}
    <div class="mb-8 grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Doughnut: répartition par statut --}}
        <div class="console-card">
            <div class="console-card-head">
                <div>
                    <h3 class="console-card-title">Répartition des paiements</h3>
                    <p class="console-card-sub">Nombre de commandes par statut</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="mx-auto h-64 max-w-xs">
                    <canvas id="pieStatutChart" role="img" aria-label="Répartition des commandes par statut de paiement"></canvas>
                </div>
            </div>
        </div>
        {{-- Bar: évolution 30 jours --}}
        <div class="console-card">
            <div class="console-card-head">
                <div>
                    <h3 class="console-card-title">Évolution sur 30 jours</h3>
                    <p class="console-card-sub">Commandes par statut de paiement</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="h-64"><canvas id="evolutionStatutChart" role="img" aria-label="Évolution des statuts de paiement sur 30 jours"></canvas></div>
            </div>
        </div>
    </div>

    {{-- ══════════ TABLEAU TOP 10 ══════════ --}}
    <div class="console-card mb-8">
        <div class="console-card-head">
            <h3 class="console-card-title">Top 10 commandes</h3>
        </div>
        <div class="console-card-body">
            <div class="table-scroll overflow-hidden rounded-xl border border-gray-200/70">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="table-head">
                        <tr>
                            <th>Type</th>
                            <th>Date</th>
                            <th class="text-right">Montant</th>
                            <th class="text-right">Payé</th>
                            <th class="text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        @forelse($topCommandes as $cmd)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $cmd->type)) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $cmd->date ? $cmd->date->format('d/m/Y') : '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900">{{ number_format($cmd->montant, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums text-green-700">{{ number_format($cmd->montant_paye, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge {{ $cmd->statut_paiement === 'paye' ? 'badge-success' : ($cmd->statut_paiement === 'partiel' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $cmd->statut_paiement === 'paye' ? 'Payé' : ($cmd->statut_paiement === 'partiel' ? 'Partiel' : 'À payer') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-gray-400">Aucune commande enregistrée</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/pharaoh-charts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Doughnut — répartition
    PharaohCharts.doughnut(document.getElementById('pieStatutChart'), {
        labels: ['Payé', 'Partiel', 'À payer'],
        datasets: [{
            data: [{{ $stats['paye']['count'] }}, {{ $stats['partiel']['count'] }}, {{ $stats['a_payer']['count'] }}],
            backgroundColor: ['success', 'warning', 'danger']
        }]
    }, {
        plugins: { legend: { position: 'bottom' } }
    });

    // Bar — évolution 30 jours
    PharaohCharts.bar(document.getElementById('evolutionStatutChart'), {
        labels: @json($labels),
        datasets: [
            { label: 'Payé', data: @json($payes), backgroundColor: 'success' },
            { label: 'Partiel', data: @json($partiels), backgroundColor: 'warning' },
            { label: 'À payer', data: @json($aPayer), backgroundColor: 'danger' }
        ]
    }, {
        plugins: { legend: { position: 'bottom' } },
        scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
        }
    });
});
</script>
@endpush
