@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-3hcig-blue-dark">Rapport de présence et de rendement</h1>
            <p class="text-gray-600">Période: <span class="font-medium">{{ request('start_date') }} - {{ request('end_date') }}</span></p>
        </div>

        @php
            $couleurBadge = [
                'vert' => 'bg-green-100 text-green-800',
                'orange' => 'bg-orange-100 text-orange-800',
                'rouge' => 'bg-red-100 text-red-800',
            ];
        @endphp

        <div class="overflow-x-auto">
            <table class="w-full border-collapse mb-6">
                <thead>
                    <tr class="bg-3hcig-blue text-white">
                        <th class="border border-gray-300 px-4 py-3 text-left">Nom de l'employé</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Total Présence</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Total Heures</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Évaluation</th>
                        <th class="border border-gray-300 px-4 py-3 text-left">Réalisations (fiches de rendement)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $data)
                    <tr class="hover:bg-gray-100 align-top">
                        <td class="border border-gray-300 px-4 py-3 font-medium">
                            {{ $data->employer_nom }}
                            <div class="mt-1">
                                <a href="{{ route('admin.evaluation.bulletin', ['id' => $data->employerID, 'mois' => substr($startDate, 0, 7)]) }}" class="inline-flex items-center gap-1 text-xs font-medium text-3hcig-blue hover:text-3hcig-blue-light" title="Télécharger le bulletin individuel du mois">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    Bulletin PDF
                                </a>
                            </div>
                        </td>
                        <td class="border border-gray-300 px-4 py-3">{{ $data->total_presence }}</td>
                        <td class="border border-gray-300 px-4 py-3 font-medium">{{ $data->total_heures ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $couleurBadge[$data->evaluation_couleur] ?? $couleurBadge['orange'] }}">
                                {{ $data->evaluation_note }}/20
                            </span>
                            @if($data->evaluation_manuelle)
                                <span class="ml-1 text-xs text-gray-500">(manuelle)</span>
                            @endif
                            <div class="mt-1 text-xs text-gray-600 max-w-xs">{{ $data->evaluation_commentaire }}</div>

                            <!-- Formulaire d'évaluation manuelle (fondateur) -->
                            <form action="{{ route('admin.storeEvaluation') }}" method="POST" class="mt-2 space-y-1">
                                @csrf
                                <input type="hidden" name="employerID" value="{{ $data->employerID }}">
                                <input type="hidden" name="mois" value="{{ substr($startDate, 0, 7) }}">
                                <div class="flex items-center gap-1">
                                    <input type="number" name="note" min="0" max="20" step="0.5" value="{{ $data->evaluation_note }}" class="w-16 rounded border border-gray-300 px-2 py-1 text-xs" title="Note sur 20">
                                    <select name="couleur" class="rounded border border-gray-300 px-1 py-1 text-xs">
                                        <option value="vert" {{ $data->evaluation_couleur === 'vert' ? 'selected' : '' }}>🟢 Vert</option>
                                        <option value="orange" {{ $data->evaluation_couleur === 'orange' ? 'selected' : '' }}>🟠 Orange</option>
                                        <option value="rouge" {{ $data->evaluation_couleur === 'rouge' ? 'selected' : '' }}>🔴 Rouge</option>
                                    </select>
                                </div>
                                <input type="text" name="commentaire" placeholder="Commentaire (optionnel)" class="w-full rounded border border-gray-300 px-2 py-1 text-xs">
                                <button type="submit" class="rounded bg-3hcig-blue px-2 py-1 text-xs text-white hover:bg-3hcig-blue-light">Enregistrer l'évaluation</button>
                            </form>
                        </td>
                        <td class="border border-gray-300 px-4 py-3">
                            @if(count($data->rendements) > 0)
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                    @foreach($data->rendements as $rendement)
                                        <li>{{ \Illuminate\Support\Str::limit($rendement, 180) }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-sm text-gray-400">Aucune fiche de rendement remplie.</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="border border-gray-300 px-4 py-6 text-center text-gray-500">
                            Aucune donnée de présence pour la période sélectionnée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <form action="{{ route('admin.evaluations.export') }}" method="GET">
                <input type="hidden" name="mois" value="{{ substr($startDate, 0, 7) }}">
                <button type="submit" class="bg-3hcig-blue hover:bg-3hcig-blue-light text-white font-bold py-2 px-6 rounded-lg transition-colors duration-300 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV évaluations & rendements
                </button>
            </form>
            <form action="{{ route('admin.exportReport') }}" method="POST">
                @csrf
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <button type="submit" class="bg-3hcig-green hover:bg-3hcig-green-light text-white font-bold py-2 px-6 rounded-lg transition-colors duration-300 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Exporter en PDF
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
