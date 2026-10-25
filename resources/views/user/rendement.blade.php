@extends('layouts.dashboard')

@section('title', 'Mes fiches de rendement')
@section('breadcrumb', 'Mes rendements')

@section('header')
    <div>
        <span>Mes fiches de rendement</span>
        <p class="console-subtitle">Historique de vos tâches effectuées, enregistrées à chaque départ.</p>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="space-y-5">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        @include('partials.dashboard.kpi', ['label' => 'Fiches remplies', 'value' => $rendements->count(), 'meta' => 'Depuis votre arrivée', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'Temps de travail total', 'value' => $totalDuree, 'meta' => 'Cumul des heures pointées', 'tone' => 'default'])
        @include('partials.dashboard.kpi', ['label' => 'Fiche de départ', 'value' => 'Obligatoire', 'meta' => 'À remplir avant de pointer', 'tone' => 'info'])
    </div>

    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Historique de mes fiches</h2>
                <p class="console-card-sub">Tâches décrites au moment de votre départ.</p>
            </div>
            <span class="badge badge-gold">{{ $rendements->count() }} fiche{{ $rendements->count() > 1 ? 's' : '' }}</span>
        </div>

        <div class="table-scroll">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-head">
                    <tr>
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
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ date('d/m/Y', strtotime($r->date)) }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $r->heureArrivee ? date('H:i', strtotime($r->heureArrivee)) : '-' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $r->heureDepart ? date('H:i', strtotime($r->heureDepart)) : '-' }}</td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-800">{{ $r->duree ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $r->rendement }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-sm font-semibold text-gray-900">Aucune fiche de rendement</h3>
                                <p class="mt-1 text-sm text-gray-500">Aucune fiche de rendement pour le moment — remplissez-la lors de votre prochain départ.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rendements->isNotEmpty())
                <tfoot class="border-t border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Temps de travail total :</td>
                        <td class="whitespace-nowrap px-6 py-3 text-sm font-bold text-pharaoh-gold">{{ $totalDuree }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
