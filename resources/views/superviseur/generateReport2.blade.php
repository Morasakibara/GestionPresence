@extends('layouts.app')

@section('title', 'Rapport d\'équipe')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-3hcig-blue-dark sm:text-3xl">Rapport d'équipe</h1>
        <p class="mt-2 text-sm text-gray-600">Consultez les statistiques de présence et le rendement de votre équipe (mois en cours)</p>
    </div>

    @php
        $couleurBadge = [
            'vert' => 'bg-green-100 text-green-800',
            'orange' => 'bg-orange-100 text-orange-800',
            'rouge' => 'bg-red-100 text-red-800',
        ];
    @endphp

    <div class="mt-8 overflow-hidden rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-3hcig-blue-dark">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Nom de l'employé</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Total de Présences (Mois en cours)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Évaluation</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Réalisations (rendement)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($reports as $report)
                    <tr class="hover:bg-gray-50 align-top">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $report['name'] }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <span class="inline-flex items-center rounded-full bg-3hcig-blue-light/10 px-3 py-0.5 text-sm font-medium text-3hcig-blue-dark">
                                    {{ $report['totalPresences'] }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $couleurBadge[$report['evaluation_couleur']] ?? $couleurBadge['orange'] }}">
                                {{ $report['evaluation_note'] }}/20
                            </span>
                            @if($report['evaluation_manuelle'])
                                <span class="ml-1 text-xs text-gray-500">(manuelle)</span>
                            @endif
                            <div class="mt-1 text-xs text-gray-600 max-w-xs">{{ $report['evaluation_commentaire'] }}</div>

                            <!-- Formulaire d'évaluation manuelle (directeur / directeur adjoint) -->
                            <form action="{{ route('superviseur.storeEvaluation') }}" method="POST" class="mt-2 space-y-1">
                                @csrf
                                <input type="hidden" name="employerID" value="{{ $report['employerID'] ?? '' }}">
                                <input type="hidden" name="mois" value="{{ now()->format('Y-m') }}">
                                <div class="flex items-center gap-1">
                                    <input type="number" name="note" min="0" max="20" step="0.5" value="{{ $report['evaluation_note'] }}" class="w-16 rounded border border-gray-300 px-2 py-1 text-xs" title="Note sur 20">
                                    <select name="couleur" class="rounded border border-gray-300 px-1 py-1 text-xs">
                                        <option value="vert" {{ $report['evaluation_couleur'] === 'vert' ? 'selected' : '' }}>🟢 Vert</option>
                                        <option value="orange" {{ $report['evaluation_couleur'] === 'orange' ? 'selected' : '' }}>🟠 Orange</option>
                                        <option value="rouge" {{ $report['evaluation_couleur'] === 'rouge' ? 'selected' : '' }}>🔴 Rouge</option>
                                    </select>
                                </div>
                                <input type="text" name="commentaire" placeholder="Commentaire (optionnel)" class="w-full rounded border border-gray-300 px-2 py-1 text-xs">
                                <button type="submit" class="rounded bg-3hcig-blue px-2 py-1 text-xs text-white hover:bg-3hcig-blue-light">Enregistrer l'évaluation</button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            @if(count($report['rendements']) > 0)
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                    @foreach($report['rendements'] as $rendement)
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
                        <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500">
                            Aucun rapport disponible — il n'y a aucune donnée de présence pour le mois en cours.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <a href="{{ route('export.pdf') }}" class="inline-flex items-center rounded-md bg-3hcig-blue px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exporter en PDF
        </a>
    </div>
</div>
@endsection
