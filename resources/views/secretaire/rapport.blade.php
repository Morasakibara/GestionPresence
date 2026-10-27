@extends('layouts.dashboard')

@section('header')
<div><span>Rapport Financier Photo</span><p class="console-subtitle">Commandes, services et retraits consolidés sur la période.</p></div>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif

<!-- Filtres avancés -->
<div class="form-card mb-6">
    <div class="form-card-header mb-0">
        <h2 class="form-card-title">Filtres d'analyse</h2>
    </div>
    <form method="GET" action="{{ route('secretaire.rapport') }}" class="space-y-4 mt-4">
        <div class="flex flex-wrap items-end gap-4">
            <div class="form-group flex-1 min-w-[140px]">
                <label class="input-label">Date début</label>
                <input type="date" name="date_debut" class="input-field" value="{{ $dateDebut }}">
            </div>
            <div class="form-group flex-1 min-w-[140px]">
                <label class="input-label">Date fin</label>
                <input type="date" name="date_fin" class="input-field" value="{{ $dateFin }}">
            </div>
            <div class="form-group flex-1 min-w-[140px]">
                <label class="input-label">Catégorie</label>
                <select name="type" class="select-field">
                    <option value="">Toutes</option>
                    @foreach($typesPhoto as $key => $label)
                        <option value="{{ $key }}" {{ ($filters['type'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group flex-1 min-w-[140px]">
                <label class="input-label">Statut paiement</label>
                <select name="statut" class="select-field">
                    <option value="">Tous</option>
                    <option value="paye" {{ ($filters['statut'] ?? '') === 'paye' ? 'selected' : '' }}>Payé</option>
                    <option value="partiel" {{ ($filters['statut'] ?? '') === 'partiel' ? 'selected' : '' }}>Partiel</option>
                    <option value="a_payer" {{ ($filters['statut'] ?? '') === 'a_payer' ? 'selected' : '' }}>À payer</option>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-end gap-4">
            <div class="form-group flex-1 min-w-[140px]">
                <label class="input-label">Montant min (FCFA)</label>
                <input type="number" name="montant_min" class="input-field" placeholder="0" value="{{ $filters['montant_min'] ?? '' }}" min="0">
            </div>
            <div class="form-group flex-1 min-w-[140px]">
                <label class="input-label">Montant max (FCFA)</label>
                <input type="number" name="montant_max" class="input-field" placeholder="∞" value="{{ $filters['montant_max'] ?? '' }}" min="0">
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="btn-gold btn-sm gap-1.5">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    Filtrer
                </button>
                <a href="{{ route('secretaire.rapport') }}" class="btn-secondary btn-sm">Réinitialiser</a>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('secretaire.rapport.export', array_merge(['date_debut' => $dateDebut, 'date_fin' => $dateFin], $filters)) }}" class="btn-secondary btn-sm gap-1.5">CSV</a>
            <a href="{{ route('secretaire.rapport.exportHtml', array_merge(['date_debut' => $dateDebut, 'date_fin' => $dateFin], $filters)) }}" target="_blank" class="btn-ghost btn-sm gap-1.5">HTML</a>
            <a href="{{ route('secretaire.rapport.exportPdf', array_merge(['date_debut' => $dateDebut, 'date_fin' => $dateFin], $filters)) }}" class="btn-danger btn-sm gap-1.5">PDF</a>
        </div>
    </form>
</div>

<!-- Totaux -->
<div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    @include('partials.dashboard.kpi', ['label' => 'Commandes', 'value' => number_format($totalCommandes, 0, ',', ' '), 'meta' => 'FCFA encaissés', 'tone' => 'success'])
    @include('partials.dashboard.kpi', ['label' => 'Services', 'value' => number_format($totalServices, 0, ',', ' '), 'meta' => 'FCFA facturés', 'tone' => 'info'])
    @include('partials.dashboard.kpi', ['label' => 'Retraits', 'value' => number_format($totalRetraits, 0, ',', ' '), 'meta' => 'FCFA sortis', 'tone' => 'danger'])
    @include('partials.dashboard.kpi', ['label' => 'Bilan net', 'value' => number_format($totalCommandes + $totalServices - $totalRetraits, 0, ',', ' '), 'meta' => 'FCFA sur la période', 'tone' => 'gold'])
</div>

<!-- Graphique -->
<div class="console-card mb-6">
    <div class="console-card-head">
        <div>
            <h3 class="console-card-title">Évolution journalière</h3>
            <p class="console-card-sub">Entrées et sorties de caisse</p>
        </div>
    </div>
    <div class="console-card-body">
        <div class="h-[300px]"><canvas id="rapportChart" aria-label="Évolution journalière des entrées et sorties" role="img"></canvas></div>
    </div>
</div>

<!-- Répartition -->
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="console-card">
        <div class="console-card-head">
            <h3 class="console-card-title">Commandes par type</h3>
        </div>
        <div class="console-card-body">
            @forelse($commandesParType as $type => $montant)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <span class="text-sm text-gray-700">{{ $typesPhoto[$type] ?? $type }}</span>
                    <span class="text-sm font-semibold tabular-nums text-green-700">{{ number_format($montant, 0, ',', ' ') }} FCFA</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">Aucune donnée</p>
            @endforelse
        </div>
    </div>
    <div class="console-card">
        <div class="console-card-head">
            <h3 class="console-card-title">Services par type</h3>
        </div>
        <div class="console-card-body">
            @forelse($servicesParType as $type => $montant)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <span class="text-sm text-gray-700">{{ $typesPhoto[$type] ?? $type }}</span>
                    <span class="text-sm font-semibold tabular-nums text-blue-700">{{ number_format($montant, 0, ',', ' ') }} FCFA</span>
                </div>
            @empty
                <p class="text-sm text-gray-400">Aucune donnée</p>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script src="{{ asset('js/pharaoh-charts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const data = @json($detailJournalier);

    PharaohCharts.bar(document.getElementById('rapportChart'), {
        labels: data.map(d => d.label),
        datasets: [
            { label: 'Entrées', data: data.map(d => d.entrees), backgroundColor: 'success' },
            { label: 'Sorties', data: data.map(d => d.sorties), backgroundColor: 'danger' }
        ]
    }, {
        plugins: {
            tooltip: { callbacks: { label: ctx => ctx.dataset.label + ' : ' + PharaohCharts.money(ctx.parsed.y) } }
        },
        scales: { y: { ticks: { callback: value => PharaohCharts.number(value) } } }
    });
});
</script>
@endpush
