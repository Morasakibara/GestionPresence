@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Dashboard Directrice — État de la Caisse</span>
    <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center px-2 py-1 text-sm font-medium text-pharaoh-gold hover:bg-white/10 rounded-md">
        <svg class="h-6 w-6 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
    </a>
</div>
@endsection

@section('navigation')
<a href="{{ route('directrice.dashboard') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
<a href="{{ route('superviseur.supdashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Équipe</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('directrice.dashboard') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>
@endif
@if(session('error'))
    <div class="alert alert-danger"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>
@endif

<!-- En-tête -->
<div class="mb-6">
    <h2 class="text-lg font-semibold text-[#080808]">État de la caisse — {{ now()->format('d/m/Y') }}</h2>
    <p class="text-sm text-gray-500">Entrées, sorties et somme en caisse du jour</p>
</div>

<!-- Cartes résumé -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="stat-card">
        <div class="stat-card-icon bg-green-50 text-green-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div class="stat-card-value text-green-600">{{ number_format($totalCommandes, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label">Total Commandes</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon bg-blue-50 text-blue-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
        </div>
        <div class="stat-card-value text-blue-600">{{ number_format($totalServices, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label">Total Services</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon bg-red-50 text-red-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
        <div class="stat-card-value text-red-600">{{ number_format($totalRetraits, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label">Total Retraits</div>
    </div>
    <div class="stat-card border-pharaoh-gold/30 bg-gradient-to-br from-[#FBF3E6] to-white">
        <div class="stat-card-icon bg-pharaoh-gold/10 text-pharaoh-gold">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
        <div class="stat-card-value text-pharaoh-gold">{{ number_format($sommeEnCaisse, 0, ',', '.') }} FCFA</div>
        <div class="stat-card-label font-semibold">Somme en caisse</div>
    </div>
</div>

<!-- Répartition par type -->
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
    <!-- Commandes par type -->
    <div class="pharaoh-card p-6">
        <h3 class="text-lg font-semibold text-[#080808] mb-4">Commandes par type</h3>
        @forelse($commandesParType as $type => $montant)
            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <span class="text-sm text-gray-700 capitalize">{{ $typesCaisse[$type] ?? $type }}</span>
                <span class="text-sm font-semibold text-[#080808]">{{ number_format($montant, 0, ',', '.') }} FCFA</span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucune commande aujourd'hui</p>
        @endforelse
    </div>
    <!-- Services par type -->
    <div class="pharaoh-card p-6">
        <h3 class="text-lg font-semibold text-[#080808] mb-4">Services par type</h3>
        @forelse($servicesParType as $type => $montant)
            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                <span class="text-sm text-gray-700 capitalize">{{ $typesCaisse[$type] ?? $type }}</span>
                <span class="text-sm font-semibold text-[#080808]">{{ number_format($montant, 0, ',', '.') }} FCFA</span>
            </div>
        @empty
            <p class="text-sm text-gray-400">Aucun service aujourd'hui</p>
        @endforelse
    </div>
</div>

<!-- Accès rapides -->
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('directrice.commandes') }}" class="pharaoh-card btn-press flex flex-col items-center p-6 hover:shadow-lg transition-shadow">
        <div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        </div>
        <h2 class="mt-3 text-base font-medium text-gray-900">Nouvelle Commande</h2>
    </a>
    <a href="{{ route('directrice.services') }}" class="pharaoh-card btn-press flex flex-col items-center p-6 hover:shadow-lg transition-shadow">
        <div class="rounded-full bg-blue-50 p-3 text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
        </div>
        <h2 class="mt-3 text-base font-medium text-gray-900">Nouveau Service</h2>
    </a>
    <a href="{{ route('directrice.retraits') }}" class="pharaoh-card btn-press flex flex-col items-center p-6 hover:shadow-lg transition-shadow">
        <div class="rounded-full bg-red-50 p-3 text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
        <h2 class="mt-3 text-base font-medium text-gray-900">Retrait</h2>
    </a>
    <a href="{{ route('directrice.rapport') }}" class="pharaoh-card btn-press flex flex-col items-center p-6 hover:shadow-lg transition-shadow">
        <div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
        </div>
        <h2 class="mt-3 text-base font-medium text-gray-900">Rapport</h2>
    </a>
</div>
@endsection
