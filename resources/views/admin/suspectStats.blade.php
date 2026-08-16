@extends('layouts.app')

@section('title', 'Statistiques des présences suspectes')

@section('content')
<div class="container px-4 py-6 mx-auto sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Statistiques des présences suspectes</h1>
            <p class="mt-2 text-sm text-gray-600">
                Vue globale de l'anti-triche : suspicions, traitements, contestations et blocages.
            </p>
        </div>
        <a href="{{ route('admin.suspectStats.pdf') }}" class="inline-flex items-center gap-2 rounded-md bg-3hcig-blue px-4 py-2 text-sm font-medium text-white hover:bg-3hcig-blue-light">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exporter PDF
        </a>
    </div>

    <!-- Cartes principales -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Présences suspectes</p>
            <p class="mt-1 text-3xl font-bold text-red-600">{{ $totalSuspectes }}</p>
            <p class="mt-1 text-xs text-gray-400">au total</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Contestations</p>
            <p class="mt-1 text-3xl font-bold text-amber-600">{{ $totalContestations }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $contestationsEnAttente }} en attente de réponse</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Employés bloqués</p>
            <p class="mt-1 text-3xl font-bold text-gray-800">{{ $employesBloques->count() }}</p>
            <p class="mt-1 text-xs text-gray-400">seuil ≥ {{ $blocageMax }} suspectes sur {{ $blocageJours }} j</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Non traitées</p>
            <p class="mt-1 text-3xl font-bold text-gray-800">{{ $parStatut['nouveau'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-gray-400">statut « nouveau »</p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Répartition par statut -->
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-3hcig-blue-dark">Répartition par statut de traitement</h2>
            <div class="mt-4 space-y-3">
                @php
                    $statutLabels = ['nouveau' => ['En attente', 'bg-yellow-100 text-yellow-800'], 'examiné' => ['Examinées', 'bg-blue-100 text-blue-800'], 'justifié' => ['Justifiées', 'bg-green-100 text-green-800'], 'rejeté' => ['Rejetées', 'bg-red-100 text-red-800']];
                @endphp
                @foreach(['nouveau', 'examiné', 'justifié', 'rejeté'] as $cle)
                    @php
                        $count = $parStatut[$cle] ?? 0;
                        $pct = $totalSuspectes > 0 ? round(($count / $totalSuspectes) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-28 text-sm text-gray-600">{{ $statutLabels[$cle][0] }}</span>
                        <div class="h-4 flex-1 overflow-hidden rounded-full bg-gray-200">
                            <div class="h-4 rounded-full {{ $statutLabels[$cle][1].' bg-opacity-60' }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="w-10 text-right text-sm font-bold text-gray-800">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Motifs -->
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-3hcig-blue-dark">Motifs de suspicion</h2>
            <div class="mt-4 space-y-3">
                @foreach($motifCounts as $label => $count)
                    @php $pct = $totalSuspectes > 0 ? round(($count / $totalSuspectes) * 100) : 0; @endphp
                    <div class="flex items-center gap-3">
                        <span class="w-40 text-sm text-gray-600">{{ $label }}</span>
                        <div class="h-4 flex-1 overflow-hidden rounded-full bg-gray-200">
                            <div class="h-4 rounded-full bg-red-500 bg-opacity-60" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="w-10 text-right text-sm font-bold text-gray-800">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Contestations -->
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-3hcig-blue-dark">Contestations des employés</h2>
            <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-lg bg-amber-50 p-4">
                    <p class="text-2xl font-bold text-amber-600">{{ $contestationsEnAttente }}</p>
                    <p class="mt-1 text-xs text-gray-500">En attente</p>
                </div>
                <div class="rounded-lg bg-green-50 p-4">
                    <p class="text-2xl font-bold text-green-600">{{ $contestationsAccordees }}</p>
                    <p class="mt-1 text-xs text-gray-500">Acceptées</p>
                </div>
                <div class="rounded-lg bg-red-50 p-4">
                    <p class="text-2xl font-bold text-red-600">{{ $contestationsRefusees }}</p>
                    <p class="mt-1 text-xs text-gray-500">Refusées</p>
                </div>
            </div>
        </div>

        <!-- Employés bloqués -->
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-3hcig-blue-dark">Employés actuellement bloqués</h2>
            @if($employesBloques->isEmpty())
                <p class="mt-4 text-sm text-gray-500">Aucun employé bloqué — tous les pointages sont cohérents. ✅</p>
            @else
                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach($employesBloques as $emp)
                        <li class="flex items-center justify-between py-2">
                            <span class="text-sm font-medium text-gray-800">{{ $emp->nom }}</span>
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                {{ $emp->suspectes }} suspecte(s)
                            </span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('admin.suspectPresences') }}" class="mt-3 inline-block text-sm font-medium text-3hcig-blue hover:text-3hcig-blue-light">
                    Traiter les présences suspectes →
                </a>
            @endif
        </div>
    </div>

    <!-- Détail par employé -->
    <div class="mt-6 rounded-lg bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-3hcig-blue-dark">Détail par employé</h2>
        @if($detailParEmploye->isEmpty())
            <p class="mt-4 text-sm text-gray-500">Aucune présence suspecte — le tableau sera vide.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-2">Employé</th>
                            <th class="px-4 py-2 text-center">Total</th>
                            <th class="px-4 py-2 text-center">En attente</th>
                            <th class="px-4 py-2 text-center">Examinées</th>
                            <th class="px-4 py-2 text-center">Justifiées</th>
                            <th class="px-4 py-2 text-center">Rejetées</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($detailParEmploye as $ligne)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $ligne->nom }}</td>
                                <td class="px-4 py-2 text-center font-bold">{{ $ligne->total }}</td>
                                <td class="px-4 py-2 text-center"><span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs text-yellow-800">{{ $ligne->nouveau }}</span></td>
                                <td class="px-4 py-2 text-center"><span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800">{{ $ligne->examine }}</span></td>
                                <td class="px-4 py-2 text-center"><span class="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800">{{ $ligne->justifie }}</span></td>
                                <td class="px-4 py-2 text-center"><span class="rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-800">{{ $ligne->rejete }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Évolution mensuelle -->
    <div class="mt-6 rounded-lg bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-3hcig-blue-dark">Évolution mensuelle (6 derniers mois)</h2>
        <div class="mt-4 flex h-48 items-end gap-4">
            @foreach($evolutionMensuelle as $ev)
                @php
                    $max = max(array_column($evolutionMensuelle, 'total')) ?: 1;
                    $height = max(4, round(($ev['total'] / $max) * 100));
                @endphp
                <div class="flex flex-1 flex-col items-center gap-2">
                    <span class="text-sm font-bold text-gray-800">{{ $ev['total'] }}</span>
                    <div class="w-full max-w-12 rounded-t-lg bg-3hcig-blue" style="height: {{ $height }}px"></div>
                    <span class="text-xs text-gray-500">{{ $ev['mois'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
