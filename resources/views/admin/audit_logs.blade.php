@extends('layouts.dashboard')

@section('title', 'Journal d\'audit — Le Pharaon')

@section('content')
<div class="min-h-screen bg-[#F8F8F8]">
    <main class="p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-[#080808] sm:text-3xl flex items-center gap-2">
                <svg class="h-8 w-8 text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>Journal d'audit</span>
            </h1>
            <p class="mt-2 text-sm text-gray-600 ml-10">Historique de toutes les opérations critiques du système</p>
        </div>

        {{-- Filtre par module --}}
        <div class="mb-6 flex flex-wrap items-center gap-3">
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
        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
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
                        <tr class="hover:bg-gray-50 transition-colors">
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
                                            class="text-[#D39B23] hover:underline text-sm font-medium">
                                        Voir ▾
                                    </button>
                                    <div id="detail-{{ $log->id }}" class="hidden mt-2 rounded-lg bg-gray-50 p-3 text-xs">
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
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-10 w-10 text-gray-400 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        <div class="mt-4">
            {{ $logs->withQueryString()->links() }}
        </div>

        {{-- Stats résumées --}}
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <div class="text-2xl font-bold text-[#080808]">{{ \App\Models\AuditLog::count() }}</div>
                <div class="text-xs text-gray-500">Total opérations</div>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <div class="text-2xl font-bold text-green-600">{{ \App\Models\AuditLog::where('action', 'like', '%.create%')->count() }}</div>
                <div class="text-xs text-gray-500">Créations</div>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <div class="text-2xl font-bold text-blue-600">{{ \App\Models\AuditLog::where('action', 'like', '%.update%')->count() }}</div>
                <div class="text-xs text-gray-500">Modifications</div>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <div class="text-2xl font-bold text-red-600">{{ \App\Models\AuditLog::where('action', 'like', '%.delete%')->count() }}</div>
                <div class="text-xs text-gray-500">Suppressions</div>
            </div>
        </div>
    </main>
</div>
@endsection
