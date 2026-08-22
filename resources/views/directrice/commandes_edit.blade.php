@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Modifier la commande — Directrice</span></div>
@endsection

@section('navigation')
<a href="{{ route('directrice.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Commandes</a>
<a href="{{ route('directrice.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')
<div class="max-w-xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="page-heading-title mb-6">Modifier la commande</h1>

    <div class="pharaoh-card p-6">
        <form action="{{ route('directrice.commandes.update', $commande->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="input-label">Type de commande</label>
                <select name="type" class="input-field mt-1" required>
                    @foreach($typesCaisse as $key => $label)
                        <option value="{{ $key }}" {{ $commande->type === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="input-label">Montant total (FCFA)</label>
                <input type="number" name="montant" id="montant" class="input-field mt-1" min="0.01" step="0.01" value="{{ old('montant', $commande->montant) }}" required oninput="updateMontantPaye()">
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="input-label">Statut du paiement</label>
                    <select name="statut_paiement" id="statut_paiement" class="input-field mt-1" required onchange="toggleMontantPaye()">
                        <option value="paye" {{ old('statut_paiement', $commande->statut_paiement) === 'paye' ? 'selected' : '' }}>✅ Payé</option>
                        <option value="partiel" {{ old('statut_paiement', $commande->statut_paiement) === 'partiel' ? 'selected' : '' }}>💰 Partiel</option>
                        <option value="a_payer" {{ old('statut_paiement', $commande->statut_paiement) === 'a_payer' ? 'selected' : '' }}>⏳ À payer</option>
                    </select>
                </div>
                <div id="montantPayeField">
                    <label class="input-label">Montant payé (FCFA)</label>
                    <input type="number" name="montant_paye" id="montant_paye" class="input-field mt-1" min="0" step="0.01" value="{{ old('montant_paye', $commande->montant_paye) }}">
                    <p class="mt-1 text-xs text-gray-400" id="montantPayeHint"></p>
                </div>
            </div>
            <div>
                <label class="input-label">Détails (facultatif)</label>
                <input type="text" name="details" class="input-field mt-1" value="{{ old('details', $commande->details) }}">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-gold">Mettre à jour</button>
                <a href="{{ route('directrice.commandes') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMontantPaye() {
    const statut = document.getElementById('statut_paiement').value;
    const input = document.getElementById('montant_paye');
    const hint = document.getElementById('montantPayeHint');
    const montantInput = document.getElementById('montant');
    if (statut === 'paye') {
        input.value = montantInput.value || '0';
        input.readOnly = true;
        input.classList.add('bg-gray-100');
        hint.textContent = 'Montant complet automatique.';
    } else if (statut === 'partiel') {
        input.readOnly = false;
        input.classList.remove('bg-gray-100');
        updateMontantPaye();
    } else {
        input.value = '0';
        input.readOnly = true;
        input.classList.add('bg-gray-100');
        hint.textContent = 'Paiement différé.';
    }
}
function updateMontantPaye() {
    const statut = document.getElementById('statut_paiement').value;
    const montant = parseFloat(document.getElementById('montant').value) || 0;
    const input = document.getElementById('montant_paye');
    const hint = document.getElementById('montantPayeHint');
    if (statut === 'paye') input.value = montant;
    else if (statut === 'partiel') {
        const reste = Math.max(0, montant - (parseFloat(input.value) || 0));
        hint.textContent = 'Reste à encaisser : ' + reste.toLocaleString('fr') + ' FCFA';
    }
}
document.addEventListener('DOMContentLoaded', toggleMontantPaye);
</script>
@endsection
