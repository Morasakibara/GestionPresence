@extends('layouts.dashboard')

@section('title', 'Stock T-Shirts')
@section('breadcrumb', 'Stock T-Shirts')

@section('header')
    <div>
        <span>Stock T-Shirts</span>
        <p class="console-subtitle">Références par couleur et par taille, avec seuils d'alerte.</p>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="space-y-5">
    <!-- Formulaire d'ajout -->
    <div class="console-card p-6">
        <h3 class="section-title mb-4">Ajouter / Modifier un T-Shirt</h3>
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
    <div class="console-card overflow-hidden">
        <div class="console-card-head pb-3"><h2 class="section-title">Stock T-Shirts ({{ $tshirts->count() }})</h2></div>
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
