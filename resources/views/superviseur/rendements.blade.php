@extends('layouts.app')

@section('title', 'Rendement de l\'équipe')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-3hcig-blue-dark sm:text-3xl">Rendement de l'équipe</h1>
            <p class="mt-2 text-sm text-gray-600">Fiches de rendement remplies par les membres de votre équipe.</p>
        </div>
        <form method="GET" action="{{ route('superviseur.rendements') }}" class="flex items-center gap-2">
            <label for="date" class="text-sm text-gray-600">Jour :</label>
            <input type="date" name="date" id="date" value="{{ $date }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm">
            <button type="submit" class="rounded-md bg-3hcig-blue px-4 py-2 text-sm font-medium text-white hover:bg-3hcig-blue-light">Filtrer</button>
        </form>
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
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $r->rendement }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                            Aucune fiche de rendement remplie pour cette date.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
