@extends('layouts.app')

@section('header', 'Gestion des Congés & Absences')

@section('navigation')
<a href="{{ route('superviseur.supdashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('superviseur.conges.index') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Congés</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Présences</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('superviseur.supdashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('superviseur.conges.index') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white" aria-current="page">Congés</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Présences</a>
@endsection

@section('content')
@php
    $statutFilter = request('statut');
    $typeFilter = request('type');
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

<!-- Filtres -->
<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="{{ route('superviseur.conges.index') }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ !$statutFilter ? 'bg-pharaoh-gold text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        Tous
    </a>
    <a href="{{ route('superviseur.conges.index', ['statut' => 'en_attente']) }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ $statutFilter === 'en_attente' ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        ⏳ En attente
    </a>
    <a href="{{ route('superviseur.conges.index', ['statut' => 'approuve']) }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ $statutFilter === 'approuve' ? 'bg-emerald-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        ✅ Approuvés
    </a>
    <a href="{{ route('superviseur.conges.index', ['statut' => 'refuse']) }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ $statutFilter === 'refuse' ? 'bg-red-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        ❌ Refusés
    </a>
</div>

<!-- Tableau des demandes -->
<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-[#080808] text-white">
                <tr>
                    <th class="px-4 py-3 font-semibold">Employé</th>
                    <th class="px-4 py-3 font-semibold">Type</th>
                    <th class="px-4 py-3 font-semibold">Période</th>
                    <th class="px-4 py-3 font-semibold">Jours</th>
                    <th class="px-4 py-3 font-semibold">Motif</th>
                    <th class="px-4 py-3 font-semibold">Statut</th>
                    <th class="px-4 py-3 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($conges as $conge)
                <tr class="hover:bg-pharaoh-gold/5 transition-colors">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">{{ $conge->employe->nom ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">{{ $conge->date_debut->format('d/m/Y') }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm">{{ $conge->typeLabel() }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="font-medium">{{ $conge->date_debut->format('d/m') }}</span>
                        →
                        <span class="font-medium">{{ $conge->date_fin->format('d/m/Y') }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-pharaoh-gold/10 text-sm font-bold text-pharaoh-gold">{{ $conge->dureeJours() }}</span>
                    </td>
                    <td class="px-4 py-3 max-w-xs">
                        <p class="text-sm text-gray-600 truncate" title="{{ $conge->motif }}">{{ $conge->motif }}</p>
                    </td>
                    <td class="px-4 py-3">{!! $conge->statutBadge() !!}</td>
                    <td class="px-4 py-3">
                        @if($conge->statut === 'en_attente')
                        <div class="flex items-center gap-2">
                            <form action="{{ route('superviseur.conges.approuver', $conge->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600 transition-colors" onclick="return confirm('Approuver cette demande ?')">
                                    ✅ Approuver
                                </button>
                            </form>
                            <button onclick="openRefusModal({{ $conge->id }})" class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-600 transition-colors">
                                ❌ Refuser
                            </button>
                        </div>
                        @elseif($conge->statut === 'refuse')
                        <span class="text-xs text-gray-500" title="{{ $conge->commentaire_admin }}">{{ \Illuminate\Support\Str::limit($conge->commentaire_admin ?? '', 30) }}</span>
                        @else
                        <span class="text-xs text-gray-400">Traité</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                        <div class="text-4xl mb-2">🏖️</div>
                        Aucune demande de congé ou d'absence.
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

<!-- Modal Refus -->
<div id="refusModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeRefusModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold text-[#080808] mb-4">❌ Refuser la demande</h3>
            <form id="refusForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label for="commentaire" class="block text-sm font-medium text-gray-700 mb-1">Motif du refus *</label>
                    <textarea name="commentaire" id="commentaire" rows="3" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20 transition-all" placeholder="Expliquez pourquoi cette demande est refusée..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRefusModal()" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button>
                    <button type="submit" class="rounded-xl bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">Confirmer le refus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRefusModal(id) {
    document.getElementById('refusForm').action = '/superviseur/conges/' + id + '/refuser';
    document.getElementById('refusModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}
function closeRefusModal() {
    document.getElementById('refusModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}
</script>
@endsection
