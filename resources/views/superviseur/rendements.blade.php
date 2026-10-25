@extends('layouts.dashboard')

@section('title', 'Rendement de l\'équipe')
@section('breadcrumb', 'Rendements')

@section('header')
    <div>
        <span>Rendement de l'équipe</span>
        <p class="console-subtitle">Fiches de rendement remplies par les membres de votre équipe.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" action="{{ route('superviseur.rendements') }}" class="flex items-center gap-2">
            <label for="date" class="sr-only">Jour</label>
            <input type="date" name="date" id="date" value="{{ $date }}" class="input-field py-2 text-sm">
            <button type="submit" class="btn-secondary">Filtrer</button>
        </form>
        <a href="{{ route('superviseur.rendements.export', ['debut' => $date, 'fin' => $date]) }}" class="btn-gold">
            <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export Excel
        </a>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="space-y-5">

    <!-- Membres n'ayant pas encore rempli leur fiche -->
    @if($membresSansFiche->isNotEmpty())
    <div class="alert alert-warning">
        <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <p class="font-semibold">Membres sans fiche de rendement ce jour-là</p>
            <ul class="mt-1 list-inside list-disc text-sm">
                @foreach($membresSansFiche as $m)
                    <li>{{ $m->nom }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="table-wrap">
        <div class="table-scroll">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-head">
                    <tr>
                        <th scope="col">Employé</th>
                        <th scope="col">Date</th>
                        <th scope="col">Arrivée</th>
                        <th scope="col">Départ</th>
                        <th scope="col">Durée</th>
                        <th scope="col">Tâches effectuées</th>
                    </tr>
                </thead>
                <tbody class="table-body divide-y divide-gray-200 bg-white">
                    @forelse($rendements as $r)
                    <tr class="align-top">
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $r->nom }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ date('d/m/Y', strtotime($r->date)) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $r->heureArrivee ? date('H:i', strtotime($r->heureArrivee)) : '-' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $r->heureDepart ? date('H:i', strtotime($r->heureDepart)) : '-' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800">{{ $r->duree ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $r->rendement }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <h3 class="mt-4 text-sm font-semibold text-gray-900">Aucune fiche de rendement</h3>
                                <p class="mt-1 text-sm text-gray-500">Aucune fiche remplie pour cette date.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rendements->isNotEmpty())
                <tfoot class="border-t border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Temps de travail total du jour :</td>
                        <td class="whitespace-nowrap px-6 py-3 text-sm font-bold text-pharaoh-bronze-dark">{{ $totalDuree }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
            </div>
    </div>
</div>
@endsection
