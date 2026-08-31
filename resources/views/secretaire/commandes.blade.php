@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Commandes — Photo</span></div>
@endsection

@section('navigation')
<a href="{{ route('secretaire.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Commandes</a>
<a href="{{ route('secretaire.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
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
                <p class="form-card-subtitle">Enregistrer une commande photo</p>
            </div>

            @if ($errors->any())
            <div class="bg-red-600/10 border-2 border-red-600 text-red-600 px-4 py-3 rounded-md mb-6">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('secretaire.commandes.store') }}" class="space-y-5">
                @csrf

                <div class="form-group">
                    <label for="type" class="input-label">Type de commande</label>
                    <select id="type" name="type" required class="select-field">
                        <option value="">— Choisir —</option>
                        @foreach($typesPhoto as $key => $label)
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
                    <textarea id="details" name="details" rows="2" class="input-field" placeholder="Ex: Shooting corporate 50 photos">{{ old('details') }}</textarea>
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
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-[#080808] flex items-center gap-2">
                    <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Commandes récentes</span>
                </h3>
                <div class="flex gap-2">
                    <button onclick="filterCommandes('all')" class="filter-btn active" data-filter="all">Toutes</button>
                    <button onclick="filterCommandes('active')" class="filter-btn" data-filter="active">Actives</button>
                    <button onclick="filterCommandes('annule')" class="filter-btn" data-filter="annule">Annulées</button>
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
                        <tr class="hover:bg-gray-50 transition-colors {{ $cmd->statut_paiement === 'annule' ? 'bg-red-50/50 opacity-60' : ($cmd->statut_paiement !== 'paye' ? 'bg-orange-50/30' : '') }}" data-status="{{ $cmd->statut_paiement }}">
                            <td class="px-4 py-3 text-sm font-medium text-[#080808]">#{{ $cmd->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $typesPhoto[$cmd->type] ?? $cmd->type }}</td>
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
                                    <a href="{{ route('secretaire.commandes.edit', $cmd->id) }}" class="text-pharaoh-gold hover:text-pharaoh-gold-light inline-block" title="Modifier">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('secretaire.commandes.annuler', $cmd->id) }}" onsubmit="return confirm('Annuler cette commande ? Le montant payé sera comptabilisé en négatif dans la caisse.')">
                                        @csrf
                                        <button type="submit" class="text-red-500 hover:text-red-700 inline-block" title="Annuler">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                @else
                                <span class="text-xs text-gray-400 italic">Annulé</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-10 w-10 text-gray-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0L12 17l-8-4" />
                                </svg>
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

@endsection

@section('mobile-navigation')
<a href="{{ route('secretaire.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium bg-pharaoh-gold text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('secretaire.services') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@push('styles')
<style>
.filter-btn {
    padding: 0.35rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600;
    border: 1px solid #E7E7E7; background: white; color: #555; cursor: pointer; transition: all 0.15s;
}
.filter-btn:hover { border-color: #D39B23; color: #D39B23; }
.filter-btn.active { background: #D39B23; color: white; border-color: #D39B23; }
</style>
@endpush

@push('scripts')
<script>
function filterCommandes(status) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('[data-filter="' + status + '"]').classList.add('active');
    document.querySelectorAll('table tbody tr[data-status]').forEach(row => {
        const s = row.getAttribute('data-status');
        if (status === 'all') { row.style.display = ''; }
        else if (status === 'active') { row.style.display = (s === 'annule') ? 'none' : ''; }
        else if (status === 'annule') { row.style.display = (s === 'annule') ? '' : 'none'; }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="statut_paiement"]');
    const montantInput = document.getElementById('montant');
    const montantPayeInput = document.getElementById('montant_paye');
    const montantPayeGroup = document.getElementById('montantPayeGroup');
    const resteInfo = document.getElementById('resteInfo');

    function updatePaiement() {
        const statut = document.querySelector('input[name="statut_paiement"]:checked')?.value;
        const montant = parseFloat(montantInput.value) || 0;

        if (statut === 'paye') {
            montantPayeInput.value = montant;
            montantPayeInput.readOnly = true;
            montantPayeGroup.classList.add('opacity-60');
            resteInfo.textContent = 'Le montant complet sera encaissé.';
        } else if (statut === 'a_payer') {
            montantPayeInput.value = 0;
            montantPayeInput.readOnly = true;
            montantPayeGroup.classList.add('opacity-60');
            resteInfo.textContent = 'Le paiement sera effectué à la livraison.';
        } else {
            montantPayeInput.readOnly = false;
            montantPayeGroup.classList.remove('opacity-60');
            const paye = parseFloat(montantPayeInput.value) || 0;
            const reste = montant - paye;
            resteInfo.textContent = reste > 0 ? 'Reste à encaisser : ' + reste.toLocaleString('fr-FR') + ' FCFA' : '';
        }
    }

    radios.forEach(r => r.addEventListener('change', updatePaiement));
    montantInput.addEventListener('input', function() {
        const statut = document.querySelector('input[name="statut_paiement"]:checked')?.value;
        if (statut === 'paye') montantPayeInput.value = this.value;
        updatePaiement();
    });
    montantPayeInput.addEventListener('input', updatePaiement);
    updatePaiement();
});
</script>
@endpush
