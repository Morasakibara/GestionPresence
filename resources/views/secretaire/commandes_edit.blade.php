@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Modifier la commande — Secrétaire</span></div>
@endsection

@section('navigation')
<a href="{{ route('secretaire.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Commandes</a>
<a href="{{ route('secretaire.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('secretaire.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">Commandes</a>
<a href="{{ route('secretaire.services') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')
<div class="max-w-xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="page-heading-title mb-6">Modifier la commande photo</h1>
    <div class="pharaoh-card p-6">
        <form action="{{ route('secretaire.commandes.update', $commande->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="input-label">Type</label>
                <select name="type" class="input-field mt-1" required>
                    @foreach($typesPhoto as $key => $label)
                        <option value="{{ $key }}" {{ $commande->type === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="input-label">Montant total (FCFA)</label>
                <input type="number" name="montant" id="montant" class="input-field mt-1" min="0.01" step="0.01" value="{{ old('montant', $commande->montant) }}" required oninput="updateMontantPaye()">
            </div>
            <div>
                <label class="input-label">Statut du paiement</label>
                <select name="statut_paiement" id="statut_paiement" class="input-field mt-1" required onchange="toggleMontantPaye()">
                    <option value="paye" {{ $commande->statut_paiement === 'paye' ? 'selected' : '' }}>✅ Payé</option>
                    <option value="partiel" {{ $commande->statut_paiement === 'partiel' ? 'selected' : '' }}>💰 Paiement partiel</option>
                    <option value="a_payer" {{ $commande->statut_paiement === 'a_payer' ? 'selected' : '' }}>⏳ À payer à la livraison</option>
                </select>
            </div>
            <div id="montantPayeField">
                <label class="input-label">Montant payé (FCFA)</label>
                <input type="number" name="montant_paye" id="montant_paye" class="input-field mt-1" min="0" step="0.01" value="{{ old('montant_paye', $commande->montant_paye) }}">
                <p class="mt-1 text-xs text-gray-400" id="montantPayeHint"></p>
            </div>
            <div>
                <label class="input-label">Détails (facultatif)</label>
                <input type="text" name="details" class="input-field mt-1" value="{{ old('details', $commande->details) }}">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-gold">Mettre à jour</button>
                <a href="{{ route('secretaire.commandes') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMontantPaye() {
    const statut = document.getElementById('statut_paiement').value;
    const input = document.getElementById('montant_paye');
    const hint = document.getElementById('montantPayeHint');
    if (statut === 'a_payer') { input.value = 0; input.disabled = true; hint.textContent = 'Le montant sera comptabilisé à la livraison.'; }
    else if (statut === 'paye') { updateMontantPaye(); input.disabled = true; hint.textContent = 'Montant complet automatiquement.'; }
    else { input.disabled = false; hint.textContent = 'Entrez le montant reçu du client.'; }
}
function updateMontantPaye() { if (document.getElementById('statut_paiement').value === 'paye') document.getElementById('montant_paye').value = document.getElementById('montant').value; }
document.addEventListener('DOMContentLoaded', function() { toggleMontantPaye(); });
</script>
@endsection
