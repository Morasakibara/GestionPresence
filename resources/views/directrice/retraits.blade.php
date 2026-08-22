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

@section('mobile-navigation')
<a href="{{ route('directrice.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
    @if(session('success'))
        <div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>
    @endif

    <h1 class="page-heading-title mb-6">Retraits</h1>

    <!-- Somme disponible -->
    <div class="pharaoh-card p-6 mb-6 border-pharaoh-gold/30 bg-gradient-to-br from-[#FBF3E6] to-white">
        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Somme disponible en caisse</div>
        <div class="mt-1 text-3xl font-bold text-pharaoh-gold">{{ number_format($totalEntrees - $totalSorties, 0, ',', '.') }} FCFA</div>
        <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Entrées :</span> <span class="font-semibold text-green-700">{{ number_format($totalEntrees, 0, ',', '.') }} FCFA</span></div>
            <div><span class="text-gray-500">Sorties :</span> <span class="font-semibold text-red-600">{{ number_format($totalSorties, 0, ',', '.') }} FCFA</span></div>
        </div>
    </div>

    <!-- Formulaire -->
    <div class="pharaoh-card p-6 mb-6">
        <form action="{{ route('directrice.retraits.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="input-label">Montant (FCFA)</label>
                    <input type="number" name="montant" class="input-field mt-1" min="0.01" step="0.01" value="{{ old('montant') }}" required>
                </div>
                <div>
                    <label class="input-label">Motif du retrait</label>
                    <input type="text" name="motif" class="input-field mt-1" value="{{ old('motif') }}" placeholder="Ex: Achat fournitures..." required>
                </div>
            </div>
            <button type="submit" class="btn-gold bg-red-600 hover:bg-red-700 focus:ring-red-500">
                <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Enregistrer le retrait
            </button>
        </form>
    </div>

    <!-- Liste -->
    <div class="pharaoh-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200/70"><h2 class="text-lg font-semibold text-[#080808]">Retraits du jour</h2></div>
        <div class="table-scroll">
            <table class="min-w-full">
                <thead class="table-head"><tr><th>Heure</th><th>Motif</th><th class="text-right">Montant</th></tr></thead>
                <tbody class="table-body">
                    @forelse($retraits as $r)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $r->created_at->format('H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-[#080808]">{{ $r->motif }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-right text-red-600">{{ number_format($r->montant, 0, ',', '.') }} FCFA</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-12 text-center text-sm text-gray-400">Aucun retrait aujourd'hui</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
