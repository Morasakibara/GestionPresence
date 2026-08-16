@extends('layouts.app')

@section('title', 'Rendement de l\'équipe')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-3hcig-blue-dark sm:text-3xl">Rendement de l'équipe</h1>
            <p class="mt-2 text-sm text-gray-600">Fiches de rendement remplies par les membres de votre équipe.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('superviseur.rendements') }}" class="flex items-center gap-2">
                <label for="date" class="text-sm text-gray-600">Jour :</label>
                <input type="date" name="date" id="date" value="{{ $date }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
                <button type="submit" class="rounded-md bg-3hcig-blue px-4 py-2 text-sm font-medium text-white hover:bg-3hcig-blue-light">Filtrer</button>
            </form>
            <a href="{{ route('superviseur.rendements.export', ['debut' => $date, 'fin' => $date]) }}" class="inline-flex items-center gap-2 rounded-md bg-3hcig-green px-4 py-2 text-sm font-medium text-white hover:bg-3hcig-green-light">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Membres n'ayant pas encore rempli leur fiche -->
    @if($membresSansFiche->isNotEmpty())
    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
        <h2 class="text-sm font-semibold text-amber-800">Membres sans fiche de rendement ce jour-là</h2>
        <ul class="mt-2 list-inside list-disc text-sm text-amber-700">
            @foreach($membresSansFiche as $m)
                <li>{{ $m->nom }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-3hcig-blue-dark">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Employé</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Arrivée</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Départ</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Durée</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Tâches effectuées</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($rendements as $r)
                    <tr class="hover:bg-gray-50 align-top">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $r->nom }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ date('d/m/Y', strtotime($r->date)) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $r->heureArrivee ? date('H:i', strtotime($r->heureArrivee)) : '-' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $r->heureDepart ? date('H:i', strtotime($r->heureDepart)) : '-' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800">{{ $r->duree ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $r->rendement }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                            Aucune fiche de rendement remplie pour cette date.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rendements->isNotEmpty())
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Temps de travail total du jour :</td>
                        <td class="whitespace-nowrap px-6 py-3 text-sm font-bold text-3hcig-blue">{{ $totalDuree }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
