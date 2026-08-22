@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Commandes Photo — Secrétaire</span></div>
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
<div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
    @if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif

    <h1 class="page-heading-title mb-6">Enregistrer une commande photo</h1>

    <div class="pharaoh-card p-6 mb-6">
        <form action="{{ route('secretaire.commandes.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div><label class="input-label">Type</label><select name="type" class="input-field mt-1" required><option value="">— Choisir —</option>@foreach($typesPhoto as $key => $label)<option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                <div><label class="input-label">Montant total (FCFA)</label><input type="number" name="montant" id="montant" class="input-field mt-1" min="0.01" step="0.01" value="{{ old('montant') }}" required oninput="updateMontantPaye()"></div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="input-label">Statut du paiement</label>
                    <select name="statut_paiement" id="statut_paiement" class="input-field mt-1" required onchange="toggleMontantPaye()">
                        <option value="paye" {{ old('statut_paiement') === 'paye' ? 'selected' : '' }}>✅ Payé (montant complet en caisse)</option>
                        <option value="partiel" {{ old('statut_paiement') === 'partiel' ? 'selected' : '' }}>💰 Paiement partiel (reste = dette)</option>
                        <option value="a_payer" {{ old('statut_paiement') === 'a_payer' ? 'selected' : '' }}>⏳ À payer à la livraison</option>
                    </select>
                </div>
                <div id="montantPayeField">
                    <label class="input-label">Montant payé (FCFA)</label>
                    <input type="number" name="montant_paye" id="montant_paye" class="input-field mt-1" min="0" step="0.01" value="{{ old('montant_paye', old('montant', '')) }}">
                    <p class="mt-1 text-xs text-gray-400" id="montantPayeHint"></p>
                </div>
            </div>

            <div>
                <label class="input-label">Détails (facultatif)</label>
                <input type="text" name="details" class="input-field mt-1" value="{{ old('details') }}">
            </div>

            <button type="submit" class="btn-gold"><svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Enregistrer</button>
        </form>
    </div>

    <div class="pharaoh-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200/70"><h2 class="text-lg font-semibold text-[#080808]">Commandes du jour</h2></div>
        <div class="table-scroll">
            <table class="min-w-full">
                <thead class="table-head">
                    <tr>
                        <th>Heure</th>
                        <th>Type</th>
                        <th>Détails</th>
                        <th class="text-right">Montant</th>
                        <th class="text-right">Payé</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse($commandes as $cmd)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cmd->created_at->format('H:i') }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-[#080808]">{{ $typesPhoto[$cmd->type] ?? $cmd->type }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $cmd->details ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-right text-[#080808]">{{ number_format($cmd->montant, 0, ',', '.') }} FCFA</td>
                        <td class="px-6 py-4 text-sm text-right {{ $cmd->statut_paiement === 'paye' ? 'text-green-700' : ($cmd->statut_paiement === 'partiel' ? 'text-orange-600' : 'text-gray-400') }}">
                            {{ number_format($cmd->montant_paye, 0, ',', '.') }} FCFA
                        </td>
                        <td class="px-6 py-4">
                            @if($cmd->statut_paiement === 'paye')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">✅ Payé</span>
                            @elseif($cmd->statut_paiement === 'partiel')
                                <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800">💰 Partiel</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">⏳ À payer</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('secretaire.commandes.edit', $cmd->id) }}" class="text-pharaoh-gold hover:text-pharaoh-bronze text-sm font-medium">Modifier</a>
                            <form action="{{ route('secretaire.commandes.destroy', $cmd->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button type="submit" class="text-red-500 hover:text-red-700 text-sm">Supprimer</button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">Aucune commande aujourd'hui</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleMontantPaye() {
    const statut = document.getElementById('statut_paiement').value;
    const field = document.getElementById('montantPayeField');
    const input = document.getElementById('montant_paye');
    const hint = document.getElementById('montantPayeHint');

    if (statut === 'a_payer') {
        input.value = 0;
        input.disabled = true;
        hint.textContent = 'Le montant sera comptabilisé à la livraison.';
    } else if (statut === 'paye') {
        updateMontantPaye();
        input.disabled = true;
        hint.textContent = 'Montant complet automatiquement.';
    } else {
        input.disabled = false;
        input.value = '';
        input.focus();
        hint.textContent = 'Entrez le montant reçu du client.';
    }
}

function updateMontantPaye() {
    const statut = document.getElementById('statut_paiement').value;
    if (statut === 'paye') {
        document.getElementById('montant_paye').value = document.getElementById('montant').value;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleMontantPaye();
});
</script>
@endsection
