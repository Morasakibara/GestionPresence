@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Gestion T-Shirts</span></div>
@endsection

@section('navigation')
<a href="{{ route('gestionnaire.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('gestionnaire.tshirts') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">T-Shirts</a>
<a href="{{ route('gestionnaire.papier') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Papier</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('gestionnaire.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('gestionnaire.tshirts') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">T-Shirts</a>
<a href="{{ route('gestionnaire.papier') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Papier</a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
    @if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif

    <h1 class="page-heading-title mb-6">Gestion des T-Shirts</h1>

    <!-- Formulaire d'ajout -->
    <div class="pharaoh-card p-6 mb-6">
        <h3 class="text-base font-semibold text-[#080808] mb-4">Ajouter / Modifier un T-Shirt</h3>
        <form action="{{ route('gestionnaire.tshirts.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div><label class="input-label">Couleur</label><input type="text" name="couleur" class="input-field mt-1" value="{{ old('couleur') }}" placeholder="Ex: Noir, Blanc..." required></div>
                <div><label class="input-label">Taille</label><input type="text" name="taille" class="input-field mt-1" value="{{ old('taille') }}" placeholder="Ex: S, M, L, XL..." required></div>
                <div><label class="input-label">Quantité</label><input type="number" name="quantite" class="input-field mt-1" min="0" value="{{ old('quantite', 0) }}" required></div>
                <div><label class="input-label">Seuil alerte</label><input type="number" name="seuil_alerte" class="input-field mt-1" min="0" value="{{ old('seuil_alerte', 5) }}"></div>
            </div>
            <button type="submit" class="btn-gold"><svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Enregistrer</button>
        </form>
    </div>

    <!-- Liste -->
    <div class="pharaoh-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200/70"><h2 class="text-lg font-semibold text-[#080808]">Stock T-Shirts ({{ $tshirts->count() }})</h2></div>
        <div class="table-scroll">
            <table class="min-w-full">
                <thead class="table-head"><tr><th>Couleur</th><th>Taille</th><th class="text-right">Quantité</th><th class="text-right">Seuil</th><th>Statut</th><th></th></tr></thead>
                <tbody class="table-body">
                    @forelse($tshirts as $t)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-[#080808]">{{ $t->couleur }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $t->taille }}</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold">{{ $t->quantite }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-500">{{ $t->seuil_alerte }}</td>
                            <td class="px-6 py-4"><span class="badge {{ $t->enAlerte() ? 'badge-danger' : 'badge-success' }}">{{ $t->enAlerte() ? 'Alerte' : 'OK' }}</span></td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('gestionnaire.tshirts.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')<button type="submit" class="text-red-500 hover:text-red-700 text-sm">Supprimer</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">Aucun T-shirt en stock</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
