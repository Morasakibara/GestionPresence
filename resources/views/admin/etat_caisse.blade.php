@extends('layouts.dashboard')

@section('title', 'État des caisses')
@section('breadcrumb', 'État des caisses')

@section('header')
    <div>
        <span>État des caisses</span>
        <p class="console-subtitle">Entrées, sorties et chiffre d'affaires par caisse, pour la journée.</p>
    </div>
    <span class="console-kicker mt-0">{{ now()->format('d/m/Y') }}</span>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="space-y-5">
    {{-- Filtre de date : par défaut aujourd'hui, l'admin peut remonter le temps. --}}
    <form method="GET" action="{{ route('admin.caisse') }}" class="console-card flex flex-wrap items-center gap-3 p-4">
        <label for="date" class="text-sm font-medium text-gray-600">Journée du</label>
        <input type="date" id="date" name="date" value="{{ $date }}"
               class="input-field w-auto">
        <button type="submit" class="btn-gold">Afficher</button>
        <a href="{{ route('admin.caisse') }}" class="btn-secondary">Aujourd'hui</a>
        <span class="ml-auto text-xs text-gray-400">Cumul historique affiché sous chaque total du jour.</span>
    </form>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        @forelse($data as $label => $info)
            <div class="console-card p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="rounded-full bg-pharaoh-gold/10 p-2 text-pharaoh-gold">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="section-title">{{ $info['nom'] }}</h3>
                </div>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="rounded-xl bg-green-50 p-4 text-center">
                        <div class="text-xl font-bold text-green-700">{{ number_format($info['entrees'], 0, ',', '.') }}</div>
                        <div class="text-xs text-green-600 mt-1">Entrées (FCFA)</div>
                        <div class="text-[11px] text-green-500/80 mt-1">Cumul : {{ number_format($info['entrees_total'], 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-xl bg-red-50 p-4 text-center">
                        <div class="text-xl font-bold text-red-600">{{ number_format($info['sorties'], 0, ',', '.') }}</div>
                        <div class="text-xs text-red-500 mt-1">Sorties (FCFA)</div>
                        <div class="text-[11px] text-red-500/80 mt-1">Cumul : {{ number_format($info['sorties_total'], 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-xl bg-[#FBF3E6] p-4 text-center">
                        <div class="text-xl font-bold text-pharaoh-gold">{{ number_format($info['chiffre_affaire'], 0, ',', '.') }}</div>
                        <div class="text-xs text-pharaoh-bronze mt-1">Chiffre affaire</div>
                        <div class="text-[11px] text-pharaoh-bronze/80 mt-1">Cumul : {{ number_format($info['chiffre_affaire_total'], 0, ',', '.') }}</div>
                    </div>
                </div>
                <p class="text-xs text-gray-400">Chiffre d'affaire du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
            </div>
        @empty
            <div class="col-span-2">
                <div class="empty-state">
                    <div class="empty-state-icon"><svg class="h-7 w-7 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg></div>
                    <p class="mt-3 text-sm text-gray-500">Aucune donnée de caisse disponible.<br>Créez un superviseur directrice ou secrétaire pour commencer.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
