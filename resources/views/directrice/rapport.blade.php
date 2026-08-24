@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Rapport Financier</span></div>
@endsection

@section('navigation')
<a href="{{ route('directrice.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Rapport</a>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif

<!-- Filtres -->
<div class="form-card mb-6">
    <div class="form-card-header mb-0">
        <h2 class="form-card-title">Période d'analyse</h2>
    </div>
    <form method="GET" action="{{ route('directrice.rapport') }}" class="flex flex-wrap items-end gap-4 mt-4">
        <div class="form-group flex-1 min-w-[150px]">
            <label class="input-label">Date début</label>
            <input type="date" name="date_debut" class="input-field" value="{{ $dateDebut }}">
        </div>
        <div class="form-group flex-1 min-w-[150px]">
            <label class="input-label">Date fin</label>
            <input type="date" name="date_fin" class="input-field" value="{{ $dateFin }}">
        </div>
        <button type="submit" class="btn-primary">🔍 Filtrer</button>
        <a href="{{ route('directrice.rapport.export', ['date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" class="btn-secondary">
            📥 Export CSV
        </a>
        <a href="{{ route('directrice.rapport.exportHtml', ['date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" target="_blank" class="btn-primary">
            📊 Rapport Complet
        </a>
    </form>
</div>

<!-- Totaux -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="stat-card">
        <div class="stat-card-icon bg-green-50 text-green-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1" /></svg>
        </div>
        <div class="stat-card-value text-green-600">{{ number_format($totalCommandes, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label">Commandes</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon bg-blue-50 text-blue-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
        </div>
        <div class="stat-card-value text-blue-600">{{ number_format($totalServices, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label">Services</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon bg-red-50 text-red-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
        <div class="stat-card-value text-red-600">{{ number_format($totalRetraits, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label">Retraits</div>
    </div>
    <div class="stat-card border-pharaoh-gold/30 bg-gradient-to-br from-[#FBF3E6] to-white">
        <div class="stat-card-icon bg-pharaoh-gold/10 text-pharaoh-gold">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
        </div>
        <div class="stat-card-value text-pharaoh-gold">{{ number_format($totalCommandes + $totalServices - $totalRetraits, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label font-semibold">Bilan net</div>
    </div>
</div>

<!-- Graphique -->
<div class="pharaoh-card p-6 mb-6">
    <h3 class="text-lg font-semibold text-[#080808] mb-4">📈 Évolution journalière</h3>
    <div style="height:300px;"><canvas id="rapportChart"></canvas></div>
</div>

<!-- Répartition -->
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="pharaoh-card p-6">
        <h3 class="text-lg font-semibold text-[#080808] mb-4">Commandes par type</h3>
        @forelse($commandesParType as $type => $montant)
            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <span class="text-sm text-gray-700 capitalize">{{ $typesCaisse[$type] ?? $type }}</span>
                <span class="text-sm font-semibold text-green-700">{{ number_format($montant, 0, ',', '.') }} FCFA</span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucune donnée</p>
        @endforelse
    </div>
    <div class="pharaoh-card p-6">
        <h3 class="text-lg font-semibold text-[#080808] mb-4">Services par type</h3>
        @forelse($servicesParType as $type => $montant)
            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <span class="text-sm text-gray-700 capitalize">{{ $typesCaisse[$type] ?? $type }}</span>
                <span class="text-sm font-semibold text-blue-700">{{ number_format($montant, 0, ',', '.') }} FCFA</span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucune donnée</p>
        @endforelse
    </div>
</div>

@endsection

@section('mobile-navigation')
<a href="{{ route('directrice.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium bg-pharaoh-gold text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('rapportChart');
    if (!ctx) return;
    const data = @json($detailJournalier);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.label),
            datasets: [
                { label: 'Entrées', data: data.map(d => d.entrees), backgroundColor: 'rgba(46,139,87,0.7)', borderRadius: 6 },
                { label: 'Sorties', data: data.map(d => d.sorties), backgroundColor: 'rgba(214,69,69,0.7)', borderRadius: 6 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' FCFA' } } },
            plugins: { legend: { position: 'top' } }
        }
    });
});
</script>
@endpush
