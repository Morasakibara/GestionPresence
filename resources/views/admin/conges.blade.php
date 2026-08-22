@extends('layouts.app')

@section('header', 'Gestion des Congés — Entreprise')

@section('navigation')
<a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('admin.conges') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Congés</a>
<a href="{{ route('admin.showEmployeeList') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Employés</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('admin.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('admin.conges') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white" aria-current="page">Congés</a>
@endsection

@section('content')
<!-- Export PDF -->
<div class="flex flex-wrap items-center justify-end gap-2 mb-6">
    <a href="{{ route('admin.conges.planning.pdf', ['mois' => now()->format('Y-m')]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border-2 border-[#080808] bg-[#080808] px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition-colors">
        📄 Planning PDF
    </a>
    <a href="{{ route('admin.conges.resume.pdf') }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-pharaoh-gold px-4 py-2 text-sm font-semibold text-white hover:bg-pharaoh-gold-light transition-colors">
        📊 Résumé PDF
    </a>
</div>

@php
    $statutFilter = request('statut');
    $typeFilter = request('type');
    $equipeFilter = request('equipe');
@endphp

<!-- Stats -->
<div class="grid grid-cols-2 gap-4 mb-6 sm:grid-cols-4">
    <div class="stat-card border border-white/20 bg-gradient-to-br from-white/90 to-white/70">
        <div class="stat-card-icon bg-pharaoh-gold/10 text-pharaoh-gold">📋</div>
        <div class="stat-card-value">{{ $stats['total'] }}</div>
        <div class="stat-card-label">Total demandes</div>
    </div>
    <div class="stat-card border border-amber-300/30 bg-gradient-to-br from-amber-50/90 to-amber-50/70">
        <div class="stat-card-icon bg-amber-500/10 text-amber-600">⏳</div>
        <div class="stat-card-value text-amber-600">{{ $stats['en_attente'] }}</div>
        <div class="stat-card-label">En attente</div>
    </div>
    <div class="stat-card border border-emerald-300/30 bg-gradient-to-br from-emerald-50/90 to-emerald-50/70">
        <div class="stat-card-icon bg-emerald-500/10 text-emerald-600">✅</div>
        <div class="stat-card-value text-emerald-600">{{ $stats['approuves'] }}</div>
        <div class="stat-card-label">Approuvés</div>
    </div>
    <div class="stat-card border border-red-300/30 bg-gradient-to-br from-red-50/90 to-red-50/70">
        <div class="stat-card-icon bg-red-500/10 text-red-600">❌</div>
        <div class="stat-card-value text-red-600">{{ $stats['refuses'] }}</div>
        <div class="stat-card-label">Refusés</div>
    </div>
</div>

<!-- Graphique -->
<div class="card mb-6">
    <h2 class="text-lg font-bold text-[#080808] mb-1">📈 Évolution des demandes (6 mois)</h2>
    <p class="text-sm text-gray-500 mb-4">Nombre de demandes de congé/absence par mois</p>
    <canvas id="congesChart" height="100"></canvas>
</div>

<!-- Filtres -->
<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="{{ route('admin.conges') }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ !$statutFilter && !$typeFilter && !$equipeFilter ? 'bg-pharaoh-gold text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        Tous
    </a>
    <a href="{{ route('admin.conges', ['statut' => 'en_attente']) }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ $statutFilter === 'en_attente' ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        ⏳ En attente
    </a>
    <a href="{{ route('admin.conges', ['statut' => 'approuve']) }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ $statutFilter === 'approuve' ? 'bg-emerald-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        ✅ Approuvés
    </a>
    <a href="{{ route('admin.conges', ['statut' => 'refuse']) }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ $statutFilter === 'refuse' ? 'bg-red-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        ❌ Refusés
    </a>

    <select onchange="window.location.href=this.value" class="rounded-full border border-gray-200 px-4 py-1.5 text-sm bg-white">
        <option value="{{ route('admin.conges') }}" {{ !$equipeFilter ? 'selected' : '' }}>Toutes les équipes</option>
        @foreach($equipes as $eq)
            <option value="{{ route('admin.conges', array_merge(request()->query(), ['equipe' => $eq])) }}" {{ $equipeFilter === $eq ? 'selected' : '' }}>{{ $eq }}</option>
        @endforeach
    </select>
</div>

<!-- Tableau -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#080808] text-white">
                    <th class="px-4 py-3 text-left font-semibold">Employé</th>
                    <th class="px-4 py-3 text-left font-semibold">Type</th>
                    <th class="px-4 py-3 text-left font-semibold">Période</th>
                    <th class="px-4 py-3 text-center font-semibold">Jours</th>
                    <th class="px-4 py-3 text-left font-semibold">Motif</th>
                    <th class="px-4 py-3 text-left font-semibold">Statut</th>
                    <th class="px-4 py-3 text-left font-semibold">Traité par</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($conges as $conge)
                <tr class="hover:bg-pharaoh-gold/5 transition-colors">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-900">{{ $conge->employe->nom ?? 'N/A' }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $conge->typeLabel() }}</td>
                    <td class="px-4 py-3 text-sm">
                        {{ $conge->date_debut->format('d/m') }} → {{ $conge->date_fin->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-pharaoh-gold/10 text-sm font-bold text-pharaoh-gold">{{ $conge->dureeJours() }}</span>
                    </td>
                    <td class="px-4 py-3 max-w-xs truncate" title="{{ $conge->motif }}">{{ $conge->motif }}</td>
                    <td class="px-4 py-3">{!! $conge->statutBadge() !!}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $conge->traitePar->nom ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                        <div class="text-4xl mb-2">🏖️</div>
                        Aucune demande de congé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($conges->hasPages())
    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
        {{ $conges->links() }}
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('congesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Demandes',
                    data: @json($chartData),
                    backgroundColor: '#D39B2340',
                    borderColor: '#D39B23',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: '#D39B23',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#080808', padding: 12, cornerRadius: 8 }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
});
</script>
@endsection
