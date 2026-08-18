@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>État de la Caisse — Admin</span></div>
@endsection

@section('navigation')
<a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('admin.caisse') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Caisse</a>
<a href="{{ route('admin.stock') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Stock</a>
<a href="{{ route('admin.addEmployee') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Ajouter employé</a>
<a href="{{ route('admin.generateReport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Générer Bilan</a>
<a href="{{ route('admin.showEmployeeList') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Liste des employés</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('admin.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('admin.caisse') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">Caisse</a>
<a href="{{ route('admin.stock') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Stock</a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="page-heading-title mb-6">État des caisses — {{ now()->format('d/m/Y') }}</h1>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @forelse($data as $label => $info)
            <div class="pharaoh-card p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="rounded-full bg-pharaoh-gold/10 p-2 text-pharaoh-gold">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-[#080808]">{{ $info['nom'] }}</h3>
                </div>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="rounded-xl bg-green-50 p-4 text-center">
                        <div class="text-xl font-bold text-green-700">{{ number_format($info['entrees'], 0, ',', '.') }}</div>
                        <div class="text-xs text-green-600 mt-1">Entrées (FCFA)</div>
                    </div>
                    <div class="rounded-xl bg-red-50 p-4 text-center">
                        <div class="text-xl font-bold text-red-600">{{ number_format($info['sorties'], 0, ',', '.') }}</div>
                        <div class="text-xs text-red-500 mt-1">Sorties (FCFA)</div>
                    </div>
                    <div class="rounded-xl bg-[#FBF3E6] p-4 text-center">
                        <div class="text-xl font-bold text-pharaoh-gold">{{ number_format($info['chiffre_affaire'], 0, ',', '.') }}</div>
                        <div class="text-xs text-pharaoh-bronze mt-1">Chiffre affaire</div>
                    </div>
                </div>
                <p class="text-xs text-gray-400">Chiffre d'affaire journalier</p>
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
