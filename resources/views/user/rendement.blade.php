@extends('layouts.app')

@section('title', 'Mes fiches de rendement')

@section('content')
<div class="container px-4 py-6 mx-auto sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-[#080808] sm:text-3xl">Mes fiches de rendement</h1>
        <p class="mt-2 text-sm text-gray-600">Historique de vos tâches effectuées, enregistrées à chaque départ.</p>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#080808]">
                    <tr>
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
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ date('d/m/Y', strtotime($r->date)) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $r->heureArrivee ? date('H:i', strtotime($r->heureArrivee)) : '-' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $r->heureDepart ? date('H:i', strtotime($r->heureDepart)) : '-' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800">{{ $r->duree ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $r->rendement }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                            Aucune fiche de rendement pour le moment — remplissez-la lors de votre prochain départ.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rendements->isNotEmpty())
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Temps de travail total :</td>
                        <td class="whitespace-nowrap px-6 py-3 text-sm font-bold text-3hcig-blue">{{ $totalDuree }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center text-sm font-medium text-3hcig-blue hover:text-3hcig-blue-light">
            ← Retour au tableau de bord
        </a>
    </div>
</div>
@endsection
