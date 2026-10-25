@extends('layouts.dashboard')

@section('breadcrumb', 'Métriques système')

@section('header')
    <div>
        <span>Métriques Système</span>
        <p class="console-subtitle">Monitoring en temps réel de l'application Le Pharaon.</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn-secondary">
        <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Retour
    </a>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="space-y-5">

    {{-- KPI Cards --}}
    <div class="mb-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        @include('partials.dashboard.kpi', ['label' => 'Erreurs aujourd\'hui', 'value' => $erreurs, 'meta' => $warnings . ' avertissement(s)', 'tone' => 'danger'])
        @include('partials.dashboard.kpi', ['label' => 'Requêtes aujourd\'hui', 'value' => number_format($totalRequetes, 0, ',', ' '), 'meta' => 'Depuis minuit', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'Rate-limits déclenchés', 'value' => $rateLimitCount, 'meta' => 'Tentatives de login bloquées', 'tone' => 'warning'])
        @include('partials.dashboard.kpi', ['label' => 'Utilisateurs actifs', 'value' => $usersToday, 'meta' => 'Connectés aujourd\'hui', 'tone' => 'success'])
    </div>

    {{-- Graphiques --}}
    <div class="mb-8 grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Répartition par module --}}
        <div class="console-card">
            <div class="console-card-head">
                <div>
                    <h3 class="console-card-title">Activité par module</h3>
                    <p class="console-card-sub">Requêtes enregistrées aujourd'hui</p>
                </div>
            </div>
            <div class="console-card-body">
                @if($requeteParModule->isEmpty())
                    <div class="console-empty">
                        <p class="text-sm text-gray-400">Aucune activité enregistrée aujourd'hui</p>
                    </div>
                @else
                    <div class="h-64"><canvas id="moduleChart" role="img" aria-label="Répartition des requêtes par module"></canvas></div>
                @endif
            </div>
        </div>

        {{-- Erreurs récentes --}}
        <div class="console-card">
            <div class="console-card-head">
                <div class="flex items-center gap-2">
                    <span class="console-dot bg-red-500"></span>
                    <h3 class="console-card-title">Erreurs récentes</h3>
                </div>
            </div>
            <div class="console-card-body">
                @if(empty($recentErrors))
                    <div class="console-empty">
                        <p class="text-sm font-medium text-green-700">Aucune erreur aujourd'hui</p>
                        <p class="text-xs text-gray-400">Le système fonctionne normalement</p>
                    </div>
                @else
                    <div class="console-scroll max-h-64 space-y-2">
                        @foreach($recentErrors as $error)
                            <div class="break-all rounded-lg border border-red-100 bg-red-50 px-3 py-2 font-mono text-xs text-red-700">
                                {{ Str::limit($error, 120) }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Info système --}}
    <div class="console-card">
        <div class="console-card-head">
            <h3 class="console-card-title">Informations système</h3>
        </div>
        <div class="console-card-body">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="console-kpi-label">PHP</div>
                    <div class="mt-1 text-lg font-bold tabular-nums text-[#080808]">{{ phpversion() }}</div>
                </div>
                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="console-kpi-label">Laravel</div>
                    <div class="mt-1 text-lg font-bold tabular-nums text-[#080808]">{{ app()->version() }}</div>
                </div>
                <div class="rounded-xl bg-gray-50 p-4">
                    <div class="console-kpi-label">Uptime</div>
                    <div class="mt-1 text-lg font-bold tabular-nums text-[#080808]">{{ $uptime ?? 'N/A (Windows)' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/pharaoh-charts.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const moduleLabels = @json($requeteParModule->pluck('module')->map(fn($m) => ucfirst($m))->toArray());
    const moduleCounts = @json($requeteParModule->pluck('total')->toArray());

    PharaohCharts.doughnut(document.getElementById('moduleChart'), {
        labels: moduleLabels,
        datasets: [{
            data: moduleCounts,
            backgroundColor: ['gold', 'success', 'info', 'warning', 'danger', 'muted']
        }]
    }, {
        plugins: { legend: { position: 'bottom' } }
    });
});
</script>
@endpush
@endsection
