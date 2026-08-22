@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Modifier la commande #{{ $commande->id }}</span>
</div>
@endsection

@section('navigation')
<a href="{{ route('directrice.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
@endsection

@section('content')

<div class="flex justify-center">
    <div class="form-card w-full max-w-lg">
        <div class="form-card-header">
            <h2 class="form-card-title">Modifier la commande</h2>
            <p class="form-card-subtitle">Mise à jour du type, montant et paiement</p>
        </div>

        @if ($errors->any())
        <div class="bg-red-600/10 border-2 border-red-600 text-red-600 px-4 py-3 rounded-md mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

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
                <label for="montant" class="input-label">Montant total (FCFA)</label>
                <input type="number" id="montant" name="montant" min="1" step="any" value="{{ old('montant', $commande->montant) }}" required class="input-field">
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
                </div>
            </div>

            <div class="form-group" id="montantPayeGroup">
                <label for="montant_paye" class="input-label">Montant payé (FCFA)</label>
                <input type="number" id="montant_paye" name="montant_paye" min="0" step="any" value="{{ old('montant_paye', $commande->montant_paye) }}" class="input-field">
                <p class="text-xs text-gray-400 mt-1" id="resteInfo"></p>
            </div>

            <div class="form-group">
                <label for="details" class="input-label">Détails (facultatif)</label>
                <textarea id="details" name="details" rows="2" class="input-field">{{ old('details', $commande->details) }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">💾 Enregistrer</button>
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
    const montantInput = document.getElementById('montant');
    const montantPayeInput = document.getElementById('montant_paye');
    const montantPayeGroup = document.getElementById('montantPayeGroup');
    const resteInfo = document.getElementById('resteInfo');

    function updatePaiement() {
        const statut = document.querySelector('input[name="statut_paiement"]:checked')?.value;
        const montant = parseFloat(montantInput.value) || 0;
        if (statut === 'paye') {
            montantPayeInput.value = montant; montantPayeInput.readOnly = true;
            montantPayeGroup.classList.add('opacity-60');
            resteInfo.textContent = '';
        } else if (statut === 'a_payer') {
            montantPayeInput.value = 0; montantPayeInput.readOnly = true;
            montantPayeGroup.classList.add('opacity-60');
            resteInfo.textContent = '';
        } else {
            montantPayeInput.readOnly = false;
            montantPayeGroup.classList.remove('opacity-60');
            const reste = montant - (parseFloat(montantPayeInput.value) || 0);
            resteInfo.textContent = reste > 0 ? 'Reste : ' + reste.toLocaleString('fr-FR') + ' FCFA' : '';
        }
    }
    radios.forEach(r => r.addEventListener('change', updatePaiement));
    montantInput.addEventListener('input', function() {
        if (document.querySelector('input[name="statut_paiement"]:checked')?.value === 'paye') montantPayeInput.value = this.value;
        updatePaiement();
    });
    montantPayeInput.addEventListener('input', updatePaiement);
    updatePaiement();
});
</script>
@endpush
