@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Facturation — Imprimerie</span>
    <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center px-2 py-1 text-sm font-medium text-pharaoh-gold hover:bg-white/10 rounded-md">
        <svg class="h-6 w-6 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
    </a>
</div>
@endsection

@section('navigation')
<a href="{{ route('secretaire.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Factures</a>
<a href="{{ route('secretaire.services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif
@if(session('error'))<div class="alert alert-danger"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>@endif

@if($errors->any())
<div class="bg-red-600/10 border-2 border-red-600 text-red-600 px-4 py-3 rounded-md mb-4">
    <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

{{-- ═══ Étape 4.2 — Alertes d'envoi WhatsApp en échec ═══ --}}
@if($envoisEchecs->count() > 0)
<div class="pharaoh-card mb-6 border-2 border-red-300 bg-red-50/80 overflow-hidden">
    <div class="px-5 py-3 border-b border-red-200 flex items-center gap-2 bg-red-100/60">
        <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19H19a2 2 0 001.66-3.04l-7-12A2 2 0 008.09 3l-7 12A2 2 0 004.07 19z"/></svg>
        <span class="font-bold text-red-700">Envois WhatsApp en échec — {{ $envoisEchecs->count() }}</span>
        <span class="text-sm text-red-600 hidden sm:inline">Vérifier le numéro avec le client puis renvoyer.</span>
    </div>
    @foreach($envoisEchecs as $envoi)
    <div class="px-5 py-3 flex flex-wrap items-center gap-3 border-b border-red-100 last:border-0">
        <span class="text-sm font-semibold text-[#080808]">#{{ $envoi->commande?->numero_facture ?? $envoi->facture?->numero ?? ('C-' . $envoi->commande_id) }}</span>
        <span class="text-sm text-gray-600">{{ $envoi->telephone }}</span>
        <span class="text-xs text-red-600 flex-1 min-w-[180px] truncate" title="{{ $envoi->erreur }}">{{ $envoi->erreur }}</span>
        <button type="button" onclick="showRenvoyerModal({{ $envoi->id }}, '{{ $envoi->telephone }}', '{{ addslashes($envoi->erreur) }}')"
                class="rounded-lg bg-pharaoh-gold px-3 py-1.5 text-xs font-semibold text-white hover:brightness-110">
            Corriger le numéro &amp; renvoyer
        </button>
    </div>
    @endforeach
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ═══ Étape 2.1 — Saisie initiale (ACOMPTE) ═══ -->
    <div class="lg:col-span-1">
        <div class="form-card">
            <div class="form-card-header">
                <h2 class="form-card-title">Nouvelle facture</h2>
                <p class="form-card-subtitle">Étape 1 : enregistrer le montant total et l'acompte</p>
            </div>

            <form id="formAcompte" method="POST" action="{{ route('secretaire.commandes.store') }}" class="space-y-5">
                @csrf

                <div class="form-group">
                    <label for="type" class="input-label">Type de facture</label>
                    <select id="type" name="type" required class="select-field">
                        <option value="">— Choisir —</option>
                        @foreach($typesPhoto as $key => $label)
                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="client_nom" class="input-label">Nom du client<span class="text-gray-400 font-normal"> (facultatif)</span></label>
                    <input type="text" id="client_nom" name="client_nom" value="{{ old('client_nom') }}" maxlength="100" class="input-field" placeholder="Ex: Mme Bela">
                </div>

                <div class="form-group">
                    <label for="numero_whatsapp" class="input-label">Numéro WhatsApp <span class="text-red-500">*</span></label>
                    <input type="tel" id="numero_whatsapp" name="numero_whatsapp" required value="{{ old('numero_whatsapp') }}"
                           class="input-field" placeholder="+2376XXXXXXXX"
                           pattern="^\+[1-9]\d{7,14}$" title="Format international : +2376XXXXXXX">
                    <p class="text-xs text-gray-400 mt-1">Le PDF de la facture sera envoyé automatiquement sur ce numéro.</p>
                </div>

                <div class="form-group">
                    <label for="montant" class="input-label">Montant total (FCFA)</label>
                    <input type="number" id="montant" name="montant" min="1" step="any" value="{{ old('montant') }}" required class="input-field" placeholder="0">
                </div>

                <div class="form-group">
                    <label for="acompte" class="input-label">Acompte versé (FCFA)</label>
                    <input type="number" id="acompte" name="acompte" min="1" step="any" value="{{ old('acompte') }}" required class="input-field" placeholder="0">
                    <p class="text-xs mt-1 font-medium" id="soldeInfo"></p>
                </div>

                <div class="form-group">
                    <label for="details" class="input-label">Détails (facultatif)</label>
                    <textarea id="details" name="details" rows="2" class="input-field" placeholder="Ex: 200 pages A4 noir et blanc">{{ old('details') }}</textarea>
                </div>

                <button type="submit" class="btn-primary mt-2 gap-2">
                    <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                    <span>Valider l'acompte</span>
                </button>
            </form>
        </div>
    </div>

    <!-- ═══ LISTE DES FACTURES + CLÔTURE (Étape 2.2) ═══ -->
    <div class="lg:col-span-2">
        {{-- Clôture du solde --}}
        <div class="pharaoh-card overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-orange-50/40">
                <h3 class="text-lg font-semibold text-[#080808] flex items-center gap-2">
                    <svg class="h-5 w-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Clôture du solde — en attente de paiement</span>
                </h3>
                <div class="mt-3">
                    <input type="text" id="searchSolde" oninput="filterSolde()" placeholder="Rechercher : client, WhatsApp, n° facture…"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-pharaoh-gold focus:ring-1 focus:ring-pharaoh-gold">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#080808] text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Facture</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Client / WhatsApp</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Soldes restants</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase">Clôture</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="soldeRows">
                        @forelse($commandes->filter(fn($c) => $c->estEnAttenteSolde()) as $cmd)
                        <tr data-solde="{{ $cmd->numero_facture }} {{ mb_strtolower($cmd->client_nom ?? '') }} {{ str_replace('+','',$cmd->numero_whatsapp ?? '') }}">
                            <td class="px-4 py-3 text-sm font-semibold text-[#080808]">{{ $cmd->numero_facture }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-[#080808]">{{ $cmd->client_nom ?: 'Client' }}</div>
                                <div class="text-xs text-gray-500">{{ $cmd->numero_whatsapp }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-orange-600">{{ number_format($cmd->solde, 0, ',', '.') }} FCFA</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" onclick="showSoldeModal({{ $cmd->id }}, '{{ e($cmd->numero_facture) }}', {{ $cmd->montant }}, {{ $cmd->montant_paye }}, '{{ e($typesPhoto[$cmd->type] ?? $cmd->type) }}')"
                                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 shadow">
                                    Encaisser le solde
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr id="soldeEmpty"><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">Aucun solde en attente — toutes les factures sont réglées.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Historique des factures --}}
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-[#080808] flex items-center gap-2">
                        <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        <span>Factures enregistrées</span>
                    </h3>
                    <div class="flex gap-2">
                        <button onclick="filterStatut('all')" class="filter-btn active" data-filter="all">Toutes</button>
                        <button onclick="filterStatut('active')" class="filter-btn" data-filter="active">Actives</button>
                        <button onclick="filterStatut('annule')" class="filter-btn" data-filter="annule">Annulées</button>
                    </div>
                </div>
                <div class="flex flex-wrap items-end gap-3 pt-2 border-t border-gray-100">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Du</label>
                        <input type="date" id="filterDateDebut" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm" onchange="applyAllFilters()">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Au</label>
                        <input type="date" id="filterDateFin" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm" onchange="applyAllFilters()">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                        <select id="filterType" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm" onchange="applyAllFilters()">
                            <option value="">Tous les types</option>
                            @foreach($typesPhoto as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button onclick="resetFilters()" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-500 hover:bg-gray-100">Réinitialiser</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#080808] text-white">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase">Facture</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase">Client</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase">Type</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase">Total</th>
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase">Acompte</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold uppercase">Statut</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($commandes as $cmd)
                        <tr class="hover:bg-gray-50 {{ $cmd->statut_paiement === 'annule' ? 'bg-red-50/50 opacity-60' : '' }}"
                            data-status="{{ $cmd->statut_paiement === 'en_attente_solde' ? 'partiel' : $cmd->statut_paiement }}"
                            data-date="{{ $cmd->date }}"
                            data-type="{{ $cmd->type }}">
                            <td class="px-3 py-3">
                                <div class="text-sm font-semibold text-[#080808]">{{ $cmd->numero_facture ?? '#' . $cmd->id }}</div>
                                <div class="text-xs text-gray-400">{{ $cmd->date }}</div>
                            </td>
                            <td class="px-3 py-3 text-sm">
                                <div class="font-medium text-[#080808]">{{ $cmd->client_nom ?: '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $cmd->numero_whatsapp ?? '' }}</div>
                            </td>
                            <td class="px-3 py-3 text-sm text-gray-700">{{ $typesPhoto[$cmd->type] ?? $cmd->type }}</td>
                            <td class="px-3 py-3 text-sm text-right font-medium text-[#080808]">
                                {{ number_format($cmd->montant, 0, ',', '.') }}
                                @if($cmd->estVerrouillee()) <svg class="h-3.5 w-3.5 text-gray-400 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-sm text-right {{ $cmd->montant_paye > 0 ? 'text-green-600 font-medium' : 'text-gray-400' }}">{{ number_format($cmd->montant_paye, 0, ',', '.') }}</td>
                            <td class="px-3 py-3">
                                <span class="badge {{ $cmd->statut_paiement === 'paye' ? 'badge-success' : ($cmd->statut_paiement === 'annule' ? 'bg-red-100 text-red-700' : 'badge-warning') }}">
                                    {{ ['paye' => 'Payée', 'annule' => 'Annulée', 'partiel' => 'Partielle', 'a_payer' => 'À payer', 'en_attente_solde' => 'Solde en attente'][$cmd->statut_paiement] ?? $cmd->statut_paiement }}
                                </span>
                                @if($cmd->numero_facture)
                                <div class="mt-1">
                                    <a href="{{ route('secretaire.factures.pdf', \App\Models\Facture::where('numero', $cmd->numero_facture)->where('phase', $cmd->statut_paiement === 'paye' ? 'solde' : 'acompte')->latest()->value('id') ?? 0) }}"
                                       class="text-xs text-pharaoh-gold hover:underline">📄 PDF</a>
                                </div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if($cmd->statut_paiement !== 'annule')
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="showVerrouModal('supprimer', {{ $cmd->id }}, '{{ e($cmd->numero_facture ?? '#' . $cmd->id) }}', {{ $cmd->montant_paye }})"
                                            class="text-red-500 hover:text-red-700" title="Supprimer ({{ $cmd->estVerrouillee() ? 'mot de passe admin requis' : 'direct' }})">
                                        🗑️
                                    </button>
                                    <button type="button" onclick="showVerrouModal('annuler', {{ $cmd->id }}, '{{ e($cmd->numero_facture ?? '#' . $cmd->id) }}', {{ $cmd->montant_paye }})"
                                            class="text-orange-500 hover:text-orange-700" title="Annuler ({{ $cmd->estVerrouillee() ? 'mot de passe admin requis' : 'direct' }})">
                                        ✖️
                                    </button>
                                    @if(!$cmd->estVerrouillee())
                                    <a href="{{ route('secretaire.commandes.edit', $cmd->id) }}" class="text-pharaoh-gold" title="Modifier">✏️</a>
                                    @endif
                                </div>
                                @else
                                <span class="text-xs text-gray-400 italic">Annulée</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">🧾</div>Aucune facture pour le moment.
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL : ENCAISSER LE SOLDE (montant verrouillé) ═══ -->
<div id="soldeModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4" onclick="event.stopPropagation()">
        <div class="bg-[#080808] px-6 py-5 text-center">
            <h3 class="text-lg font-bold text-white">Clôture — Encaisser le solde</h3>
            <p class="text-sm text-gray-400 mt-1">Le montant reste à payer est verrouillé par le système</p>
        </div>
        <div class="px-6 py-5">
            <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-2">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Facture</span><span class="font-semibold" id="soldeNum"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Montant total</span><span class="font-semibold" id="soldeMontant"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Déjà encaissé</span><span class="font-semibold text-green-600" id="soldePaye"></span></div>
                <div class="flex justify-between text-sm border-t pt-2"><span class="text-gray-500 font-bold">Reste à payer (verrouillé)</span><span class="font-bold text-orange-600" id="soldeReste"></span></div>
            </div>
            <form id="soldeForm" method="POST" onsubmit="return confirm('Confirmer l\'encaissement du solde ?')">
                @csrf
                <input type="hidden" name="montant_verse" value="">
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('soldeModal')" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">Annuler</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-green-600 text-sm font-semibold text-white hover:bg-green-700 shadow-lg">💰 Encaisser le solde</button>
                </div>
                <input type="hidden" name="types_photo_fallback" value="1">
            </form>
        </div>
    </div>
</div>

<!-- ═══ MODAL : SUPPRIMER / ANNULER (+ verrou manager) ═══ -->
<div id="verrouModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4" onclick="event.stopPropagation()">
        <div class="bg-[#080808] px-6 py-5 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-red-500/10 flex items-center justify-center mb-3">
                <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white uppercase" id="verrouAction">—</h3>
            <p class="text-sm text-gray-400 mt-1">Action irréversible</p>
        </div>
        <div class="px-6 py-5">
            <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-2">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Facture</span><span class="font-semibold" id="verrouFacture"></span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Déjà encaissé</span><span class="font-semibold text-orange-600" id="verrouPaye"></span></div>
            </div>
            <div id="verrouWarn" class="hidden bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4 text-sm text-red-700">
                Un remboursement sera comptabilisé en négatif dans la caisse.
            </div>
            {{-- Verrou manager : visible dès que la facture a un versement --}}
            <div id="verrouManager" class="hidden bg-amber-50 border border-amber-300 rounded-lg px-4 py-3 mb-4">
                <p class="text-sm font-semibold text-amber-800 mb-2">🔐 Facture avec acompte — mot de passe administrateur requis</p>
                <input type="password" name="manager_password" id="managerPassword" class="w-full rounded-lg border border-amber-300 px-3 py-2 text-sm" placeholder="Mot de passe administrateur" autocomplete="current-password">
            </div>
            <form id="verrouForm" method="POST">
                @csrf
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('verrouModal')" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold text-gray-700 hover:bg-gray-50">Non, garder</button>
                    <button type="submit" id="verrouSubmit" class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 text-sm font-semibold text-white hover:bg-red-700 shadow-lg">Oui, confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══ MODAL : Renvoi WhatsApp ═══ -->
<div id="renvoyerModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4" onclick="event.stopPropagation()">
        <div class="bg-[#080808] px-6 py-5 text-center">
            <h3 class="text-lg font-bold text-white">Corriger le numéro WhatsApp</h3>
            <p class="text-sm text-gray-400 mt-1">Le PDF de la facture sera renvoyé automatiquement</p>
        </div>
        <div class="px-6 py-5">
            <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4 text-sm text-red-700" id="renvoyerErreur"></div>
            <form id="renvoyerForm" method="POST" class="space-y-4">
                @csrf
                <input type="tel" name="numero_whatsapp" id="renvoyerTel" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="+2376XXXXXXXX" pattern="^\+[1-9]\d{7,14}$">
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('renvoyerModal')" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-sm font-semibold">Fermer</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-pharaoh-gold text-sm font-semibold text-white hover:brightness-110">📤 Renvoyer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('mobile-navigation')
<a href="{{ route('secretaire.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="block rounded-md px-3 py-2 text-base font-medium bg-pharaoh-gold text-white">Factures</a>
<a href="{{ route('secretaire.services') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@push('styles')
<style>
.filter-btn { padding: 0.35rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; border: 1px solid #E7E7E7; background: white; color: #555; cursor: pointer; }
.filter-btn.active { background: #D39B23; color: white; border-color: #D39B23; }
</style>
@endpush

@push('scripts')
<script>
// ═══ Aides modals ═══
function openModal(id)   { const m = document.getElementById(id); m.classList.remove('hidden'); m.classList.add('flex'); document.body.style.overflow = 'hidden'; }
function closeModal(id)  { const m = document.getElementById(id); m.classList.add('hidden'); m.classList.remove('flex'); document.body.style.overflow = ''; }

// ═══ Clôture du solde — montant verrouillé ═══
function showSoldeModal(id, numero, total, paye, type) {
    document.getElementById('soldeNum').textContent = numero;
    document.getElementById('soldeMontant').textContent = Number(total).toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('soldePaye').textContent = Number(paye).toLocaleString('fr-FR') + ' FCFA';
    const reste = Number(total) - Number(paye);
    document.getElementById('soldeReste').textContent = reste.toLocaleString('fr-FR') + ' FCFA';
    const form = document.getElementById('soldeForm');
    form.action = '/secretaire/commandes/' + id + '/encaisser-solde';
    form.querySelector('input[name=montant_verse]').value = reste;
    openModal('soldeModal');
}

// ═══ Suppression / Annulation avec verrou manager ═══
function showVerrouModal(action, id, factureLabel, paye) {
    document.getElementById('verrouAction').textContent = action === 'supprimer' ? 'Supprimer la facture' : 'Annuler la facture';
    document.getElementById('verrouFacture').textContent = factureLabel;
    document.getElementById('verrouPaye').textContent = Number(paye).toLocaleString('fr-FR') + ' FCFA';
    const vernee = Number(paye) > 0;
    document.getElementById('verrouManager').classList.toggle('hidden', !vernee);
    document.getElementById('managerPassword').required = vernee;
    document.getElementById('verrouWarn').classList.toggle('hidden', !(action === 'annuler' && vernee));
    document.getElementById('verrouSubmit').textContent = action === 'supprimer' ? 'Oui, supprimer' : 'Oui, annuler';
    document.getElementById('verrouSubmit').className = 'flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-lg ' + (action === 'supprimer' ? 'bg-red-600 hover:bg-red-700' : 'bg-orange-500 hover:bg-orange-600');
    document.getElementById('verrouForm').action = action === 'supprimer'
        ? '/secretaire/commandes/' + id
        : '/secretaire/commandes/' + id + '/annuler';
    openModal('verrouModal');
}

// ═══ Renvoi WhatsApp ═══
function showRenvoyerModal(envoiId, tel, erreur) {
    document.getElementById('renvoyerTel').value = tel;
    document.getElementById('renvoyerErreur').textContent = 'Erreur principale : ' + (erreur || 'inconnue');
    document.getElementById('renvoyerForm').action = '/secretaire/whatsapp-envois/' + envoiId + '/renvoyer';
    openModal('renvoyerModal');
}

document.addEventListener('click', e => { if (['soldeModal','verrouModal','renvoyerModal'].includes(e.target.id)) closeModal(e.target.id); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') ['soldeModal','verrouModal','renvoyerModal'].forEach(closeModal); });

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

// ═══ Filtres + recherche ═══
let currentStatut = 'all';
function filterStatut(s) {
    currentStatut = s;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`[data-filter="${s}"]`).classList.add('active');
    applyAllFilters();
}
function applyAllFilters() {
    const d1 = document.getElementById('filterDateDebut').value;
    const d2 = document.getElementById('filterDateFin').value;
    const t = document.getElementById('filterType').value;
    document.querySelectorAll('table tbody tr[data-status]').forEach(row => {
        const s = row.getAttribute('data-status'), dt = row.getAttribute('data-date'), tp = row.getAttribute('data-type');
        let show = true;
        if (currentStatut === 'active' && s === 'annule') show = false;
        if (currentStatut === 'annule' && s !== 'annule') show = false;
        if (d1 && dt < d1) show = false;
        if (d2 && dt > d2) show = false;
        if (t && tp !== t) show = false;
        row.style.display = show ? '' : 'none';
    });
}
function filterSolde() {
    const q = document.getElementById('searchSolde').value.toLowerCase().replace('+','');
    document.querySelectorAll('#soldeRows tr[data-solde]').forEach(row => {
        row.style.display = row.getAttribute('data-solde').includes(q) ? '' : 'none';
    });
}
function resetFilters() {
    document.getElementById('filterDateDebut').value = '';
    document.getElementById('filterDateFin').value = '';
    document.getElementById('filterType').value = '';
    filterStatut('all');
}
</script>
@endpush
