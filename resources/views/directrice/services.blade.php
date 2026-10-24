@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Services Fournis</span></div>
@endsection

@section('navigation')
<a href="{{ route('directrice.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Services</a>
<a href="{{ route('directrice.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif
@if(session('error'))<div class="alert alert-error"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/></svg><span>{{ session('error') }}</span></div>@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ═══ Étape 2.1 — Saisie initiale (ACOMPTE) ═══ -->
    <div class="lg:col-span-1">
        <div class="form-card">
            <div class="form-card-header">
                <h2 class="form-card-title">Nouveau service</h2>
                <p class="form-card-subtitle">Étape 1 : enregistrer le montant total et l'acompte</p>
            </div>

            @if ($errors->any())
            <div class="bg-red-600/10 border-2 border-red-600 text-red-600 px-4 py-3 rounded-md mb-6">
                <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            <form action="{{ route('directrice.services.store') }}" method="POST" class="space-y-5">
                @csrf
                <div class="form-group">
                    <label class="input-label">Type de service</label>
                    <select name="type" class="select-field" required>
                        <option value="">— Choisir —</option>
                        @foreach($typesCaisse as $key => $label)
                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="input-label">Nom du client<span class="text-gray-400 font-normal"> (facultatif)</span></label>
                    <input type="text" name="client_nom" value="{{ old('client_nom') }}" maxlength="100" class="input-field" placeholder="Ex: Mme Bela">
                </div>
                <div class="form-group">
                    <label class="input-label">Numéro WhatsApp <span class="text-red-500">*</span></label>
                    <input type="tel" name="numero_whatsapp" required value="{{ old('numero_whatsapp') }}"
                           class="input-field" placeholder="+2376XXXXXXXX"
                           pattern="^\+[1-9]\d{7,14}$" title="Format international : +2376XXXXXXX">
                    <p class="text-xs text-gray-400 mt-1">Le PDF de la facture sera envoyé automatiquement sur ce numéro.</p>
                </div>
                <div class="form-group">
                    <label class="input-label">Montant total (FCFA)</label>
                    <input type="number" name="montant" id="montant" class="input-field" min="1" step="any" value="{{ old('montant') }}" required placeholder="0">
                </div>
                <div class="form-group">
                    <label class="input-label">Acompte versé (FCFA)</label>
                    <input type="number" name="acompte" id="acompte" class="input-field" min="1" step="any" value="{{ old('acompte') }}" required placeholder="0">
                    <p class="text-xs mt-1 font-medium" id="soldeInfo"></p>
                </div>
                <div class="form-group">
                    <label class="input-label">Détails (facultatif)</label>
                    <textarea name="details" rows="2" class="input-field" placeholder="Description du service">{{ old('details') }}</textarea>
                </div>
                <button type="submit" class="btn-primary gap-2">
                    <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
                    <span>Valider l'acompte</span>
                </button>
            </form>
        </div>
    </div>

    <!-- ═══ LISTE DES SERVICES ═══ -->
    <div class="lg:col-span-2">

        {{-- Clôture du solde (Étape 2.2) --}}
        <div class="pharaoh-card overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-orange-50/40">
                <h3 class="text-lg font-semibold text-[#080808] flex items-center gap-2">
                    <svg class="h-5 w-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Clôture du solde — services en attente de paiement</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#080808] text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Facture</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Client / WhatsApp</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Solde restant</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase">Clôture</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($services->filter(fn($s) => $s->estEnAttenteSolde()) as $svc)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm font-semibold text-[#080808]">{{ $svc->numero_facture }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-[#080808]">{{ $svc->client_nom ?: 'Client' }}</div>
                                <div class="text-xs text-gray-500">{{ $svc->numero_whatsapp }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-orange-600">{{ number_format($svc->solde, 0, ',', '.') }} FCFA</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" onclick="showSoldeServiceModal({{ $svc->id }}, '{{ e($svc->numero_facture) }}', {{ $svc->montant }}, {{ $svc->montant_paye }}, '{{ e($typesCaisse[$svc->type] ?? $svc->type) }}')"
                                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 shadow">
                                    Encaisser le solde
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">Aucun solde en attente — tous les services sont réglés.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Historique --}}
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-[#080808] flex items-center gap-2">
                    <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    <span>Services enregistrés</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#080808] text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Heure</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Facture / Client</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Montant</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Statut</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($services as $svc)
                        <tr class="hover:bg-gray-50 transition-colors" data-status="{{ $svc->statut_paiement ?? 'a_payer' }}">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $svc->created_at->format('H:i') }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-semibold text-[#080808]">{{ $svc->numero_facture ?? '#' . $svc->id }}</div>
                                @if($svc->numero_whatsapp)<div class="text-xs text-gray-500">{{ $svc->numero_whatsapp }}</div>@endif
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-[#080808]">{{ $typesCaisse[$svc->type] ?? $svc->type }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-right text-blue-600">{{ number_format($svc->montant, 0, ',', '.') }} FCFA</td>
                            <td class="px-4 py-3 text-sm">
                                @if(($svc->statut_paiement ?? 'a_payer') === 'paye')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">✅ Payé</span>
                                @elseif($svc->estEnAttenteSolde())
                                    <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-semibold text-orange-700">⏳ Solde : {{ number_format($svc->solde, 0, ',', '.') }} FCFA</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600">À payer</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center space-x-2">
                                <a href="{{ route('directrice.services.edit', $svc->id) }}" class="text-pharaoh-gold hover:text-pharaoh-gold-light text-sm font-medium" title="{{ $svc->estVerrouillee() ? 'Mot de passe admin requis' : 'Modifier' }}">✏️</a>
                                <button type="button" onclick="showDeleteServiceModal({{ $svc->id }}, '{{ $typesCaisse[$svc->type] ?? $svc->type }}', '{{ number_format($svc->montant, 0, ',', '.') }}', 'directrice', {{ $svc->montant_paye }})" class="text-red-500 hover:text-red-700 text-sm cursor-pointer">🗑️</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">📭</div>Aucun service enregistré.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL : ENCAISSER LE SOLDE SERVICE (montant verrouillé) ═══ -->
<div id="soldeServiceModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4" onclick="event.stopPropagation()">
        <div class="bg-[#080808] px-6 py-5 text-center">
            <h3 class="text-lg font-bold text-white">Clôture — Encaisser le solde</h3>
            <p class="text-sm text-gray-400 mt-1">Le reste à payer est verrouillé par le système</p>
        </div>
        <div class="px-6 py-5">
            <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-2">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Facture</span><span class="font-semibold" id="svcSoldeNum"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Montant total</span><span class="font-semibold" id="svcSoldeMontant"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Déjà encaissé</span><span class="font-semibold text-green-600" id="svcSoldePaye"></span></div>
                <div class="flex justify-between text-sm border-t pt-2"><span class="text-gray-500 font-bold">Reste à payer (verrouillé)</span><span class="font-bold text-orange-600" id="svcSoldeReste"></span></div>
            </div>
            <form id="svcSoldeForm" method="POST" onsubmit="return confirm('Confirmer l\'encaissement du solde ?')">
                @csrf
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('soldeServiceModal')" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">Annuler</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-green-600 text-sm font-semibold text-white hover:bg-green-700 shadow-lg">💰 Encaisser le solde</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══ MODAL SUPPRESSION SERVICE (+ verrou manager) ═══ -->
<div id="deleteServiceModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" onclick="event.stopPropagation()">
        <div class="bg-[#080808] px-6 py-5 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-red-500/10 flex items-center justify-center mb-3">
                <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white">Supprimer le service</h3>
            <p class="text-sm text-gray-400 mt-1">Cette action est irréversible</p>
        </div>
        <div class="px-6 py-5">
            <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-2">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Type</span><span class="font-semibold text-[#080808]" id="deleteSvcType"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Montant</span><span class="font-semibold text-blue-600" id="deleteSvcMontant"></span></div>
            </div>
            <div id="deleteSvcVerrou" class="hidden bg-amber-50 border border-amber-300 rounded-lg px-4 py-3 mb-4">
                <p class="text-sm font-semibold text-amber-800 mb-2">🔐 Facture avec acompte — mot de passe administrateur requis</p>
                <input type="password" name="manager_password" id="deleteSvcManagerPassword" class="w-full rounded-lg border border-amber-300 px-3 py-2 text-sm" placeholder="Mot de passe administrateur" autocomplete="current-password">
            </div>
            <p class="text-sm text-gray-500 mb-4">Êtes-vous sûr de vouloir supprimer ce service ?</p>
            <form id="deleteServiceForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteServiceModal()" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">Non, garder</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 text-sm font-semibold text-white hover:bg-red-700 transition-colors shadow-lg shadow-red-600/20">Oui, supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('mobile-navigation')
<a href="{{ route('directrice.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('directrice.services') }}" class="block rounded-md px-3 py-2 text-base font-medium bg-pharaoh-gold text-white">Services</a>
<a href="{{ route('directrice.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@push('scripts')
<script>
// ═══ Aides modals ═══
function openModal(id)  { const m = document.getElementById(id); m.classList.remove('hidden'); m.classList.add('flex'); document.body.style.overflow = 'hidden'; }
function closeModal(id) { const m = document.getElementById(id); m.classList.add('hidden'); m.classList.remove('flex'); document.body.style.overflow = ''; }

// ═══ Clôture du solde service — montant verrouillé ═══
function showSoldeServiceModal(id, numero, total, paye, type) {
    document.getElementById('svcSoldeNum').textContent = numero;
    document.getElementById('svcSoldeMontant').textContent = Number(total).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('svcSoldePaye').textContent = Number(paye).toLocaleString('fr-FR') + ' FCFA';
    const reste = Number(total) - Number(paye);
    document.getElementById('svcSoldeReste').textContent = reste.toLocaleString('fr-FR') + ' FCFA';
    const form = document.getElementById('svcSoldeForm');
    form.action = '/directrice/services/' + id + '/encaisser-solde';
    openModal('soldeServiceModal');
}

// ═══ Suppression service avec verrou manager ═══
function showDeleteServiceModal(id, type, montant, module, paye) {
    document.getElementById('deleteSvcType').textContent = type;
    document.getElementById('deleteSvcMontant').textContent = montant + ' FCFA';
    document.getElementById('deleteServiceForm').action = '/' + module + '/services/' + id;
    const vernee = Number(paye) > 0;
    document.getElementById('deleteSvcVerrou').classList.toggle('hidden', !vernee);
    document.getElementById('deleteSvcManagerPassword').required = vernee;
    const modal = document.getElementById('deleteServiceModal');
    modal.classList.remove('hidden'); modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeDeleteServiceModal() {
    const modal = document.getElementById('deleteServiceModal');
    modal.classList.add('hidden'); modal.classList.remove('flex');
    document.body.style.overflow = '';
}

// ═══ Acompte : calcul automatique du solde restant ═══
document.addEventListener('DOMContentLoaded', () => {
    const total = document.getElementById('montant');
    const acompte = document.getElementById('acompte');
    const info = document.getElementById('soldeInfo');
    function majSolde() {
        const t = parseFloat(total.value) || 0;
        const a = parseFloat(acompte.value) || 0;
        const reste = t - a;
        if (!t || !a) { info.textContent = ''; info.className = 'text-xs mt-1 font-medium'; return; }
        if (reste < 0) { info.textContent = "⚠️ L'acompte dépasse le montant total."; info.className = 'text-xs mt-1 font-medium text-red-600'; return; }
        info.textContent = reste === 0
            ? '✅ Comptant — la facture sera créée payée.'
            : 'Solde restant : ' + reste.toLocaleString('fr-FR') + ' FCFA (verrouillé)';
        info.className = 'text-xs mt-1 font-medium text-pharaoh-gold';
    }
    total.addEventListener('input', majSolde);
    acompte.addEventListener('input', majSolde);
    majSolde();
});

document.addEventListener('click', function(e) { if (['soldeServiceModal','deleteServiceModal'].includes(e.target.id)) closeModal(e.target.id); });
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') ['soldeServiceModal','deleteServiceModal'].forEach(closeModal); });
</script>
@endpush
