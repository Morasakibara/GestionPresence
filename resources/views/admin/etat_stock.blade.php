@extends('layouts.dashboard')

@section('title', 'État du stock')
@section('breadcrumb', 'État du stock')

@section('header')
    <div>
        <span>État du stock</span>
        <p class="console-subtitle">Inventaire des T-shirts et du papier d'impression, avec seuils d'alerte.</p>
    </div>
    <span class="console-kicker mt-0">{{ now()->format('d/m/Y') }}</span>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="space-y-5">
    <!-- T-Shirts -->
    <div>
        <div class="console-card overflow-hidden">
            <div class="console-card-head">
                <div>
                    <h2 class="section-title">T-Shirts</h2>
                    <p class="console-card-sub">{{ $totalTshirts }} pièce(s) au total</p>
                </div>
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
                                    <td class="px-6 py-4"><span class="badge {{ $t->enAlerte() ? 'badge-danger' : 'badge-success' }}">{{ $t->enAlerte() ? 'Alerte' : 'Correct' }}</span></td>
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
        <div class="console-card overflow-hidden">
            <div class="console-card-head">
                <div>
                    <h2 class="section-title">Papier d'impression</h2>
                    <p class="console-card-sub">Consommation par imprimante, en mètres restants.</p>
                </div>
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
                                    <td class="px-6 py-4"><span class="badge {{ $p->enAlerte() ? 'badge-danger' : 'badge-success' }}">{{ $p->enAlerte() ? 'Alerte' : 'Correct' }}</span></td>
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
