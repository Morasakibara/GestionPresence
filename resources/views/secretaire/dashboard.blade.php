@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Dashboard — Services Photo</span></div>
@endsection

@section('navigation')
<a href="{{ route('secretaire.dashboard') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('secretaire.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif
@if(session('error'))<div class="alert alert-danger"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>@endif

<div class="mb-6">
    <h2 class="text-lg font-semibold text-[#080808]">État de la caisse — {{ now()->format('d/m/Y') }}</h2>
    <p class="text-sm text-gray-500">Vue globale cumulative (toutes périodes confondues)</p>
</div>

<!-- ═══ LIGNE 1 : Solde + Impayés + Reste à encaisser ═══ -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
    <div class="stat-card border-pharaoh-gold/30 bg-gradient-to-br from-[#FBF3E6] to-white">
        <div class="stat-card-icon bg-pharaoh-gold/10 text-pharaoh-gold"><svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg></div>
        <div class="stat-card-value text-pharaoh-gold">{{ number_format($sommeEnCaisse, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label font-semibold">Solde en caisse</div>
    </div>
    @if(isset($impayeTotal) && $impayeTotal > 0)
    <div class="stat-card border-red-200 bg-gradient-to-br from-red-50 to-white">
        <div class="stat-card-icon bg-red-100 text-red-600"><svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
        <div class="stat-card-value text-red-600">-{{ number_format($impayeTotal, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label font-semibold">Impayés (non encaissé)</div>
    </div>
    @endif
    @if($resteAEncaisser > 0)
    <button onclick="document.getElementById('modalImpayes').classList.remove('hidden')" class="stat-card border-orange-200 bg-gradient-to-br from-orange-50 to-white text-left cursor-pointer hover:shadow-md transition-shadow">
        <div class="stat-card-icon bg-orange-100 text-orange-600"><svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
        <div class="stat-card-value text-orange-600">{{ number_format($resteAEncaisser, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label font-semibold">Reste à encaisser (cliquer) — {{ $commandesImpayees->count() }} commande(s)</div>
    </button>
    @endif
</div>

<!-- ═══ LIGNE 2 : Total Commandes + Services + Retraits ═══ -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
    {{-- Total Commandes avec détail par catégorie --}}
    <div class="pharaoh-card p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="stat-card-icon bg-green-50 text-green-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1" /></svg>
            </div>
            <div>
                <div class="text-lg font-bold text-green-600">{{ number_format($totalCommandes, 0, ',', '.') }} FCFA</div>
                <div class="text-xs text-gray-500">Total Commandes</div>
            </div>
        </div>
        @if(!empty($commandesParCategorie))
        <div class="border-t pt-3 space-y-1.5">
            @foreach($commandesParCategorie as $type => $data)
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">{{ $typesPhoto[$type] ?? $type }}</span>
                <span class="font-medium text-gray-800">{{ number_format($data['total'], 0, ',', '.') }} FCFA <span class="text-gray-400">({{ $data['count'] }})</span></span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Total Services avec détail par catégorie --}}
    <div class="pharaoh-card p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="stat-card-icon bg-blue-50 text-blue-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
            <div>
                <div class="text-lg font-bold text-blue-600">{{ number_format($totalServices, 0, ',', '.') }} FCFA</div>
                <div class="text-xs text-gray-500">Total Services</div>
            </div>
        </div>
        @if(!empty($servicesParCategorie))
        <div class="border-t pt-3 space-y-1.5">
            @foreach($servicesParCategorie as $type => $data)
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">{{ $typesPhoto[$type] ?? $type }}</span>
                <span class="font-medium text-gray-800">{{ number_format($data['total'], 0, ',', '.') }} FCFA <span class="text-gray-400">({{ $data['count'] }})</span></span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Retraits — séparé --}}
    <div class="pharaoh-card p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="stat-card-icon bg-red-50 text-red-600">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            </div>
            <div>
                <div class="text-lg font-bold text-red-600">{{ number_format($totalRetraits, 0, ',', '.') }} FCFA</div>
                <div class="text-xs text-gray-500">Total Retraits</div>
            </div>
        </div>
        @if(isset($derniersRetraits) && count($derniersRetraits) > 0)
        <div class="border-t pt-3 space-y-1.5">
            @foreach($derniersRetraits->take(5) as $rt)
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600 truncate max-w-[140px]" title="{{ $rt->motif }}">{{ $rt->motif }}</span>
                <span class="font-medium text-red-600">-{{ number_format($rt->montant, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="border-t pt-3">
            <p class="text-xs text-gray-400">Aucun retrait enregistré</p>
        </div>
        @endif
    </div>
</div>

<!-- Graphique 7 jours -->
<div class="pharaoh-card p-6 mb-6">
    <h3 class="text-lg font-semibold text-[#080808] mb-4 flex items-center gap-2">
        <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <span>Entrées / Sorties — 7 derniers jours</span>
    </h3>
    <div style="height:300px;"><canvas id="chart7j"></canvas></div>
</div>

<!-- Répartition -->
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
    <div class="pharaoh-card p-6"><h3 class="text-lg font-semibold text-[#080808] mb-4">Commandes par type</h3>
        @forelse($commandesParType as $type => $montant)<div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}"><span class="text-sm text-gray-700">{{ $typesPhoto[$type] ?? $type }}</span><span class="text-sm font-semibold text-[#080808]">{{ number_format($montant, 0, ',', '.') }} FCFA</span></div>@empty<p class="text-sm text-gray-400">Aucune commande</p>@endforelse
    </div>
    <div class="pharaoh-card p-6"><h3 class="text-lg font-semibold text-[#080808] mb-4">Services par type</h3>
        @forelse($servicesParType as $type => $montant)<div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}"><span class="text-sm text-gray-700">{{ $typesPhoto[$type] ?? $type }}</span><span class="text-sm font-semibold text-[#080808]">{{ number_format($montant, 0, ',', '.') }} FCFA</span></div>@empty<p class="text-sm text-gray-400">Aucun service</p>@endforelse
    </div>
</div>

<!-- Accès rapides -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('secretaire.commandes') }}" class="pharaoh-card btn-press flex flex-col items-center p-6 hover:shadow-lg transition-shadow"><div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg></div><h2 class="mt-3 text-base font-medium text-gray-900">Nouvelle Commande</h2></a>
    <a href="{{ route('secretaire.services') }}" class="pharaoh-card btn-press flex flex-col items-center p-6 hover:shadow-lg transition-shadow"><div class="rounded-full bg-blue-50 p-3 text-blue-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg></div><h2 class="mt-3 text-base font-medium text-gray-900">Nouveau Service</h2></a>
    <a href="{{ route('secretaire.retraits') }}" class="pharaoh-card btn-press flex flex-col items-center p-6 hover:shadow-lg transition-shadow"><div class="rounded-full bg-red-50 p-3 text-red-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg></div><h2 class="mt-3 text-base font-medium text-gray-900">Retrait</h2></a>
    <a href="{{ route('secretaire.rapport') }}" class="pharaoh-card btn-press flex flex-col items-center p-6 hover:shadow-lg transition-shadow"><div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></div><h2 class="mt-3 text-base font-medium text-gray-900">Rapport</h2></a>
</div>

<!-- ═══ MODAL COMMANDES IMPAYÉES ═══ -->
@if(isset($commandesImpayees) && $commandesImpayees->count() > 0)
<div id="modalImpayes" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden">
        <div class="flex items-center justify-between border-b px-6 py-4 bg-orange-50">
            <h3 class="text-lg font-bold text-orange-800 flex items-center gap-2">
                <svg class="h-5 w-5 text-orange-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Commandes en attente — {{ $commandesImpayees->count() }}</span>
            </h3>
            <button onclick="document.getElementById('modalImpayes').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <div class="overflow-y-auto max-h-[60vh] p-6">
            @foreach($commandesImpayees as $cmd)
            <div class="mb-4 rounded-xl border border-orange-200 p-4 bg-white hover:shadow-sm transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <span class="font-semibold text-[#080808]">{{ $typesPhoto[$cmd->type] ?? $cmd->type }}</span>
                        <span class="ml-2 text-sm text-gray-500">#{{ $cmd->id }} — {{ $cmd->date }}</span>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $cmd->statut_paiement === 'a_payer' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ $cmd->statut_paiement === 'a_payer' ? 'À payer' : 'Partiel' }}
                    </span>
                </div>
                <div class="flex items-center gap-4 text-sm mb-3">
                    <span>Total : <b class="text-[#080808]">{{ number_format($cmd->montant, 0, ',', '.') }} FCFA</b></span>
                    <span>Payé : <b class="text-green-600">{{ number_format($cmd->montant_paye, 0, ',', '.') }} FCFA</b></span>
                    <span class="text-orange-600 font-bold">Reste : {{ number_format($cmd->montant - $cmd->montant_paye, 0, ',', '.') }} FCFA</span>
                </div>
                <form action="{{ route('secretaire.encaisserReste') }}" method="POST" class="flex items-center gap-2" onsubmit="return confirm('Confirmer l\\'encaissement ?')">
                    @csrf
                    <input type="hidden" name="commande_id" value="{{ $cmd->id }}">
                    <input type="number" name="montant_verse" min="0.01" step="any" max="{{ $cmd->montant - $cmd->montant_paye }}" value="{{ $cmd->montant - $cmd->montant_paye }}" required
                           class="w-36 rounded-lg border-gray-300 text-sm focus:border-pharaoh-gold focus:ring-pharaoh-gold">
                    <span class="text-xs text-gray-400">FCFA</span>
                    <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition inline-flex items-center gap-1">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Encaisser</span>
                    </button>
                </form>
            </div>
            @endforeach
        </div>

        {{-- Historique des encaissements récents --}}
        @if(isset($historiqueEncaissements) && $historiqueEncaissements->count() > 0)
        <div class="mt-4 border-t pt-4">
            <h4 class="text-sm font-semibold text-gray-600 mb-3 flex items-center gap-1.5">
                <svg class="h-4 w-4 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Derniers encaissements</span>
            </h4>
            @foreach($historiqueEncaissements as $log)
            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400">{{ $log->created_at->format('d/m H:i') }}</span>
                    <span class="text-sm text-[#080808]">{{ $log->description }}</span>
                </div>
                <span class="text-xs text-gray-400">{{ $log->user?->nom }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <div class="border-t px-6 py-3 bg-gray-50 flex justify-between items-center">
            <span class="text-sm font-semibold text-orange-700">Total reste : {{ number_format($resteAEncaisser, 0, ',', '.') }} FCFA</span>
            <button onclick="document.getElementById('modalImpayes').classList.add('hidden')" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-300">Fermer</button>
        </div>
    </div>
</div>
@endif

@endsection

@section('mobile-navigation')
<a href="{{ route('secretaire.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium bg-pharaoh-gold text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('secretaire.services') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const ctx = document.getElementById('chart7j');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($labels7j),
            datasets: [
                { label: 'Entrées', data: @json($dataEntrees7j), backgroundColor: 'rgba(46,139,87,0.7)', borderRadius: 6 },
                { label: 'Sorties', data: @json($dataSorties7j), backgroundColor: 'rgba(214,69,69,0.7)', borderRadius: 6 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') + ' FCFA' } } },
            plugins: { legend: { position: 'top' } }
        }
    });
}
</script>
@endpush
