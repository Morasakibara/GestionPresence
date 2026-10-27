@extends('layouts.dashboard')

@section('header')
<div><span>Retraits</span><p class="console-subtitle">Sorties de caisse avec motif, montant et justificatif.</p></div>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif
@if(session('error'))<div class="alert alert-danger"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>@endif

<!-- ═══ INDICATEURS ═══ -->
<div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    @include('partials.dashboard.kpi', ['label' => 'Solde en caisse', 'value' => number_format($solde, 0, ',', ' '), 'meta' => 'FCFA disponibles', 'tone' => 'gold'])
    @include('partials.dashboard.kpi', ['label' => 'Total retraits', 'value' => number_format($totalRetraits, 0, ',', ' '), 'meta' => $countRetraits . ' retrait(s) — FCFA sortis', 'tone' => 'danger'])
    @include('partials.dashboard.kpi', ['label' => 'Moyenne / retrait', 'value' => number_format($moyenne, 0, ',', ' '), 'meta' => 'FCFA par opération', 'tone' => 'info'])
    @include('partials.dashboard.kpi', ['label' => 'Plus gros retrait', 'value' => number_format($maxRetrait, 0, ',', ' '), 'meta' => 'FCFA sur la période', 'tone' => 'warning'])
</div>

<!-- ═══ GRAPHIQUE + TOP MOTIFS ═══ -->
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
    <div class="console-card lg:col-span-2">
        <div class="console-card-head">
            <div>
                <h3 class="console-card-title">Évolution des retraits</h3>
                <p class="console-card-sub">30 derniers jours</p>
            </div>
        </div>
        <div class="console-card-body">
            <div class="h-[280px]"><canvas id="retraitsChart" aria-label="Évolution des retraits sur 30 jours" role="img"></canvas></div>
        </div>
    </div>

    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h3 class="console-card-title">Top motifs</h3>
                <p class="console-card-sub">Répartition des sorties</p>
            </div>
        </div>
        <div class="console-card-body">
        @forelse($topMotifs as $item)
        <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-[#080808] truncate">{{ $item['motif'] }}</div>
                <div class="text-xs text-gray-400">{{ $item['count'] }} retrait(s)</div>
            </div>
            <div class="text-sm font-semibold text-red-600 ml-3">-{{ number_format($item['total'], 0, ',', '.') }}</div>
        </div>
        @empty
        <p class="text-sm text-gray-400">Aucun retrait enregistré</p>
        @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="form-card">
            <div class="form-card-header">
                <h2 class="form-card-title">Nouveau retrait</h2>
                <p class="form-card-subtitle">Enregistrer une sortie de caisse</p>
            </div>

            <form action="{{ route('secretaire.retraits.store') }}" method="POST" class="space-y-5">
                @csrf
                <div class="form-group">
                    <label class="input-label">Montant (FCFA)</label>
                    <input type="number" name="montant" class="input-field" min="0.01" step="any" value="{{ old('montant') }}" required placeholder="0">
                </div>
                <div class="form-group">
                    <label class="input-label">Motif du retrait</label>
                    <textarea name="motif" rows="2" class="input-field" required placeholder="Ex: Achat cartes mémoire...">{{ old('motif') }}</textarea>
                </div>
                <button type="submit" class="btn-danger w-full gap-2">
                    <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Enregistrer le retrait</span>
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="section-title flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Retraits enregistrés</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#080808] text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Heure</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Motif</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($retraits as $r)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $r->date }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $r->created_at->format('H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-[#080808]">{{ $r->motif }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-right text-red-600">-{{ number_format($r->montant, 0, ',', '.') }} FCFA</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">📭</div>Aucun retrait enregistré.
                        </td></tr>
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
    PharaohCharts.bar(document.getElementById('retraitsChart'), {
        labels: @json($labels30j),
        datasets: [{ label: 'Retraits', data: @json($data30j), backgroundColor: 'gold' }]
    }, {
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => PharaohCharts.money(ctx.parsed.y) } }
        },
        scales: { y: { ticks: { callback: value => PharaohCharts.number(value) } } }
    });
});
</script>
@endpush
