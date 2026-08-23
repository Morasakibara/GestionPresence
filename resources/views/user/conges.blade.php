@extends('layouts.app')

@section('header', 'Mes Congés & Absences')

@section('navigation')
<a href="{{ route('user.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('user.conges') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Congés</a>
<a href="{{ route('presence.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Présence</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('user.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('user.conges') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white" aria-current="page">Congés</a>
<a href="{{ route('presence.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Présence</a>
@endsection

@section('content')
@php
    $userConges = \App\Models\Conge::where('employer_id', auth()->id())->orderByDesc('created_at')->get();
@endphp

<!-- Stats personnelles -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="stat-card border border-white/20 bg-gradient-to-br from-white/90 to-white/70 text-center">
        <div class="stat-card-value">{{ $userConges->count() }}</div>
        <div class="stat-card-label">Total demandes</div>
    </div>
    <div class="stat-card border border-emerald-300/30 bg-gradient-to-br from-emerald-50/90 to-emerald-50/70 text-center">
        <div class="stat-card-value text-emerald-600">{{ $userConges->where('statut', 'approuve')->count() }}</div>
        <div class="stat-card-label">Approuvées</div>
    </div>
    <div class="stat-card border border-amber-300/30 bg-gradient-to-br from-amber-50/90 to-amber-50/70 text-center">
        <div class="stat-card-value text-amber-600">{{ $userConges->where('statut', 'en_attente')->count() }}</div>
        <div class="stat-card-label">En attente</div>
    </div>
</div>

<!-- Formulaire de demande -->
<div class="card mb-6">
    <h2 class="text-lg font-bold text-[#080808] mb-4 flex items-center gap-2">
        <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        <span>Nouvelle demande</span>
    </h2>
    <form action="{{ route('user.conges.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-4">
            <div>
                <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">Date de début *</label>
                <input type="date" name="date_debut" id="date_debut" required min="{{ now()->format('Y-m-d') }}" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-pharaoh-gold focus:ring-2 focus:ring-pharaoh-gold/20 transition-all">
            </div>
            <div>
                <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">Date de fin *</label>
                <input type="date" name="date_fin" id="date_fin" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-pharaoh-gold focus:ring-2 focus:ring-pharaoh-gold/20 transition-all">
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                <select name="type" id="type" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-pharaoh-gold focus:ring-2 focus:ring-pharaoh-gold/20 transition-all">
                    <option value="conge">Congé</option>
                    <option value="absence_justifiee">Absence justifiée</option>
                    <option value="absence_injustifiee">Absence injustifiée</option>
                </select>
            </div>
        </div>
        <div class="mb-4">
            <label for="motif" class="block text-sm font-medium text-gray-700 mb-1">Motif *</label>
            <textarea name="motif" id="motif" rows="3" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-pharaoh-gold focus:ring-2 focus:ring-pharaoh-gold/20 transition-all" placeholder="Décrivez le motif de votre demande..."></textarea>
        </div>
        <button type="submit" class="btn-gold btn-press rounded-xl px-6 py-2.5 text-sm font-semibold" onclick="return confirm('Envoyer cette demande ?')">
            Soumettre la demande
        </button>
    </form>
</div>

<!-- Historique -->
<div class="card">
    <h2 class="text-lg font-bold text-[#080808] mb-4 flex items-center gap-2">
        <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Historique de mes demandes</span>
    </h2>
    @if($userConges->isEmpty())
        <p class="text-center text-gray-500 py-8">Aucune demande pour le moment.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Type</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Période</th>
                    <th class="px-3 py-2 text-center font-semibold text-gray-600">Jours</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Motif</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($userConges as $c)
                <tr class="hover:bg-pharaoh-gold/5">
                    <td class="px-3 py-2">{{ $c->typeLabel() }}</td>
                    <td class="px-3 py-2">{{ $c->date_debut->format('d/m/Y') }} → {{ $c->date_fin->format('d/m/Y') }}</td>
                    <td class="px-3 py-2 text-center font-bold text-pharaoh-gold">{{ $c->dureeJours() }}</td>
                    <td class="px-3 py-2 max-w-xs truncate" title="{{ $c->motif }}">{{ $c->motif }}</td>
                    <td class="px-3 py-2">{!! $c->statutBadge() !!}</td>
                </tr>
                @if($c->commentaire_admin)
                <tr class="bg-gray-50">
                    <td colspan="5" class="px-3 py-2">
                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                            <svg class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span>Réponse du superviseur : {{ $c->commentaire_admin }}</span>
                        </div>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
