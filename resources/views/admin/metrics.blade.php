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
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        {{-- Erreurs --}}
        <div class="stat-card border-danger/20 bg-gradient-to-br from-red-50 to-white">
            <div class="stat-card-icon bg-danger/10 text-danger">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="stat-card-label">Erreurs aujourd'hui</div>
            <div class="stat-card-value text-danger">{{ $erreurs }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $warnings }} avertissements</div>
        </div>

        {{-- Requêtes --}}
        <div class="stat-card border-pharaoh-gold/20 bg-gradient-to-br from-amber-50 to-white">
            <div class="stat-card-icon bg-pharaoh-gold/10 text-pharaoh-gold">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="stat-card-label">Requêtes aujourd'hui</div>
            <div class="stat-card-value">{{ number_format($totalRequetes) }}</div>
        </div>

        {{-- Rate Limits --}}
        <div class="stat-card border-warning/20 bg-gradient-to-br from-orange-50 to-white">
            <div class="stat-card-icon bg-warning/10 text-warning">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div class="stat-card-label">Rate-limits déclenchés</div>
            <div class="stat-card-value text-warning">{{ $rateLimitCount }}</div>
            <div class="text-xs text-gray-400 mt-1">Tentatives de login bloquées</div>
        </div>

        {{-- Utilisateurs --}}
        <div class="stat-card border-success/20 bg-gradient-to-br from-emerald-50 to-white">
            <div class="stat-card-icon bg-success/10 text-success">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="stat-card-label">Utilisateurs actifs</div>
            <div class="stat-card-value text-success">{{ $usersToday }}</div>
            <div class="text-xs text-gray-400 mt-1">Connectés aujourd'hui</div>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">
        {{-- Répartition par module --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="section-title mb-4">Activité par module</h3>
            <canvas id="moduleChart" height="200"></canvas>
            @if($requeteParModule->isEmpty())
                <p class="text-center text-gray-400 text-sm mt-4">Aucune activité enregistrée aujourd'hui</p>
            @endif
        </div>

        {{-- Erreurs récentes --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="section-title mb-4">
                <span class="inline-flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-danger animate-pulse"></span>
                    Erreurs récentes
                </span>
            </h3>
            @if(empty($recentErrors))
                <div class="flex items-center justify-center h-48 text-success">
                    <div class="text-center">
                        <svg class="h-12 w-12 mx-auto mb-2 text-success/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm font-medium">Aucune erreur aujourd'hui</p>
                        <p class="text-xs text-gray-400">Le système fonctionne normalement</p>
                    </div>
                </div>
            @else
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($recentErrors as $error)
                        <div class="rounded-lg bg-red-50 border border-red-100 px-3 py-2 text-xs font-mono text-red-700 break-all">
                            {{ Str::limit($error, 120) }}
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Info système --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="section-title mb-4">Informations Système</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">PHP</div>
                <div class="mt-1 text-lg font-bold text-pharaoh-black">{{ phpversion() }}</div>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Laravel</div>
                <div class="mt-1 text-lg font-bold text-pharaoh-black">{{ app()->version() }}</div>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Uptime</div>
                <div class="mt-1 text-lg font-bold text-pharaoh-black">{{ $uptime ?? 'N/A (Windows)' }}</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const moduleLabels = @json($requeteParModule->pluck('module')->map(fn($m) => ucfirst($m))->toArray());
    const moduleCounts = @json($requeteParModule->pluck('total')->toArray());

    if (moduleLabels.length > 0) {
        new Chart(document.getElementById('moduleChart'), {
            type: 'doughnut',
            data: {
                labels: moduleLabels,
                datasets: [{
                    data: moduleCounts,
                    backgroundColor: ['#D39B23', '#2E8B57', '#3B82C4', '#D97706', '#D64545', '#888888'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
