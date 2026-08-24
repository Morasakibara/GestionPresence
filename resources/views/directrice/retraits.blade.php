@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Retraits</span></div>
@endsection

@section('navigation')
<a href="{{ route('directrice.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif
@if(session('error'))<div class="alert alert-danger"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>@endif

<!-- ═══ STATS CARDS ═══ -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="stat-card border-pharaoh-gold/30 bg-gradient-to-br from-[#FBF3E6] to-white">
        <div class="stat-card-icon bg-pharaoh-gold/10 text-pharaoh-gold">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
        <div class="stat-card-value text-pharaoh-gold">{{ number_format($solde, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label font-semibold">Solde en caisse</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon bg-red-50 text-red-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
        <div class="stat-card-value text-red-600">{{ number_format($totalRetraits, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label">{{ $countRetraits }} retrait(s)</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon bg-blue-50 text-blue-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
        </div>
        <div class="stat-card-value text-blue-600">{{ number_format($moyenne, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label">Moyenne / retrait</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon bg-orange-50 text-orange-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
        </div>
        <div class="stat-card-value text-orange-600">{{ number_format($maxRetrait, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label">Plus gros retrait</div>
    </div>
</div>

<!-- ═══ GRAPHIQUE + TOP MOTIFS ═══ -->
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
    <!-- Graphique 30 jours -->
    <div class="lg:col-span-2 pharaoh-card p-6">
        <h3 class="text-lg font-semibold text-[#080808] mb-4 flex items-center gap-2">
            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
            <span>Évolution des retraits — 30 derniers jours</span>
        </h3>
        <div style="height:280px;"><canvas id="retraitsChart"></canvas></div>
    </div>

    <!-- Top motifs -->
    <div class="pharaoh-card p-6">
        <h3 class="text-lg font-semibold text-[#080808] mb-4">📋 Top motifs</h3>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ═══ FORMULAIRE ═══ -->
    <div class="lg:col-span-1">
        <div class="form-card">
            <div class="form-card-header">
                <h2 class="form-card-title">Nouveau retrait</h2>
                <p class="form-card-subtitle">Enregistrer une sortie de caisse</p>
            </div>

            <form action="{{ route('directrice.retraits.store') }}" method="POST" class="space-y-5">
                @csrf
                <div class="form-group">
                    <label class="input-label">Montant (FCFA)</label>
                    <input type="number" name="montant" class="input-field" min="0.01" step="any" value="{{ old('montant') }}" required placeholder="0">
                </div>
                <div class="form-group">
                    <label class="input-label">Motif du retrait</label>
                    <textarea name="motif" rows="2" class="input-field" required placeholder="Ex: Achat fournitures, dépôt bancaire...">{{ old('motif') }}</textarea>
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

    <!-- ═══ LISTE DES RETRAITS ═══ -->
    <div class="lg:col-span-2">
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-[#080808] flex items-center gap-2">
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
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('retraitsChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($labels30j),
            datasets: [{
                label: 'Retraits',
                data: @json($data30j),
                backgroundColor: 'rgba(214,69,69,0.65)',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' FCFA' } },
                x: { ticks: { maxRotation: 45, font: { size: 10 } } }
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ctx.parsed.y.toLocaleString('fr-FR') + ' FCFA' } }
            }
        }
    });
});
</script>
@endpush
