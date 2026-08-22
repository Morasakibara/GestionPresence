@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Dashboard Gestion De Stock</span></div>
@endsection

@section('navigation')
<a href="{{ route('gestionnaire.dashboard') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Dashboard</a>
<a href="{{ route('gestionnaire.tshirts') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">T-Shirts</a>
<a href="{{ route('gestionnaire.papier') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Papier</a>
<a href="{{ route('superviseur.supdashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Équipe</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('gestionnaire.dashboard') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">Dashboard</a>
<a href="{{ route('gestionnaire.tshirts') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">T-Shirts</a>
<a href="{{ route('gestionnaire.papier') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Papier</a>
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif

<div class="mb-6">
    <h2 class="text-lg font-semibold text-[#080808]">Vue d'ensemble des stocks</h2>
    <p class="text-sm text-gray-500">État des stocks T-shirts et papier d'impression</p>
</div>

<!-- Alertes -->
@if($tshirtsEnAlerte->isNotEmpty() || $papiersEnAlerte->isNotEmpty())
<div class="mb-6">
    <div class="alert alert-warning">
        <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <h3 class="text-sm font-medium text-yellow-800">Stock en alerte</h3>
            <div class="mt-1 text-sm text-yellow-700">
                @foreach($tshirtsEnAlerte as $t)
                    <div>T-Shirt {{ $t->couleur }} {{ $t->taille }} : {{ $t->quantite }} restant(s) (seuil: {{ $t->seuil_alerte }})</div>
                @endforeach
                @foreach($papiersEnAlerte as $p)
                    <div>Papier {{ $p->imprimante }} : {{ $p->metres_restants }} m restant(s) (seuil: {{ $p->seuil_alerte }} m)</div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- Stats -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="stat-card">
        <div class="stat-card-icon bg-blue-50 text-blue-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
        </div>
        <div class="stat-card-value">{{ $totalTshirts }}</div>
        <div class="stat-card-label">Total T-Shirts</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon bg-purple-50 text-purple-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7" /></svg>
        </div>
        <div class="stat-card-value">{{ $papiers->count() }}</div>
        <div class="stat-card-label">Imprimantes</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon bg-red-50 text-red-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="stat-card-value">{{ $tshirtsEnAlerte->count() + $papiersEnAlerte->count() }}</div>
        <div class="stat-card-label">Alertes actives</div>
    </div>
    <div class="stat-card border-pharaoh-gold/30 bg-[#FBF3E6]">
        <div class="stat-card-icon bg-pharaoh-gold/10 text-pharaoh-gold">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </div>
        <div class="stat-card-value text-pharaoh-gold">{{ $tshirts->count() + $papiers->count() }}</div>
        <div class="stat-card-label font-semibold">Références stock</div>
    </div>
</div>

<!-- Aperçu T-Shirts -->
<div class="pharaoh-card p-6 mb-6">
    <h3 class="text-lg font-semibold text-[#080808] mb-4">Aperçu T-Shirts</h3>
    @if($tshirts->isEmpty())
        <p class="text-sm text-gray-400">Aucun T-shirt en stock</p>
    @else
        <div class="table-scroll"><table class="min-w-full"><thead class="table-head"><tr><th>Couleur</th><th>Taille</th><th class="text-right">Quantité</th><th>Statut</th></tr></thead>
            <tbody class="table-body">@foreach($tshirts->take(10) as $t)<tr><td class="px-6 py-3 text-sm font-medium text-[#080808]">{{ $t->couleur }}</td><td class="px-6 py-3 text-sm text-gray-700">{{ $t->taille }}</td><td class="px-6 py-3 text-sm text-right font-semibold">{{ $t->quantite }}</td><td class="px-6 py-3"><span class="badge {{ $t->enAlerte() ? 'badge-danger' : 'badge-success' }}">{{ $t->enAlerte() ? 'Alerte' : 'OK' }}</span></td></tr>@endforeach</tbody>
        </table></div>
    @endif
    <div class="mt-4"><a href="{{ route('gestionnaire.tshirts') }}" class="text-sm font-medium text-pharaoh-gold hover:text-pharaoh-bronze">Voir tout →</a></div>
</div>

<!-- Aperçu Papier -->
<div class="pharaoh-card p-6 mb-6">
    <h3 class="text-lg font-semibold text-[#080808] mb-4">Aperçu Papier d'impression</h3>
    @if($papiers->isEmpty())
        <p class="text-sm text-gray-400">Aucun stock de papier enregistré</p>
    @else
        <div class="table-scroll"><table class="min-w-full"><thead class="table-head"><tr><th>Imprimante</th><th class="text-right">Reste (m)</th><th class="text-right">Total (m)</th><th>Statut</th></tr></thead>
            <tbody class="table-body">@foreach($papiers as $p)<tr><td class="px-6 py-3 text-sm font-medium text-[#080808]">{{ $p->imprimante }}</td><td class="px-6 py-3 text-sm text-right font-semibold">{{ number_format($p->metres_restants, 1) }}</td><td class="px-6 py-3 text-sm text-right text-gray-500">{{ number_format($p->metres_total, 1) }}</td><td class="px-6 py-3"><span class="badge {{ $p->enAlerte() ? 'badge-danger' : 'badge-success' }}">{{ $p->enAlerte() ? 'Alerte' : 'OK' }}</span></td></tr>@endforeach</tbody>
        </table></div>
    @endif
    <div class="mt-4"><a href="{{ route('gestionnaire.papier') }}" class="text-sm font-medium text-pharaoh-gold hover:text-pharaoh-bronze">Voir tout →</a></div>
</div>

<!-- Accès rapides -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <a href="{{ route('gestionnaire.tshirts') }}" class="pharaoh-card btn-press flex flex-col items-center p-6 hover:shadow-lg transition-shadow"><div class="rounded-full bg-blue-50 p-3 text-blue-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg></div><h2 class="mt-3 text-base font-medium text-gray-900">Gérer les T-Shirts</h2></a>
    <a href="{{ route('gestionnaire.papier') }}" class="pharaoh-card btn-press flex flex-col items-center p-6 hover:shadow-lg transition-shadow"><div class="rounded-full bg-purple-50 p-3 text-purple-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7" /></svg></div><h2 class="mt-3 text-base font-medium text-gray-900">Gérer le Papier</h2></a>
</div>
@endsection
