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

<!-- Solde disponible -->
<div class="pharaoh-card p-6 mb-6 border-pharaoh-gold/30 bg-gradient-to-br from-[#FBF3E6] to-white">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Somme disponible en caisse</div>
            <div class="mt-1 text-3xl font-bold text-pharaoh-gold">{{ number_format($totalEntrees - $totalSorties, 0, ',', '.') }} FCFA</div>
        </div>
        <div class="text-right text-sm">
            <div><span class="text-gray-500">Entrées :</span> <span class="font-semibold text-green-700">{{ number_format($totalEntrees, 0, ',', '.') }} FCFA</span></div>
            <div><span class="text-gray-500">Sorties :</span> <span class="font-semibold text-red-600">{{ number_format($totalSorties, 0, ',', '.') }} FCFA</span></div>
        </div>
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
                <button type="submit" class="btn-danger w-full">💸 Enregistrer le retrait</button>
            </form>
        </div>
    </div>

    <!-- ═══ LISTE DES RETRAITS ═══ -->
    <div class="lg:col-span-2">
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-[#080808]">📋 Retraits enregistrés</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#080808] text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Heure</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Motif</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($retraits as $r)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $r->created_at->format('H:i') }}</td>
                            <td class="px-4 py-3 text-sm text-[#080808]">{{ $r->motif }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-right text-red-600">-{{ number_format($r->montant, 0, ',', '.') }} FCFA</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-12 text-center text-gray-400">
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
@endsection\n
