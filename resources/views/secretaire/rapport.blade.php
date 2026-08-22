@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Gestion Des Rapports</span></div>
@endsection

@section('navigation')
<a href="{{ route('secretaire.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('secretaire.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Rapport</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('secretaire.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('secretaire.services') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">Rapport</a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="page-heading-title mb-6">Rapport financier photo</h1>

    <div class="pharaoh-card p-6 mb-6">
        <form method="GET" action="{{ route('secretaire.rapport') }}" class="flex flex-wrap items-end gap-4">
            <div><label class="input-label">Date début</label><input type="date" name="date_debut" class="input-field mt-1" value="{{ $dateDebut }}"></div>
            <div><label class="input-label">Date fin</label><input type="date" name="date_fin" class="input-field mt-1" value="{{ $dateFin }}"></div>
            <button type="submit" class="btn-gold">Filtrer</button>
            <a href="{{ route('secretaire.rapport.export', ['date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" class="btn-secondary"><svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Exporter CSV</a>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4 mb-6">
        <div class="stat-card"><div class="stat-card-value text-green-600">{{ number_format($totalCommandes, 0, ',', '.') }}</div><div class="stat-card-label">Commandes (FCFA)</div></div>
        <div class="stat-card"><div class="stat-card-value text-blue-600">{{ number_format($totalServices, 0, ',', '.') }}</div><div class="stat-card-label">Services (FCFA)</div></div>
        <div class="stat-card"><div class="stat-card-value text-red-600">{{ number_format($totalRetraits, 0, ',', '.') }}</div><div class="stat-card-label">Retraits (FCFA)</div></div>
        <div class="stat-card border-pharaoh-gold/30 bg-[#FBF3E6]"><div class="stat-card-value text-pharaoh-gold">{{ number_format($totalCommandes + $totalServices - $totalRetraits, 0, ',', '.') }}</div><div class="stat-card-label font-semibold">Bilan net (FCFA)</div></div>
    </div>

    <div class="pharaoh-card p-6 mb-6">
        <h3 class="text-lg font-semibold text-[#080808] mb-4">Évolution journalière</h3>
        <div class="h-64"><canvas id="caisseChart"></canvas></div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="pharaoh-card p-6"><h3 class="text-lg font-semibold text-[#080808] mb-4">Commandes par type</h3>@forelse($commandesParType as $type => $montant)<div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}"><span class="text-sm text-gray-700">{{ $typesPhoto[$type] ?? $type }}</span><span class="text-sm font-semibold text-green-700">{{ number_format($montant, 0, ',', '.') }} FCFA</span></div>@empty<p class="text-sm text-gray-400">Aucune donnée</p>@endforelse</div>
        <div class="pharaoh-card p-6"><h3 class="text-lg font-semibold text-[#080808] mb-4">Services par type</h3>@forelse($servicesParType as $type => $montant)<div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}"><span class="text-sm text-gray-700">{{ $typesPhoto[$type] ?? $type }}</span><span class="text-sm font-semibold text-blue-700">{{ number_format($montant, 0, ',', '.') }} FCFA</span></div>@empty<p class="text-sm text-gray-400">Aucune donnée</p>@endforelse</div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('caisseChart');
    if (!ctx) return;
    const data = @json($detailJournalier);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(d => d.label),
            datasets: [
                { label: 'Entrées', data: data.map(d => d.entrees), backgroundColor: '#2E8B57', borderRadius: 4 },
                { label: 'Sorties', data: data.map(d => d.sorties), backgroundColor: '#D64545', borderRadius: 4 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' FCFA' } } }, plugins: { legend: { position: 'top' } } }
    });
});
</script>
@endpush
