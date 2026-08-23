@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Services Photo</span></div>
@endsection

@section('navigation')
<a href="{{ route('secretaire.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('secretaire.services') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="form-card">
            <div class="form-card-header">
                <h2 class="form-card-title">Nouveau service</h2>
                <p class="form-card-subtitle">Enregistrer un service photo</p>
            </div>

            @if ($errors->any())
            <div class="bg-red-600/10 border-2 border-red-600 text-red-600 px-4 py-3 rounded-md mb-6">
                <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            <form action="{{ route('secretaire.services.store') }}" method="POST" class="space-y-5">
                @csrf
                <div class="form-group">
                    <label class="input-label">Type de service</label>
                    <select name="type" class="select-field" required>
                        <option value="">— Choisir —</option>
                        @foreach($typesPhoto as $key => $label)
                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="input-label">Montant (FCFA)</label>
                    <input type="number" name="montant" class="input-field" min="0.01" step="any" value="{{ old('montant') }}" required placeholder="0">
                </div>
                <div class="form-group">
                    <label class="input-label">Détails (facultatif)</label>
                    <textarea name="details" rows="2" class="input-field" placeholder="Description du service">{{ old('details') }}</textarea>
                </div>
                <button type="submit" class="btn-primary gap-2">
                    <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h-2v5.586L7.707 10.293z" />
                        <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V8l-4-4H4zm12 12A2 2 0 0114 16H6a2 2 0 01-2-2V6a2 2 0 012-2h7v4h4v6z" clip-rule="evenodd" />
                    </svg>
                    <span>Enregistrer le service</span>
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-[#080808] flex items-center gap-2">
                    <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Services enregistrés</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#080808] text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Heure</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase">Détails</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase">Montant</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($services as $svc)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $svc->created_at->format('H:i') }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-[#080808]">{{ $typesPhoto[$svc->type] ?? $svc->type }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $svc->details ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-right text-blue-600">{{ number_format($svc->montant, 0, ',', '.') }} FCFA</td>
                            <td class="px-4 py-3 text-center space-x-2">
                                <a href="{{ route('secretaire.services.edit', $svc->id) }}" class="text-pharaoh-gold hover:text-pharaoh-gold-light text-sm font-medium">✏️</a>
                                <form action="{{ route('secretaire.services.destroy', $svc->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">📭</div>Aucun service enregistré.
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
<a href="{{ route('secretaire.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Commandes</a>
<a href="{{ route('secretaire.services') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">Services</a>
<a href="{{ route('secretaire.retraits') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rapport</a>
@endsection

