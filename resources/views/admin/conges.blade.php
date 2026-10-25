@extends('layouts.app')

@section('header', 'Gestion des Congés — Entreprise')

@section('content')
<!-- Export PDF -->
<div class="flex flex-wrap items-center justify-end gap-2 mb-6">
    <a href="{{ route('admin.conges.planning.pdf', ['mois' => now()->format('Y-m')]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl border-2 border-[#080808] bg-[#080808] px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition-colors">
        <svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span>Planning PDF</span>
    </a>
    <a href="{{ route('admin.conges.resume.pdf') }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-pharaoh-gold px-4 py-2 text-sm font-semibold text-white hover:bg-pharaoh-gold-light transition-colors">
        <svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span>Résumé PDF</span>
    </a>
    <a href="{{ route('admin.conges.export.pivot', ['mois' => now()->format('Y-m')]) }}" class="inline-flex items-center gap-2 rounded-xl border-2 border-emerald-500 bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 transition-colors">
        <svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <span>Excel Pivot</span>
    </a>
    <a href="{{ route('admin.conges.export.liste') }}" class="inline-flex items-center gap-2 rounded-xl border-2 border-blue-500 bg-blue-500 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600 transition-colors">
        <svg class="h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <span>Excel Liste</span>
    </a>
</div>

@php
    $statutFilter = request('statut');
    $typeFilter = request('type');
    $equipeFilter = request('equipe');
@endphp

<!-- Stats -->
<div class="grid grid-cols-2 gap-4 mb-6 sm:grid-cols-4">
    <div class="stat-card border border-white/20 bg-gradient-to-br from-white/90 to-white/70">
        <div class="stat-card-icon bg-pharaoh-gold/10 text-pharaoh-gold">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <div class="stat-card-value">{{ $stats['total'] }}</div>
        <div class="stat-card-label">Total demandes</div>
    </div>
    <div class="stat-card border border-amber-300/30 bg-gradient-to-br from-amber-50/90 to-amber-50/70">
        <div class="stat-card-icon bg-amber-500/10 text-amber-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="stat-card-value text-amber-600">{{ $stats['en_attente'] }}</div>
        <div class="stat-card-label">En attente</div>
    </div>
    <div class="stat-card border border-emerald-300/30 bg-gradient-to-br from-emerald-50/90 to-emerald-50/70">
        <div class="stat-card-icon bg-emerald-500/10 text-emerald-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="stat-card-value text-emerald-600">{{ $stats['approuves'] }}</div>
        <div class="stat-card-label">Approuvés</div>
    </div>
    <div class="stat-card border border-red-300/30 bg-gradient-to-br from-red-50/90 to-red-50/70">
        <div class="stat-card-icon bg-red-500/10 text-red-600">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
        <div class="stat-card-value text-red-600">{{ $stats['refuses'] }}</div>
        <div class="stat-card-label">Refusés</div>
    </div>
</div>

<!-- Graphique -->
<div class="card mb-6">
    <h2 class="text-lg font-bold text-[#080808] mb-1 flex items-center gap-2">
        <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
        </svg>
        <span>Évolution des demandes (6 mois)</span>
    </h2>
    <p class="text-sm text-gray-500 mb-4 ml-7">Nombre de demandes de congé/absence par mois</p>
    <canvas id="congesChart" height="100"></canvas>
</div>

<!-- Calendrier FullCalendar -->
<div class="card mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-bold text-[#080808] flex items-center gap-2">
                <svg class="h-5 w-5 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Calendrier des absences</span>
            </h2>
            <p class="text-sm text-gray-500 ml-7">Vue calendaire des congés approuvés</p>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-[#D39B23]"></span> Congé</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-[#D97706]"></span> Abs. justifiée</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-[#D64545]"></span> Abs. injustifiée</span>
        </div>
    </div>
    <div id="calendar"></div>
</div>

<!-- Filtres -->
<div class="flex flex-wrap items-center gap-3 mb-6">
    <a href="{{ route('admin.conges') }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ !$statutFilter && !$typeFilter && !$equipeFilter ? 'bg-pharaoh-gold text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        Tous
    </a>
    <a href="{{ route('admin.conges', ['statut' => 'en_attente']) }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ $statutFilter === 'en_attente' ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        En attente
    </a>
    <a href="{{ route('admin.conges', ['statut' => 'approuve']) }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ $statutFilter === 'approuve' ? 'bg-emerald-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        Approuvés
    </a>
    <a href="{{ route('admin.conges', ['statut' => 'refuse']) }}" class="rounded-full px-4 py-1.5 text-sm font-medium {{ $statutFilter === 'refuse' ? 'bg-red-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
        Refusés
    </a>

    <select onchange="window.location.href=this.value" class="rounded-full border border-gray-200 px-4 py-1.5 text-sm bg-white">
        <option value="{{ route('admin.conges') }}" {{ !$equipeFilter ? 'selected' : '' }}>Toutes les équipes</option>
        @foreach($equipes as $eq)
            <option value="{{ route('admin.conges', array_merge(request()->query(), ['equipe' => $eq])) }}" {{ $equipeFilter === $eq ? 'selected' : '' }}>{{ $eq }}</option>
        @endforeach
    </select>
</div>

<!-- Tableau -->
<div class="card">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#080808] text-white">
                    <th class="px-4 py-3 text-left font-semibold">Employé</th>
                    <th class="px-4 py-3 text-left font-semibold">Type</th>
                    <th class="px-4 py-3 text-left font-semibold">Période</th>
                    <th class="px-4 py-3 text-center font-semibold">Jours</th>
                    <th class="px-4 py-3 text-left font-semibold">Motif</th>
                    <th class="px-4 py-3 text-left font-semibold">Statut</th>
                    <th class="px-4 py-3 text-left font-semibold">Traité par</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($conges as $conge)
                <tr class="hover:bg-pharaoh-gold/5 transition-colors">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-900">{{ $conge->employe->nom ?? 'N/A' }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $conge->typeLabel() }}</td>
                    <td class="px-4 py-3 text-sm">
                        {{ $conge->date_debut->format('d/m') }} → {{ $conge->date_fin->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-pharaoh-gold/10 text-sm font-bold text-pharaoh-gold">{{ $conge->dureeJours() }}</span>
                    </td>
                    <td class="px-4 py-3 max-w-xs truncate" title="{{ $conge->motif }}">{{ $conge->motif }}</td>
                    <td class="px-4 py-3">{!! $conge->statutBadge() !!}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $conge->traitePar->nom ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="h-10 w-10 text-gray-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
        {{ $conges->links() }}
    </div>
    @endif
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('congesChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Demandes',
                    data: @json($chartData),
                    backgroundColor: '#D39B2340',
                    borderColor: '#D39B23',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: '#D39B23',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#080808', padding: 12, cornerRadius: 8 }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
});
});

// FullCalendar via CDN
document.addEventListener('DOMContentLoaded', function() {
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
            eventDidMount: function(info) {
                info.el.title = info.event.extendedProps.employe + '\n' + info.event.extendedProps.motif;
            },
            noEventsContent: '<div class="text-center py-8 text-gray-400">Aucune absence ce mois</div>',
        });
        cal.render();
    }
});
</script>
@endsection
