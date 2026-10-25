@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Modifier la commande #{{ $commande->id }}</span>
</div>
@endsection

@section('content')

@php
    $montantTotal = $commande->montant;
    $montantPaye = $commande->montant_paye ?? 0;
    $reste = $montantTotal - $montantPaye;
@endphp

<div class="flex justify-center">
    <div class="form-card w-full max-w-lg">
        <div class="form-card-header">
            <h2 class="form-card-title">Modifier la commande</h2>
            <p class="form-card-subtitle">Mise à jour du paiement — Montant total fixé</p>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <!-- Résumé commande -->
        <div class="mb-6 rounded-xl border-2 border-pharaoh-gold/30 bg-[#FBF3E6] p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Montant total</span>
                <span class="text-lg font-bold text-[#080808]">{{ number_format($montantTotal, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Déjà payé</span>
                <span class="text-lg font-bold text-emerald-600">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="border-t border-pharaoh-gold/30 pt-2 flex items-center justify-between">
                <span class="text-sm font-bold text-gray-800">Reste à payer</span>
                <span class="text-lg font-bold {{ $reste > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ number_format($reste, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <form method="POST" action="{{ route('directrice.commandes.update', $commande->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="type" class="input-label">Type de commande</label>
                <select id="type" name="type" required class="select-field">
                    @foreach($typesCaisse as $key => $label)
                        <option value="{{ $key }}" {{ old('type', $commande->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="input-label">Montant total (FCFA)</label>
                <input type="text" value="{{ number_format($montantTotal, 0, ',', ' ') }} FCFA" readonly class="input-field bg-gray-100 cursor-not-allowed text-gray-600">
                <input type="hidden" name="montant" value="{{ $montantTotal }}">
                <p class="text-xs text-gray-400 mt-1">🔒 Le montant total ne peut plus être modifié</p>
            </div>

            <div class="form-group">
                <label class="input-label">Statut du paiement</label>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-3 cursor-pointer hover:border-pharaoh-gold/50 transition-colors has-[:checked]:border-pharaoh-gold has-[:checked]:bg-[#FBF3E6]">
                        <input type="radio" name="statut_paiement" value="paye" {{ old('statut_paiement', $commande->statut_paiement) === 'paye' ? 'checked' : '' }} class="text-pharaoh-gold focus:ring-pharaoh-gold">
                        <span class="text-sm font-medium text-[#080808]">✅ Payé — montant complet encaissé</span>
                    </label>
                    <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-3 cursor-pointer hover:border-orange-400/50 transition-colors has-[:checked]:border-orange-400 has-[:checked]:bg-orange-50">
                        <input type="radio" name="statut_paiement" value="partiel" {{ old('statut_paiement', $commande->statut_paiement) === 'partiel' ? 'checked' : '' }} class="text-orange-500 focus:ring-orange-400">
                        <span class="text-sm font-medium text-[#080808]">🟠 Partiel — versement partiel</span>
                    </label>
                    <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-3 cursor-pointer hover:border-red-400/50 transition-colors has-[:checked]:border-red-400 has-[:checked]:bg-red-50">
                        <input type="radio" name="statut_paiement" value="a_payer" {{ old('statut_paiement', $commande->statut_paiement) === 'a_payer' ? 'checked' : '' }} class="text-red-500 focus:ring-red-400">
                        <span class="text-sm font-medium text-[#080808]">🔴 À payer — à la livraison</span>
                    </label>
                    <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-3 cursor-pointer hover:border-red-600/50 transition-colors has-[:checked]:border-red-600 has-[:checked]:bg-red-100">
                        <input type="radio" name="statut_paiement" value="annule" {{ old('statut_paiement', $commande->statut_paiement) === 'annule' ? 'checked' : '' }} class="text-red-700 focus:ring-red-600">
                        <span class="text-sm font-medium text-[#080808]">❌ Annulé — commande annulée</span>
                    </label>
                </div>
            </div>

            <div class="form-group" id="montantPayeGroup">
                <label for="montant_paye" class="input-label">Montant payé (FCFA)</label>
                <input type="number" id="montant_paye" name="montant_paye" min="0" max="{{ $montantTotal }}" step="any" value="{{ old('montant_paye', $montantPaye) }}" class="input-field">
                <p class="text-xs text-gray-500 mt-1" id="resteInfo">
                    @if($reste > 0)
                        Reste à payer : <strong class="text-red-600">{{ number_format($reste, 0, ',', ' ') }} FCFA</strong>
                    @else
                        <span class="text-emerald-600 font-bold">✅ Solde complet</span>
                    @endif
                </p>
            </div>

            <div class="form-group">
                <label for="details" class="input-label">Détails (facultatif)</label>
                <textarea id="details" name="details" rows="2" class="input-field">{{ old('details', $commande->details) }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 gap-2">
                    <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h-2v5.586L7.707 10.293z" />
                        <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V8l-4-4H4zm12 12A2 2 0 0114 16H6a2 2 0 01-2-2V6a2 2 0 012-2h7v4h4v6z" clip-rule="evenodd" />
                    </svg>
                    <span>Enregistrer</span>
                </button>
                <a href="{{ route('directrice.commandes') }}" class="btn-secondary flex-1 text-center">Annuler</a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="statut_paiement"]');
    const montantTotal = {{ $montantTotal }};
    const montantPayeInput = document.getElementById('montant_paye');
    const montantPayeGroup = document.getElementById('montantPayeGroup');
    const resteInfo = document.getElementById('resteInfo');

    function updatePaiement() {
        const statut = document.querySelector('input[name="statut_paiement"]:checked')?.value;
        if (statut === 'paye') {
            montantPayeInput.value = montantTotal;
            montantPayeInput.readOnly = true;
            montantPayeGroup.classList.add('opacity-60');
            resteInfo.innerHTML = '<span class="text-emerald-600 font-bold">✅ Solde complet</span>';
        } else if (statut === 'a_payer') {
            montantPayeInput.value = 0;
            montantPayeInput.readOnly = true;
            montantPayeGroup.classList.add('opacity-60');
            resteInfo.innerHTML = 'Reste à payer : <strong class="text-red-600">' + montantTotal.toLocaleString('fr-FR') + ' FCFA</strong>';
        } else {
            montantPayeInput.readOnly = false;
            montantPayeGroup.classList.remove('opacity-60');
            const paye = parseFloat(montantPayeInput.value) || 0;
            const reste = montantTotal - paye;
            if (reste > 0) {
                resteInfo.innerHTML = 'Reste à payer : <strong class="text-red-600">' + reste.toLocaleString('fr-FR') + ' FCFA</strong>';
            } else {
                resteInfo.innerHTML = '<span class="text-emerald-600 font-bold">✅ Solde complet</span>';
            }
        }
    }
    radios.forEach(r => r.addEventListener('change', updatePaiement));
    montantPayeInput.addEventListener('input', updatePaiement);
    updatePaiement();
});
</script>
@endpush
