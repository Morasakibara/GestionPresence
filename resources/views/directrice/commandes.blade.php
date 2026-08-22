@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Gestion Des Commandes</span></div>
@endsection

@section('navigation')
<a href="{{ route('directrice.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Commandes</a>
<a href="{{ route('directrice.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('directrice.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">

    @if(session('success'))
        <div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>
    @endif

    <h1 class="page-heading-title mb-6">Enregistrer une commande</h1>

    <!-- Formulaire -->
    <div class="pharaoh-card p-6 mb-6">
        <form action="{{ route('directrice.commandes.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="input-label">Type de commande</label>
                    <select name="type" class="input-field mt-1" required>
                        <option value="">— Choisir —</option>
                        @foreach($typesCaisse as $key => $label)
                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="input-label">Montant total (FCFA)</label>
                    <input type="number" name="montant" id="montant" class="input-field mt-1" min="0.01" step="0.01" value="{{ old('montant') }}" required oninput="updateMontantPaye()">
                </div>
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

            @error('type')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            @error('montant')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            @error('statut_paiement')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror

            <div>
                <label class="input-label">Détails (facultatif)</label>
                <input type="text" name="details" class="input-field mt-1" value="{{ old('details') }}" placeholder="Ex: 100 pages noir...">
            </div>
            <button type="submit" class="btn-gold">
                <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Enregistrer
            </button>
        </form>
    </div>

    <!-- Liste des commandes du jour -->
    <div class="pharaoh-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200/70">
            <h2 class="text-lg font-semibold text-[#080808]">Commandes du jour</h2>
        </div>
        <div class="table-scroll">
            <table class="min-w-full">
                <thead class="table-head">
                    <tr>
                        <th>Heure</th>
                        <th>Type</th>
                        <th>Détails</th>
                        <th class="text-right">Montant</th>
                        <th class="text-right">Payé</th>
                        <th class="text-center">Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    @forelse($commandes as $cmd)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $cmd->created_at->format('H:i') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-[#080808]">{{ $typesCaisse[$cmd->type] ?? $cmd->type }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $cmd->details ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-right text-[#080808]">{{ number_format($cmd->montant, 0, ',', '.') }} FCFA</td>
                            <td class="px-6 py-4 text-sm text-right text-[#2E8B57]">{{ number_format($cmd->montant_paye ?? 0, 0, ',', '.') }} FCFA</td>
                            <td class="px-6 py-4 text-center">
                                @if(($cmd->statut_paiement ?? 'a_payer') === 'paye')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Payé</span>
                                @elseif(($cmd->statut_paiement ?? 'a_payer') === 'partiel')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Partiel</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">À payer</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('directrice.commandes.edit', $cmd->id) }}" class="text-pharaoh-gold hover:text-pharaoh-bronze text-sm font-medium">Modifier</a>
                                <form action="{{ route('directrice.commandes.destroy', $cmd->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette commande ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">Aucune commande enregistrée aujourd'hui</td></tr>
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
        hint.textContent = 'Paiement différé — aucune encaisse.';
    }
}
function updateMontantPaye() {
    const statut = document.getElementById('statut_paiement').value;
    const montant = parseFloat(document.getElementById('montant').value) || 0;
    const input = document.getElementById('montant_paye');
    const hint = document.getElementById('montantPayeHint');
    if (statut === 'paye') {
        input.value = montant;
    } else if (statut === 'partiel') {
        const paye = parseFloat(input.value) || 0;
        const reste = Math.max(0, montant - paye);
        hint.textContent = 'Reste à encaisser : ' + reste.toLocaleString('fr') + ' FCFA';
    }
}
document.addEventListener('DOMContentLoaded', toggleMontantPaye);
</script>
@endsection
