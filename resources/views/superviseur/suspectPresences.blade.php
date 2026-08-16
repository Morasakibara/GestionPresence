@extends('layouts.app')

@section('title', 'Présences suspectes de l\'équipe')

@section('content')
<div class="container px-4 py-6 mx-auto sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Présences suspectes de l'équipe</h1>
        <p class="mt-2 text-sm text-gray-600">
            Pointages de votre équipe détectés par l'anti-triche de géolocalisation (vitesse irréaliste, précision GPS trop faible, etc.)
        </p>
    </div>

    @if (session('success'))
        <div class="p-4 mb-6 rounded-md bg-3hcig-green-light/20 text-3hcig-green-dark">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 mb-6 text-red-600 rounded-md bg-red-50">
            {{ session('error') }}
        </div>
    @endif

    <div class="p-6 mb-6 rounded-lg bg-white shadow-sm">
        <form method="GET" action="{{ route('superviseur.suspectPresences') }}" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                <div class="md:col-span-2">
                    <input type="text" name="search" id="search"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:ring focus:ring-3hcig-blue focus:ring-opacity-20 sm:text-sm"
                        placeholder="Rechercher par nom d'employé..." value="{{ old('search', $search) }}">
                </div>
                <div>
                    <input type="date" name="start_date" id="start_date"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:ring focus:ring-3hcig-blue focus:ring-opacity-20 sm:text-sm"
                        value="{{ old('start_date', $startDate) }}">
                </div>
                <div>
                    <input type="date" name="end_date" id="end_date"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:ring focus:ring-3hcig-blue focus:ring-opacity-20 sm:text-sm"
                        value="{{ old('end_date', $endDate) }}">
                </div>
                <div>
                    <select name="statut" id="statut"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:ring focus:ring-3hcig-blue focus:ring-opacity-20 sm:text-sm">
                        <option value="">Tous les statuts</option>
                        @foreach(['nouveau', 'examiné', 'justifié', 'rejeté'] as $option)
                            <option value="{{ $option }}" {{ ($statut ?? '') === $option ? 'selected' : '' }}>{{ ucfirst($option) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white rounded-md bg-3hcig-blue shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-3hcig-blue-dark">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Employé</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Arrivée</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Départ</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Vitesse</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Motif de suspicion</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Contestation</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($suspectPresences as $presence)
                        <tr class="hover:bg-red-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $presence->employer_nom }}</div>
                                <div class="text-sm text-gray-500">{{ $presence->employer_email }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                {{ \Carbon\Carbon::parse($presence->date)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                {{ $presence->heureArrivee ? \Carbon\Carbon::parse($presence->heureArrivee)->format('H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                {{ $presence->heureDepart ? \Carbon\Carbon::parse($presence->heureDepart)->format('H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                {{ $presence->vitesse_kmh !== null ? number_format($presence->vitesse_kmh, 1, ',', ' ') . ' km/h' : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                    {{ $presence->motif_suspicion ?? 'Aucun motif renseigné' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @if($presence->commentaire_contestation)
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">Contesté</span>
                                    <div class="mt-1 max-w-xs text-xs text-gray-600">« {{ $presence->commentaire_contestation }} »</div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <p class="mt-2 text-sm font-medium text-gray-900">Aucune présence suspecte dans votre équipe</p>
                                <p class="mt-1 text-sm text-gray-500">Tous les pointages validés sont cohérents avec la géolocalisation.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($suspectPresences->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $suspectPresences->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
