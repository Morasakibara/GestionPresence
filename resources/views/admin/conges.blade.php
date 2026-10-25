@extends('layouts.dashboard')

@section('title', 'Gestion des Congés')
@section('breadcrumb', 'Gestion des congés')

@section('header')
    <div>
        <span>Gestion des Congés — Entreprise</span>
        <p class="console-subtitle">Suivi, validation et export des demandes de congé et d'absence.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.conges.planning.pdf', ['mois' => now()->format('Y-m')]) }}" target="_blank" rel="noopener"
           class="btn-dark btn-sm gap-1.5">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Planning PDF</span>
        </a>
        <a href="{{ route('admin.conges.resume.pdf') }}" target="_blank" rel="noopener"
           class="btn-gold btn-sm gap-1.5">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Résumé PDF</span>
        </a>
        <a href="{{ route('admin.conges.export.pivot', ['mois' => now()->format('Y-m')]) }}"
           class="btn-success btn-sm gap-1.5">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <span>Excel Pivot</span>
        </a>
        <a href="{{ route('admin.conges.export.liste') }}"
           class="btn-secondary btn-sm gap-1.5">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Excel Liste</span>
        </a>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
@php
    $statutFilter = request('statut');
    $typeFilter = request('type');
    $equipeFilter = request('equipe');
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        @include('partials.dashboard.kpi', ['label' => 'Total demandes', 'value' => $stats['total'], 'meta' => 'Toutes périodes', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'En attente', 'value' => $stats['en_attente'], 'meta' => 'À traiter', 'tone' => 'warning'])
        @include('partials.dashboard.kpi', ['label' => 'Approuvés', 'value' => $stats['approuves'], 'meta' => 'Validés', 'tone' => 'success'])
        @include('partials.dashboard.kpi', ['label' => 'Refusés', 'value' => $stats['refuses'], 'meta' => 'Non accordés', 'tone' => 'danger'])
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Évolution des demandes (6 mois)</h2>
                    <p class="console-card-sub">Nombre de demandes de congé et d'absence par mois.</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="h-56"><canvas id="congesChart" aria-label="Évolution des demandes de congé sur six mois" role="img"></canvas></div>
            </div>
        </div>

        <div class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Calendrier des absences</h2>
                    <p class="console-card-sub">Vue calendaire des congés approuvés.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5"><span class="console-dot bg-[#D39B23]"></span> Congé</span>
                    <span class="flex items-center gap-1.5"><span class="console-dot bg-[#D97706]"></span> Abs. justifiée</span>
                    <span class="flex items-center gap-1.5"><span class="console-dot bg-[#D64545]"></span> Abs. injustifiée</span>
                </div>
            </div>
            <div class="console-card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Demandes de congé</h2>
                <p class="console-card-sub">Filtrez puis traitez les demandes de l'entreprise.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 px-4 pt-4 sm:px-5">
            <a href="{{ route('admin.conges') }}" class="chip {{ !$statutFilter && !$typeFilter && !$equipeFilter ? 'is-active' : '' }}">
                Tous
            </a>
            <a href="{{ route('admin.conges', ['statut' => 'en_attente']) }}" class="chip {{ $statutFilter === 'en_attente' ? 'is-active' : '' }}">
                En attente
            </a>
            <a href="{{ route('admin.conges', ['statut' => 'approuve']) }}" class="chip {{ $statutFilter === 'approuve' ? 'is-active' : '' }}">
                Approuvés
            </a>
            <a href="{{ route('admin.conges', ['statut' => 'refuse']) }}" class="chip {{ $statutFilter === 'refuse' ? 'is-active' : '' }}">
                Refusés
            </a>

            <select onchange="window.location.href=this.value" aria-label="Filtrer par équipe"
                    class="select-field-sm rounded-full py-1.5 pl-3 pr-8">
                <option value="{{ route('admin.conges') }}" {{ !$equipeFilter ? 'selected' : '' }}>Toutes les équipes</option>
                @foreach($equipes as $eq)
                    <option value="{{ route('admin.conges', array_merge(request()->query(), ['equipe' => $eq])) }}" {{ $equipeFilter === $eq ? 'selected' : '' }}>{{ $eq }}</option>
                @endforeach
            </select>
        </div>

        <div class="console-card-body">
            <div class="table-scroll overflow-x-auto">
                <table class="w-full min-w-[48rem] text-sm">
                    <thead class="table-head">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Employé</th>
                            <th class="px-4 py-3 text-left font-semibold">Type</th>
                            <th class="px-4 py-3 text-left font-semibold">Période</th>
                            <th class="px-4 py-3 text-center font-semibold">Jours</th>
                            <th class="px-4 py-3 text-left font-semibold">Motif</th>
                            <th class="px-4 py-3 text-left font-semibold">Statut</th>
                            <th class="px-4 py-3 text-left font-semibold">Traité par</th>
                        </tr>
                    </thead>
                    <tbody class="table-body divide-y divide-gray-100">
                        @forelse($conges as $conge)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $conge->employe->nom ?? 'N/A' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $conge->typeLabel() }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                {{ $conge->date_debut->format('d/m') }} → {{ $conge->date_fin->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-pharaoh-gold/10 text-sm font-bold text-pharaoh-gold">{{ $conge->dureeJours() }}</span>
                            </td>
                            <td class="max-w-xs truncate px-4 py-3" title="{{ $conge->motif }}">{{ $conge->motif }}</td>
                            <td class="px-4 py-3">{!! $conge->statutBadge() !!}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-500">{{ $conge->traitePar->nom ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="mb-2 h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm">Aucune demande de congé.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($conges->hasPages())
            <div class="mt-4 border-t border-gray-200 pt-4">
                {{ $conges->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="{{ asset('js/pharaoh-charts.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            PharaohCharts.bar(document.getElementById('congesChart'), {
                labels: @json($chartLabels),
                datasets: [{ label: 'Demandes', data: @json($chartData), backgroundColor: 'gold' }]
            }, {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } } }
            });
        });

        // FullCalendar via CDN
        document.addEventListener('DOMContentLoaded', function () {
            const calEl = document.getElementById('calendar');
            if (calEl && typeof FullCalendar !== 'undefined') {
                const cal = new FullCalendar.Calendar(calEl, {
                    plugins: ['dayGrid', 'interaction'],
                    initialView: 'dayGridMonth',
                    locale: 'fr',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth'
                    },
                    events: '{{ route('admin.conges.calendar.events') }}',
                    height: 'auto',
                    eventDisplay: 'block',
                    eventDidMount: function (info) {
                        info.el.title = info.event.extendedProps.employe + '\n' + info.event.extendedProps.motif;
                    },
                    noEventsContent: '<div class="text-center py-8 text-gray-400">Aucune absence ce mois</div>',
                });
                cal.render();
            }
        });
    </script>
@endpush
