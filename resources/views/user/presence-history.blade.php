@extends('layouts.app')

@section('title', 'Historique de la présence')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-3hcig-blue-dark sm:text-3xl">Historique de la présence</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Pointage du {{ \Carbon\Carbon::parse($presence->date)->format('d/m/Y') }}
                </p>
            </div>
            <a href="{{ route('user.presence.report') }}" class="text-sm font-medium text-3hcig-blue hover:text-3hcig-blue-light">
                ← Retour au bilan
            </a>
        </div>

        <!-- Récapitulatif de la présence -->
        <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-lg bg-3hcig-blue-light/10 p-4 text-center">
                    <p class="text-xs text-gray-500">Arrivée</p>
                    <p class="mt-1 text-lg font-bold text-3hcig-blue">{{ $presence->heureArrivee ? \Carbon\Carbon::parse($presence->heureArrivee)->format('H:i') : '—' }}</p>
                </div>
                <div class="rounded-lg bg-3hcig-blue-light/10 p-4 text-center">
                    <p class="text-xs text-gray-500">Départ</p>
                    <p class="mt-1 text-lg font-bold text-3hcig-blue">{{ $presence->heureDepart ? \Carbon\Carbon::parse($presence->heureDepart)->format('H:i') : '—' }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4 text-center">
                    <p class="text-xs text-gray-500">Statut</p>
                    <p class="mt-1 text-lg font-bold text-gray-800">{{ ucfirst($presence->status ?? '—') }}</p>
                </div>
                <div class="rounded-lg p-4 text-center {{ $presence->suspect ? 'bg-red-50' : 'bg-green-50' }}">
                    <p class="text-xs text-gray-500">Suspicion</p>
                    <p class="mt-1 text-lg font-bold {{ $presence->suspect ? 'text-red-600' : 'text-green-600' }}">
                        {{ $presence->suspect ? 'Suspecte' : 'Normale' }}
                    </p>
                </div>
            </div>

            @if($presence->suspect)
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50/50 p-4">
                    <p class="text-sm font-medium text-red-800">Motif de suspicion</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $presence->motif_suspicion ?? 'Non renseigné' }}</p>
                    @if($presence->distance_km !== null || $presence->vitesse_kmh !== null)
                        <p class="mt-2 text-xs text-gray-500">
                            @if($presence->distance_km !== null) Distance : {{ number_format($presence->distance_km, 2, ',', ' ') }} km @endif
                            @if($presence->vitesse_kmh !== null) · Vitesse : {{ number_format($presence->vitesse_kmh, 1, ',', ' ') }} km/h @endif
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Timeline -->
        <div class="mt-6 overflow-hidden rounded-lg bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-3hcig-blue-dark">Suivi complet</h2>

            <ol class="relative mt-6 ml-3 space-y-6 border-l-2 border-gray-200">
                <!-- Pointage arrivée -->
                <li class="ml-6">
                    <span class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full border-2 border-white bg-3hcig-blue"></span>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900">Arrivée pointée</p>
                        <span class="text-xs text-gray-500">{{ $presence->heureArrivee ? \Carbon\Carbon::parse($presence->heureArrivee)->format('d/m/Y H:i') : '—' }}</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        @if($presence->localisation_validee_arrivee) Localisation validée ✅ @else Localisation non validée @endif
                    </p>
                </li>

                <!-- Départ -->
                <li class="ml-6">
                    <span class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full border-2 border-white bg-3hcig-blue"></span>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900">Départ pointé</p>
                        <span class="text-xs text-gray-500">{{ $presence->heureDepart ? \Carbon\Carbon::parse($presence->heureDepart)->format('d/m/Y H:i') : '—' }}</span>
                    </div>
                </li>

                <!-- Suspicion -->
                @if($presence->suspect)
                <li class="ml-6">
                    <span class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full border-2 border-white bg-red-500"></span>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium text-red-700">⚠️ Présence marquée suspecte</p>
                    </div>
                    <p class="mt-1 text-xs text-gray-600">{{ $presence->motif_suspicion }}</p>
                </li>
                @endif

                <!-- Contestation -->
                @if($presence->commentaire_contestation)
                <li class="ml-6">
                    <span class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full border-2 border-white bg-amber-500"></span>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium text-amber-700">Contestation envoyée</p>
                        <span class="text-xs text-gray-500">{{ $presence->conteste_le ? \Carbon\Carbon::parse($presence->conteste_le)->format('d/m/Y H:i') : '' }}</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-600">« {{ $presence->commentaire_contestation }} »</p>
                </li>
                @endif

                <!-- Réponse admin à la contestation -->
                @if($presence->reponse_contestation)
                <li class="ml-6">
                    <span class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full border-2 border-white {{ $presence->reponse_contestation === 'accordé' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium {{ $presence->reponse_contestation === 'accordé' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $presence->reponse_contestation === 'accordé' ? '✅ Contestation acceptée' : '❌ Contestation refusée' }}
                        </p>
                        <span class="text-xs text-gray-500">{{ $presence->reponse_contestation_le ? \Carbon\Carbon::parse($presence->reponse_contestation_le)->format('d/m/Y H:i') : '' }}</span>
                    </div>
                    @if($presence->commentaire_reponse_contestation)
                        <p class="mt-1 text-xs text-gray-600">« {{ $presence->commentaire_reponse_contestation }} »</p>
                    @endif
                </li>
                @endif

                <!-- Historique des statuts de traitement -->
                @forelse($traitements as $traitement)
                <li class="ml-6">
                    <span class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full border-2 border-white bg-gray-400"></span>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900">
                            Statut : {{ ucfirst($traitement->statut_avant) }} → {{ ucfirst($traitement->statut_apres) }}
                        </p>
                        <span class="text-xs text-gray-500">{{ $traitement->created_at ? \Carbon\Carbon::parse($traitement->created_at)->format('d/m/Y H:i') : '' }}</span>
                    </div>
                    @if($traitement->commentaire)
                        <p class="mt-1 text-xs text-gray-600">« {{ $traitement->commentaire }} »</p>
                    @endif
                </li>
                @empty
                    @if(!$presence->suspect && !$presence->commentaire_contestation)
                    <li class="ml-6">
                        <span class="absolute -left-1.5 mt-1 h-3 w-3 rounded-full border-2 border-white bg-green-500"></span>
                        <p class="text-sm font-medium text-gray-900">Aucun signalement — présence normale ✅</p>
                    </li>
                    @endif
                @endforelse
            </ol>
        </div>
    </div>
</div>
@endsection
