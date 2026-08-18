@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>État du Stock — Admin</span></div>
@endsection

@section('navigation')
<a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('admin.caisse') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Caisse</a>
<a href="{{ route('admin.stock') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Stock</a>
<a href="{{ route('admin.addEmployee') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Ajouter employé</a>
<a href="{{ route('admin.generateReport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Générer Bilan</a>
<a href="{{ route('admin.showEmployeeList') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Liste des employés</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('admin.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Tableau de bord</a>
<a href="{{ route('admin.caisse') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Caisse</a>
<a href="{{ route('admin.stock') }}" class="block rounded-md bg-pharaoh-gold px-3 py-2 text-base font-medium text-white">Stock</a>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="page-heading-title mb-6">État du stock — {{ now()->format('d/m/Y') }}</h1>

    <!-- T-Shirts -->
    <div class="mb-6">
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200/70 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-[#080808]">T-Shirts ({{ $totalTshirts }} au total)</h2>
                <span class="badge badge-info">{{ $tshirts->count() }} référence(s)</span>
            </div>
            @if($tshirts->isEmpty())
                <div class="empty-state py-12">
                    <p class="text-sm text-gray-400">Aucun T-shirt en stock</p>
                </div>
            @else
                <div class="table-scroll">
                    <table class="min-w-full">
                        <thead class="table-head"><tr><th>Couleur</th><th>Taille</th><th class="text-right">Quantité</th><th class="text-right">Seuil</th><th>Statut</th></tr></thead>
                        <tbody class="table-body">
                            @foreach($tshirts as $t)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-[#080808]">{{ $t->couleur }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $t->taille }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold">{{ $t->quantite }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-500">{{ $t->seuil_alerte }}</td>
                                    <td class="px-6 py-4"><span class="badge {{ $t->enAlerte() ? 'badge-danger' : 'badge-success' }}">{{ $t->enAlerte() ? '⚠ Alerte' : '✓ OK' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Papier -->
    <div>
        <div class="pharaoh-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200/70 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-[#080808]">Papier d'impression</h2>
                <span class="badge badge-info">{{ $papiers->count() }} imprimante(s)</span>
            </div>
            @if($papiers->isEmpty())
                <div class="empty-state py-12">
                    <p class="text-sm text-gray-400">Aucun stock de papier enregistré</p>
                </div>
            @else
                <div class="table-scroll">
                    <table class="min-w-full">
                        <thead class="table-head"><tr><th>Imprimante</th><th class="text-right">Reste (m)</th><th class="text-right">Total (m)</th><th class="text-right">%</th><th>Statut</th></tr></thead>
                        <tbody class="table-body">
                            @foreach($papiers as $p)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-[#080808]">{{ $p->imprimante }}</td>
                                    <td class="px-6 py-4 text-sm text-right font-semibold">{{ number_format($p->metres_restants, 1) }}</td>
                                    <td class="px-6 py-4 text-sm text-right text-gray-500">{{ number_format($p->metres_total, 1) }}</td>
                                    <td class="px-6 py-4 text-sm text-right">{{ $p->pourcentageRestant() }}%</td>
                                    <td class="px-6 py-4"><span class="badge {{ $p->enAlerte() ? 'badge-danger' : 'badge-success' }}">{{ $p->enAlerte() ? '⚠ Alerte' : '✓ OK' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
