@extends('layouts.dashboard')

@section('title', 'Journal d\'audit — Le Pharaon')
@section('breadcrumb', 'Journal d\'audit')

@section('header')
    <div>
        <span>Journal d'audit</span>
        <p class="console-subtitle">Historique de toutes les opérations critiques du système.</p>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="space-y-5">
    {{-- Filtre par module --}}
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.auditLogs') }}"
           class="rounded-full px-4 py-1.5 text-sm font-medium {{ !$module ? 'bg-[#D39B23] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Tous
        </a>
        @foreach($modules as $m)
            <a href="{{ route('admin.auditLogs', ['module' => $m]) }}"
               class="rounded-full px-4 py-1.5 text-sm font-medium {{ $module === $m ? 'bg-[#D39B23] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                {{ ucfirst(str_replace('_', ' ', $m)) }}
            </a>
        @endforeach
    </div>

    {{-- Tableau des logs --}}
    <div class="console-card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#080808] text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Heure</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Utilisateur</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Module</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Détails</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr class="transition-colors hover:bg-gray-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-[#080808]">{{ $log->user?->nom ?? 'Système' }}</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                                    {{ $log->role ?? '—' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold {{ $log->badgeColor() }}">
                                {{ $log->icon() }} {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ ucfirst(str_replace('_', ' ', $log->module)) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-[#080808]">
                            {{ $log->description }}
                        </td>
                        <td class="px-4 py-3">
                            @if($log->old_values || $log->new_values)
                                <button onclick="document.getElementById('detail-{{ $log->id }}').classList.toggle('hidden')"
                                        class="text-sm font-medium text-[#D39B23] hover:underline">
                                    Voir ▾
                                </button>
                                <div id="detail-{{ $log->id }}" class="mt-2 hidden rounded-lg bg-gray-50 p-3 text-xs">
                                    @if($log->old_values)
                                        <div class="mb-2">
                                            <span class="font-semibold text-red-600">Avant :</span>
                                            <pre class="mt-1 whitespace-pre-wrap text-gray-600">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    @endif
                                    @if($log->new_values)
                                        <div>
                                            <span class="font-semibold text-green-600">Après :</span>
                                            <pre class="mt-1 whitespace-pre-wrap text-gray-600">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="mb-2 h-10 w-10 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2 2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0L12 17l-8-4" />
                                </svg>
                                <span class="text-sm">Aucun journal d'audit pour le moment.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $logs->withQueryString()->links() }}
    </div>

    {{-- Stats résumées --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="console-card p-4">
            <div class="text-2xl font-bold text-[#080808]">{{ \App\Models\AuditLog::count() }}</div>
            <div class="text-xs text-gray-500">Total opérations</div>
        </div>
        <div class="console-card p-4">
            <div class="text-2xl font-bold text-green-600">{{ \App\Models\AuditLog::where('action', 'like', '%.create%')->count() }}</div>
            <div class="text-xs text-gray-500">Créations</div>
        </div>
        <div class="console-card p-4">
            <div class="text-2xl font-bold text-blue-600">{{ \App\Models\AuditLog::where('action', 'like', '%.update%')->count() }}</div>
            <div class="text-xs text-gray-500">Modifications</div>
        </div>
        <div class="console-card p-4">
            <div class="text-2xl font-bold text-red-600">{{ \App\Models\AuditLog::where('action', 'like', '%.delete%')->count() }}</div>
            <div class="text-xs text-gray-500">Suppressions</div>
        </div>
    </div>
</div>
@endsection
