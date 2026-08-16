@extends('layouts.app')

@section('title', 'Statistiques des suspicions — Équipe')

@section('content')
<div class="container px-4 py-6 mx-auto sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Statistiques des suspicions — Équipe</h1>
        <p class="mt-2 text-sm text-gray-600">
            Vue des pointages suspects de <strong>votre équipe</strong> : suspicions, traitements, contestations et blocages.
        </p>
    </div>

    <!-- Cartes principales -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Présences suspectes</p>
            <p class="mt-1 text-3xl font-bold text-red-600">{{ $totalSuspectes }}</p>
            <p class="mt-1 text-xs text-gray-400">dans votre équipe</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Contestations</p>
            <p class="mt-1 text-3xl font-bold text-amber-600">{{ $totalContestations }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $contestationsEnAttente }} en attente de réponse</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Membres bloqués</p>
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
            <h2 class="text-lg font-bold text-3hcig-blue-dark">Contestations des membres</h2>
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

        <!-- Membres bloqués -->
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-3hcig-blue-dark">Membres actuellement bloqués</h2>
            @if($employesBloques->isEmpty())
                <p class="mt-4 text-sm text-gray-500">Aucun membre bloqué — tous les pointages de l'équipe sont cohérents. ✅</p>
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
                <a href="{{ route('superviseur.suspectPresences') }}" class="mt-3 inline-block text-sm font-medium text-3hcig-blue hover:text-3hcig-blue-light">
                    Voir les présences suspectes de l'équipe →
                </a>
            @endif
        </div>
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
