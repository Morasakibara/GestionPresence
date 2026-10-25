@extends('layouts.dashboard')

@section('title', 'Bilan de présence du mois')
@section('breadcrumb', 'Bilan de présence')

@section('header')
    <div>
        <span>Bilan de présence du mois</span>
        <p class="console-subtitle">Récapitulatif de vos présences et absences sur le mois en cours.</p>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
@php
    $presenceRate = $totalPresences + $totalAbsences > 0
        ? round(($totalPresences / ($totalPresences + $totalAbsences)) * 100)
        : 0;

    $lastPresence = App\Models\Presence::where('employerID', Auth::user()->id)
        ->whereNotNull('latitude_arrivee')
        ->orderBy('date', 'desc')
        ->first();
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        @include('partials.dashboard.kpi', ['label' => 'Présences', 'value' => $totalPresences, 'meta' => 'Jours ce mois-ci', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'Absences', 'value' => $totalAbsences, 'meta' => 'Jours ce mois-ci', 'tone' => 'danger'])
        @include('partials.dashboard.kpi', ['label' => 'Taux de présence', 'value' => $presenceRate . '%', 'meta' => 'Présences / jours pointés', 'tone' => 'success'])
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="console-card xl:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Présences et absences du mois</h2>
                    <p class="console-card-sub">Répartition jour par jour sur le mois en cours.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5"><span class="console-dot bg-[#D39B23]"></span> Présences</span>
                    <span class="flex items-center gap-1.5"><span class="console-dot bg-[#D64545]"></span> Absences</span>
                </div>
            </div>
            <div class="console-card-body">
                <div class="relative h-64 sm:h-72">
                    <canvas id="presenceChart" role="img" aria-label="Présences et absences du mois"></canvas>
                </div>
            </div>
        </div>

        <aside class="space-y-5">
            <div class="console-card">
                <div class="console-card-body">
                    <h2 class="console-card-title">Taux de présence</h2>
                    <p class="mt-3 text-3xl font-extrabold tabular-nums text-pharaoh-gold">{{ $presenceRate }}%</p>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                        <div class="h-2 rounded-full bg-pharaoh-gold" style="width: {{ $presenceRate }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">{{ $totalPresences }} présence(s) et {{ $totalAbsences }} absence(s) enregistrées ce mois-ci.</p>
                </div>
            </div>

            <div class="console-card">
                <div class="console-card-body">
                    <h2 class="console-card-title">Dernier pointage</h2>
                    <p class="mt-1 text-xs text-gray-500">État de validation GPS de votre dernière arrivée.</p>

                    <div class="mt-3">
                        @if($lastPresence && ($lastPresence->localisation_validee_arrivee || $lastPresence->localisation_validee_depart))
                            <span class="badge badge-success">
                                <svg class="-ml-0.5 mr-1 h-2 w-2" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true">
                                    <circle cx="4" cy="4" r="3" />
                                </svg>
                                Validée
                            </span>
                        @elseif($lastPresence)
                            <span class="badge badge-warning">
                                <svg class="-ml-0.5 mr-1 h-2 w-2" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true">
                                    <circle cx="4" cy="4" r="3" />
                                </svg>
                                Non validée
                            </span>
                        @else
                            <span class="badge badge-info">
                                <svg class="-ml-0.5 mr-1 h-2 w-2" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true">
                                    <circle cx="4" cy="4" r="3" />
                                </svg>
                                Non disponible
                            </span>
                        @endif
                    </div>

                    @if($lastPresence)
                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="console-panel-row">
                                <dt class="text-gray-500">Date</dt>
                                <dd class="font-medium text-[#080808]">{{ \Illuminate\Support\Carbon::parse($lastPresence->date)->format('d/m/Y') }}</dd>
                            </div>
                            <div class="console-panel-row">
                                <dt class="text-gray-500">Arrivée</dt>
                                <dd class="font-medium text-[#080808]">{{ $lastPresence->heureArrivee ? \Illuminate\Support\Carbon::parse($lastPresence->heureArrivee)->format('H:i') : '-' }}</dd>
                            </div>
                        </dl>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/pharaoh-charts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    PharaohCharts.bar(document.getElementById('presenceChart'), {
        labels: @json($presences->pluck('date')->toArray()),
        datasets: [
            {
                label: 'Présences',
                data: @json($presences->where('status', 'présent')->pluck('date')->countBy()->toArray()),
                backgroundColor: 'gold'
            },
            {
                label: 'Absences',
                data: @json($presences->where('status', 'absent')->pluck('date')->countBy()->toArray()),
                backgroundColor: 'danger'
            }
        ]
    }, {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        },
        barPercentage: 0.6,
        categoryPercentage: 0.8
    });
});
</script>
@endpush
