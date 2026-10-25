@extends('layouts.dashboard')

@section('title', 'Gestion des Congés & Absences')
@section('breadcrumb', 'Congés de l\'équipe')

@section('header')
    <div>
        <span>Gestion des Congés & Absences</span>
        <p class="console-subtitle">Demandes de votre équipe : à approuver ou à refuser.</p>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
@php
    $statutFilter = request('statut');
    $typeFilter = request('type');
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        @include('partials.dashboard.kpi', ['label' => 'Total demandes', 'value' => $stats['total'], 'meta' => 'Toutes périodes', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'En attente', 'value' => $stats['en_attente'], 'meta' => 'À traiter', 'tone' => 'warning'])
        @include('partials.dashboard.kpi', ['label' => 'Approuvés', 'value' => $stats['approuves'], 'meta' => 'Validés', 'tone' => 'success'])
        @include('partials.dashboard.kpi', ['label' => 'Refusés', 'value' => $stats['refuses'], 'meta' => 'Non accordés', 'tone' => 'danger'])
    </div>

    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Demandes de congé</h2>
                <p class="console-card-sub">Filtrez puis traitez les demandes de votre équipe.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 px-4 pt-4 sm:px-5">
            <a href="{{ route('superviseur.conges.index') }}" class="chip {{ !$statutFilter ? 'is-active' : '' }}">
                Tous
            </a>
            <a href="{{ route('superviseur.conges.index', ['statut' => 'en_attente']) }}" class="chip {{ $statutFilter === 'en_attente' ? 'is-active' : '' }}">
                En attente
            </a>
            <a href="{{ route('superviseur.conges.index', ['statut' => 'approuve']) }}" class="chip {{ $statutFilter === 'approuve' ? 'is-active' : '' }}">
                Approuvés
            </a>
            <a href="{{ route('superviseur.conges.index', ['statut' => 'refuse']) }}" class="chip {{ $statutFilter === 'refuse' ? 'is-active' : '' }}">
                Refusés
            </a>
        </div>

        <div class="table-scroll mt-4">
            <table class="min-w-full text-sm">
                <thead class="table-head">
                    <tr>
                        <th scope="col">Employé</th>
                        <th scope="col">Type</th>
                        <th scope="col">Période</th>
                        <th scope="col">Jours</th>
                        <th scope="col">Motif</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-body divide-y divide-gray-100 bg-white">
                    @forelse($conges as $conge)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $conge->employe->nom ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $conge->date_debut->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $conge->typeLabel() }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">
                            <span class="font-medium">{{ $conge->date_debut->format('d/m') }}</span>
                            →
                            <span class="font-medium">{{ $conge->date_fin->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-pharaoh-gold/10 text-sm font-bold text-pharaoh-gold">{{ $conge->dureeJours() }}</span>
                        </td>
                        <td class="max-w-xs px-4 py-3">
                            <p class="truncate text-gray-600" title="{{ $conge->motif }}">{{ $conge->motif }}</p>
                        </td>
                        <td class="px-4 py-3">{!! $conge->statutBadge() !!}</td>
                        <td class="px-4 py-3">
                            @if($conge->statut === 'en_attente')
                            <div class="flex items-center gap-2">
                                <form action="{{ route('superviseur.conges.approuver', $conge->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-success px-3 py-1.5 text-xs" onclick="return confirm('Approuver cette demande ?')">
                                        <svg class="mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Approuver</span>
                                    </button>
                                </form>
                                <button type="button" onclick="openRefusModal({{ $conge->id }})" class="btn-danger px-3 py-1.5 text-xs">
                                    <svg class="mr-1 h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span>Refuser</span>
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
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-sm font-semibold text-gray-900">Aucune demande</h3>
                                <p class="mt-1 text-sm text-gray-500">Aucune demande de congé ou d'absence.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($conges->hasPages())
        <div class="border-t border-gray-200 bg-gray-50 px-4 py-3">
            {{ $conges->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Refus -->
<div id="refusModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true" role="dialog" aria-labelledby="refusModalTitle">
    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeRefusModal()"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <h3 id="refusModalTitle" class="section-title mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Refuser la demande</span>
            </h3>
            <form id="refusForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label for="commentaire" class="input-label mb-1">Motif du refus *</label>
                    <textarea name="commentaire" id="commentaire" rows="3" required class="input-field" placeholder="Expliquez pourquoi cette demande est refusée..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRefusModal()" class="btn-secondary">Annuler</button>
                    <button type="submit" class="btn-danger">Confirmer le refus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
@endpush
