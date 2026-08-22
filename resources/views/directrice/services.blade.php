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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ═══ FORMULAIRE ═══ -->
    <div class="lg:col-span-1">
        <div class="form-card">
            <div class="form-card-header">
                <h2 class="form-card-title">Nouveau service</h2>
                <p class="form-card-subtitle">Enregistrer un service fourni</p>
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
                    <label class="input-label">Montant (FCFA)</label>
                    <input type="number" name="montant" class="input-field" min="0.01" step="any" value="{{ old('montant') }}" required placeholder="0">
                </div>
                <div class="form-group">
                    <label class="input-label">Détails (facultatif)</label>
                    <textarea name="details" rows="2" class="input-field" placeholder="Description du service">{{ old('details') }}</textarea>
                </div>
                <button type="submit" class="btn-primary">💾 Enregistrer le service</button>
            </form>
        </div>
    </div>

    <!-- ═══ LISTE DES SERVICES ═══ -->
    <div class="lg:col-span-2">
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-[#080808]">📋 Services enregistrés</h3>
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
                            <td class="px-4 py-3 text-sm font-medium text-[#080808]">{{ $typesCaisse[$svc->type] ?? $svc->type }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $svc->details ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-right text-blue-600">{{ number_format($svc->montant, 0, ',', '.') }} FCFA</td>
                            <td class="px-4 py-3 text-center space-x-2">
                                <a href="{{ route('directrice.services.edit', $svc->id) }}" class="text-pharaoh-gold hover:text-pharaoh-gold-light text-sm font-medium">✏️</a>
                                <form action="{{ route('directrice.services.destroy', $svc->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ?')">
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
