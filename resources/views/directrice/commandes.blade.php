@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Commandes — Imprimerie</span>
    <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center px-2 py-1 text-sm font-medium text-pharaoh-gold hover:bg-white/10 rounded-md">
        <svg class="h-6 w-6 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
    </a>
</div>
@endsection

@section('navigation')
<a href="{{ route('directrice.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Commandes</a>
<a href="{{ route('directrice.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif
@if(session('error'))<div class="alert alert-danger"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ═══ FORMULAIRE ═══ -->
    <div class="lg:col-span-1">
        <div class="form-card">
            <div class="form-card-header">
                <h2 class="form-card-title">Nouvelle commande</h2>
                <p class="form-card-subtitle">Enregistrer une commande d'imprimerie</p>
            </div>

            @if ($errors->any())
            <div class="bg-red-600/10 border-2 border-red-600 text-red-600 px-4 py-3 rounded-md mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('directrice.commandes.store') }}" class="space-y-5">
                @csrf

                <div class="form-group">
                    <label for="type" class="input-label">Type de commande</label>
                    <select id="type" name="type" required class="select-field">
                        <option value="">— Choisir —</option>
                        @foreach($typesCaisse as $key => $label)
                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="montant" class="input-label">Montant total (FCFA)</label>
                    <input type="number" id="montant" name="montant" min="1" step="any" value="{{ old('montant') }}" required class="input-field" placeholder="0">
                </div>

                <div class="form-group">
                    <label class="input-label">Statut du paiement</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-3 cursor-pointer hover:border-pharaoh-gold/50 transition-colors has-[:checked]:border-pharaoh-gold has-[:checked]:bg-[#FBF3E6]">
                            <input type="radio" name="statut_paiement" value="paye" {{ old('statut_paiement', 'paye') === 'paye' ? 'checked' : '' }} class="text-pharaoh-gold focus:ring-pharaoh-gold">
                            <span class="text-sm font-medium text-[#080808] flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                <span>Payé — montant complet encaissé</span>
                            </span>
                        </label>
                        <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-3 cursor-pointer hover:border-orange-400/50 transition-colors has-[:checked]:border-orange-400 has-[:checked]:bg-orange-50">
                            <input type="radio" name="statut_paiement" value="partiel" {{ old('statut_paiement') === 'partiel' ? 'checked' : '' }} class="text-orange-500 focus:ring-orange-400">
                            <span class="text-sm font-medium text-[#080808] flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-yellow-500"></span>
                                <span>Partiel — versement partiel</span>
                            </span>
                        </label>
                        <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-3 cursor-pointer hover:border-red-400/50 transition-colors has-[:checked]:border-red-400 has-[:checked]:bg-red-50">
                            <input type="radio" name="statut_paiement" value="a_payer" {{ old('statut_paiement') === 'a_payer' ? 'checked' : '' }} class="text-red-500 focus:ring-red-400">
                            <span class="text-sm font-medium text-[#080808] flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                <span>À payer — à la livraison</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="form-group" id="montantPayeGroup">
                    <label for="montant_paye" class="input-label">Montant payé (FCFA)</label>
                    <input type="number" id="montant_paye" name="montant_paye" min="0" step="any" value="{{ old('montant_paye') }}" class="input-field" placeholder="0">
                    <p class="text-xs text-gray-400 mt-1" id="resteInfo"></p>
                </div>

                <div class="form-group">
                    <label for="details" class="input-label">Détails (facultatif)</label>
                    <textarea id="details" name="details" rows="2" class="input-field" placeholder="Ex: 200 pages A4 noir et blanc">{{ old('details') }}</textarea>
                </div>

                <button type="submit" class="btn-primary mt-2 gap-2">
                    <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h-2v5.586L7.707 10.293z" />
                        <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V8l-4-4H4zm12 12A2 2 0 0114 16H6a2 2 0 01-2-2V6a2 2 0 012-2h7v4h4v6z" clip-rule="evenodd" />
                    </svg>
                    <span>Enregistrer la commande</span>
                </button>
            </form>
        </div>
    </div>

    <!-- ═══ LISTE DES COMMANDES ═══ -->
    <div class="lg:col-span-2">
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-[#080808] flex items-center gap-2">
                        <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>Commandes récentes</span>
                    </h3>
                    <div class="flex gap-2">
                        <button onclick="filterStatut('all')" class="filter-btn active" data-filter="all">Toutes</button>
                        <button onclick="filterStatut('active')" class="filter-btn" data-filter="active">Actives</button>
                        <button onclick="filterStatut('annule')" class="filter-btn" data-filter="annule">Annulées</button>
                    </div>
                </div>
                <!-- Filtres avancés -->
                <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-gray-100">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Du</label>
                        <input type="date" id="filterDateDebut" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold" onchange="applyAllFilters()">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Au</label>
                        <input type="date" id="filterDateFin" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold" onchange="applyAllFilters()">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                        <select id="filterType" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold" onchange="applyAllFilters()">
                            <option value="">Tous les types</option>
                            @foreach($typesCaisse as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button onclick="resetFilters()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-500 hover:bg-gray-100 transition-colors">Réinitialiser</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#080808] text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Payé</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Date</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($commandes as $cmd)
                        <tr class="hover:bg-gray-50 transition-colors {{ $cmd->statut_paiement === 'annule' ? 'bg-red-50/50 opacity-60' : ($cmd->statut_paiement !== 'paye' ? 'bg-orange-50/30' : '') }}"
                            data-status="{{ $cmd->statut_paiement }}"
                            data-date="{{ $cmd->date }}"
                            data-type="{{ $cmd->type }}">
                            <td class="px-4 py-3 text-sm font-medium text-[#080808]">#{{ $cmd->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $typesCaisse[$cmd->type] ?? $cmd->type }}</td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-[#080808]">{{ number_format($cmd->montant, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-right {{ $cmd->montant_paye > 0 ? 'text-green-600 font-medium' : 'text-gray-400' }}">{{ number_format($cmd->montant_paye, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $cmd->statut_paiement === 'paye' ? 'badge-success' : ($cmd->statut_paiement === 'annule' ? 'bg-red-100 text-red-700' : ($cmd->statut_paiement === 'partiel' ? 'badge-warning' : 'badge-danger')) }}">
                                    {{ $cmd->statut_paiement === 'paye' ? 'Payé' : ($cmd->statut_paiement === 'annule' ? 'Annulé' : ($cmd->statut_paiement === 'partiel' ? 'Partiel' : 'À payer')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $cmd->date }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($cmd->statut_paiement !== 'annule')
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('directrice.commandes.edit', $cmd->id) }}" class="text-pharaoh-gold hover:text-pharaoh-gold-light inline-block" title="Modifier">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </a>
                                    <button type="button" onclick="showAnnulerModal({{ $cmd->id }}, '{{ $typesCaisse[$cmd->type] ?? $cmd->type }}', {{ $cmd->montant }}, {{ $cmd->montant_paye }}, 'directrice')" class="text-red-500 hover:text-red-700 inline-block" title="Annuler">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                @else
                                <span class="text-xs text-gray-400 italic">Annulé</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-10 w-10 text-gray-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0L12 17l-8-4" /></svg>
                                <span class="text-sm">Aucune commande pour le moment.</span>
                            </div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════ MODAL ANNULATION COMMANDE ═══════════ -->
<div id="annulerModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" onclick="event.stopPropagation()">
        <div class="bg-[#080808] px-6 py-5 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-red-500/10 flex items-center justify-center mb-3">
                <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white">Annuler la commande</h3>
            <p class="text-sm text-gray-400 mt-1">Cette action est irréversible</p>
        </div>
        <div class="px-6 py-5">
            <div id="annulerDetails" class="bg-gray-50 rounded-xl p-4 mb-4 space-y-2">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Commande</span><span class="font-semibold text-[#080808]" id="annulerCmdId"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Type</span><span class="font-semibold text-[#080808]" id="annulerCmdType"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Montant total</span><span class="font-semibold text-[#080808]" id="annulerCmdMontant"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Déjà payé</span><span class="font-semibold text-orange-600" id="annulerCmdPaye"></span></div>
            </div>
            <div id="annulerWarning" class="hidden bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4">
                <div class="flex items-start gap-2">
                    <svg class="h-5 w-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/></svg>
                    <p class="text-sm text-red-700">Un remboursement de <strong id="annulerRembours"></strong> sera comptabilisé en négatif dans la caisse.</p>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-4">Êtes-vous sûr de vouloir annuler cette commande ?</p>
            <form id="annulerForm" method="POST">
                @csrf
                <div class="flex gap-3">
                    <button type="button" onclick="closeAnnulerModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">Non, garder</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 text-sm font-semibold text-white hover:bg-red-700 transition-colors shadow-lg shadow-red-600/20">Oui, annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('mobile-navigation')
<a href="{{ route('directrice.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="block rounded-md px-3 py-2 text-base font-medium bg-pharaoh-gold text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@push('styles')
<style>
.filter-btn { padding: 0.35rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; border: 1px solid #E7E7E7; background: white; color: #555; cursor: pointer; transition: all 0.15s; }
.filter-btn:hover { border-color: #D39B23; color: #D39B23; }
.filter-btn.active { background: #D39B23; color: white; border-color: #D39B23; }
</style>
@endpush

@push('scripts')
<script>
// ═══ MODAL ANNULATION ═══
function showAnnulerModal(id, type, montant, paye, module) {
    const modal = document.getElementById('annulerModal');
    document.getElementById('annulerCmdId').textContent = '#' + id;
    document.getElementById('annulerCmdType').textContent = type;
    document.getElementById('annulerCmdMontant').textContent = Number(montant).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('annulerCmdPaye').textContent = Number(paye).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('annulerForm').action = '/' + module + '/commandes/' + id + '/annuler';
    const warning = document.getElementById('annulerWarning');
    const rembours = document.getElementById('annulerRembours');
    if (paye > 0) { warning.classList.remove('hidden'); rembours.textContent = Number(paye).toLocaleString('fr-FR') + ' FCFA'; }
    else { warning.classList.add('hidden'); }
    modal.classList.remove('hidden'); modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeAnnulerModal() {
    const modal = document.getElementById('annulerModal');
    modal.classList.add('hidden'); modal.classList.remove('flex');
    document.body.style.overflow = '';
}
document.addEventListener('click', function(e) { if (e.target.id === 'annulerModal') closeAnnulerModal(); });
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeAnnulerModal(); });

// ═══ FILTRES AVANCÉS ═══
let currentStatut = 'all';
function filterStatut(status) {
    currentStatut = status;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('[data-filter="' + status + '"]').classList.add('active');
    applyAllFilters();
}
function applyAllFilters() {
    const dateDebut = document.getElementById('filterDateDebut').value;
    const dateFin = document.getElementById('filterDateFin').value;
    const typeFilter = document.getElementById('filterType').value;
    document.querySelectorAll('table tbody tr[data-status]').forEach(row => {
        const s = row.getAttribute('data-status');
        const d = row.getAttribute('data-date');
        const t = row.getAttribute('data-type');
        let show = true;
        // Statut
        if (currentStatut === 'active' && s === 'annule') show = false;
        if (currentStatut === 'annule' && s !== 'annule') show = false;
        // Date
        if (dateDebut && d < dateDebut) show = false;
        if (dateFin && d > dateFin) show = false;
        // Type
        if (typeFilter && t !== typeFilter) show = false;
        row.style.display = show ? '' : 'none';
    });
}
function resetFilters() {
    document.getElementById('filterDateDebut').value = '';
    document.getElementById('filterDateFin').value = '';
    document.getElementById('filterType').value = '';
    filterStatut('all');
}

// ═══ FORMULAIRE PAIEMENT ═══
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="statut_paiement"]');
    const montantInput = document.getElementById('montant');
    const montantPayeInput = document.getElementById('montant_paye');
    const montantPayeGroup = document.getElementById('montantPayeGroup');
    const resteInfo = document.getElementById('resteInfo');
    function updatePaiement() {
        const statut = document.querySelector('input[name="statut_paiement"]:checked')?.value;
        const montant = parseFloat(montantInput.value) || 0;
        if (statut === 'paye') { montantPayeInput.value = montant; montantPayeInput.readOnly = true; montantPayeGroup.classList.add('opacity-60'); resteInfo.textContent = 'Le montant complet sera encaissé.'; }
        else if (statut === 'a_payer') { montantPayeInput.value = 0; montantPayeInput.readOnly = true; montantPayeGroup.classList.add('opacity-60'); resteInfo.textContent = 'Le paiement sera effectué à la livraison.'; }
        else { montantPayeInput.readOnly = false; montantPayeGroup.classList.remove('opacity-60'); const paye = parseFloat(montantPayeInput.value) || 0; const reste = montant - paye; resteInfo.textContent = reste > 0 ? 'Reste à encaisser : ' + reste.toLocaleString('fr-FR') + ' FCFA' : ''; }
    }
    radios.forEach(r => r.addEventListener('change', updatePaiement));
    montantInput.addEventListener('input', function() { const statut = document.querySelector('input[name="statut_paiement"]:checked')?.value; if (statut === 'paye') montantPayeInput.value = this.value; updatePaiement(); });
    montantPayeInput.addEventListener('input', updatePaiement);
    updatePaiement();
});
</script>
@endpush
