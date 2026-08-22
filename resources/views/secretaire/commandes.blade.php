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
                            <span class="text-sm font-medium text-[#080808]">✅ Payé — montant complet encaissé</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-3 cursor-pointer hover:border-orange-400/50 transition-colors has-[:checked]:border-orange-400 has-[:checked]:bg-orange-50">
                            <input type="radio" name="statut_paiement" value="partiel" {{ old('statut_paiement') === 'partiel' ? 'checked' : '' }} class="text-orange-500 focus:ring-orange-400">
                            <span class="text-sm font-medium text-[#080808]">🟠 Partiel — versement partiel</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-3 cursor-pointer hover:border-red-400/50 transition-colors has-[:checked]:border-red-400 has-[:checked]:bg-red-50">
                            <input type="radio" name="statut_paiement" value="a_payer" {{ old('statut_paiement') === 'a_payer' ? 'checked' : '' }} class="text-red-500 focus:ring-red-400">
                            <span class="text-sm font-medium text-[#080808]">🔴 À payer — à la livraison</span>
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

                <button type="submit" class="btn-primary mt-2">
                    💾 Enregistrer la commande
                </button>
            </form>
        </div>
    </div>

    <!-- ═══ LISTE DES COMMANDES ═══ -->
    <div class="lg:col-span-2">
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-[#080808]">📋 Commandes récentes</h3>
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
                        <tr class="hover:bg-gray-50 transition-colors {{ $cmd->statut_paiement !== 'paye' ? 'bg-orange-50/30' : '' }}">
                            <td class="px-4 py-3 text-sm font-medium text-[#080808]">#{{ $cmd->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $typesPhoto[$cmd->type] ?? $cmd->type }}</td>
                            <td class="px-4 py-3 text-sm text-right font-medium text-[#080808]">{{ number_format($cmd->montant, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-right {{ $cmd->montant_paye > 0 ? 'text-green-600 font-medium' : 'text-gray-400' }}">{{ number_format($cmd->montant_paye, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $cmd->statut_paiement === 'paye' ? 'badge-success' : ($cmd->statut_paiement === 'partiel' ? 'badge-warning' : 'badge-danger') }}">
                                    {{ $cmd->statut_paiement === 'paye' ? '✅ Payé' : ($cmd->statut_paiement === 'partiel' ? '🟠 Partiel' : '🔴 À payer') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $cmd->date }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('secretaire.commandes.edit', $cmd->id) }}" class="text-pharaoh-gold hover:text-pharaoh-gold-light text-sm font-medium">✏️</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">📭</div>Aucune commande pour le moment.
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
@endsection\n

@push('scripts')
<script>
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
