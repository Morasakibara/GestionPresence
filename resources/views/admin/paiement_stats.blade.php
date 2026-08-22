@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Statistiques des paiements</span>
</div>
@endsection

@section('navigation')
<a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-[#D39B23] hover:text-white">Tableau de bord</a>
<a href="{{ route('admin.paiements') }}" class="rounded-md bg-[#D39B23] px-3 py-2 text-sm font-medium text-white" aria-current="page">Paiements</a>
<a href="{{ route('admin.addEmployee') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-[#D39B23] hover:text-white">Ajouter employé</a>
<a href="{{ route('admin.generateReport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-[#D39B23] hover:text-white">Générer Bilan</a>
<a href="{{ route('admin.showEmployeeList') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-[#D39B23] hover:text-white">Liste employés</a>
@endsection

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">

    {{-- ══════════ CARTES RÉSUMÉ ══════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        {{-- Payé --}}
        <div class="bg-white rounded-xl shadow border border-[#E7E7E7] p-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="inline-block w-3 h-3 rounded-full bg-[#2E8B57]"></span>
                <span class="text-sm font-medium text-[#555555]">Payé</span>
            </div>
            <p class="text-3xl font-bold text-[#080808]">{{ number_format($stats['paye']['montant'], 0, ',', '.') }} <span class="text-base font-normal">FCFA</span></p>
            <p class="text-sm text-[#888888] mt-1">{{ $stats['paye']['count'] }} commande(s)</p>
        </div>
        {{-- Partiel --}}
        <div class="bg-white rounded-xl shadow border border-[#E7E7E7] p-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="inline-block w-3 h-3 rounded-full bg-[#D97706]"></span>
                <span class="text-sm font-medium text-[#555555]">Partiel</span>
            </div>
            <p class="text-3xl font-bold text-[#080808]">{{ number_format($stats['partiel']['montant'], 0, ',', '.') }} <span class="text-base font-normal">FCFA</span></p>
            <p class="text-sm text-[#888888] mt-1">{{ $stats['partiel']['count'] }} commande(s) · reste {{ number_format($stats['partiel']['montant'] - $stats['partiel']['paye'], 0, ',', '.') }} FCFA</p>
        </div>
        {{-- À payer --}}
        <div class="bg-white rounded-xl shadow border border-[#E7E7E7] p-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="inline-block w-3 h-3 rounded-full bg-[#D64545]"></span>
                <span class="text-sm font-medium text-[#555555]">À payer</span>
            </div>
            <p class="text-3xl font-bold text-[#080808]">{{ number_format($stats['a_payer']['montant'], 0, ',', '.') }} <span class="text-base font-normal">FCFA</span></p>
            <p class="text-sm text-[#888888] mt-1">{{ $stats['a_payer']['count'] }} commande(s) en attente</p>
        </div>
    </div>

    {{-- ══════════ CA PAR POSTE ══════════ --}}
    @if($caParPoste)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
        @foreach($caParPoste as $poste => $data)
        <div class="bg-white rounded-xl shadow border border-[#E7E7E7] p-6">
            <h3 class="text-lg font-semibold text-[#080808] mb-3">{{ $poste }}</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-[#555555]">Total commandé</span><span class="font-semibold text-[#080808]">{{ number_format($data['total'], 0, ',', '.') }} FCFA</span></div>
                <div class="flex justify-between"><span class="text-[#555555]">Encaissé</span><span class="font-semibold text-[#2E8B57]">{{ number_format($data['paye'], 0, ',', '.') }} FCFA</span></div>
                <div class="flex justify-between"><span class="text-[#555555]">Restant</span><span class="font-semibold text-[#D64545]">{{ number_format($data['restant'], 0, ',', '.') }} FCFA</span></div>
            </div>
            {{-- Barre de progression --}}
            @php($pct = $data['total'] > 0 ? round($data['paye'] / $data['total'] * 100) : 0)
            <div class="mt-3 w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-[#2E8B57] h-2.5 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-xs text-[#888888] mt-1">{{ $pct }}% encaissé</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ══════════ GRAPHIQUES ══════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Doughnut: répartition par statut --}}
        <div class="bg-white rounded-xl shadow border border-[#E7E7E7] p-6">
            <h3 class="text-lg font-semibold text-[#080808] mb-4">Répartition des paiements</h3>
            <div class="relative" style="max-width: 320px; margin: 0 auto;">
                <canvas id="pieStatutChart"></canvas>
            </div>
        </div>
        {{-- Bar: évolution 30 jours --}}
        <div class="bg-white rounded-xl shadow border border-[#E7E7E7] p-6">
            <h3 class="text-lg font-semibold text-[#080808] mb-4">Évolution sur 30 jours</h3>
            <canvas id="evolutionStatutChart" height="220"></canvas>
        </div>
    </div>

    {{-- ══════════ TABLEAU TOP 10 ══════════ --}}
    <div class="bg-white rounded-xl shadow border border-[#E7E7E7] overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-[#E7E7E7]">
            <h3 class="text-lg font-semibold text-[#080808]">Top 10 commandes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#E7E7E7]">
                <thead class="bg-[#F8F8F8]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[#555555] uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-[#555555] uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-[#555555] uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-[#555555] uppercase tracking-wider">Payé</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-[#555555] uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E7E7]">
                    @forelse($topCommandes as $cmd)
                    <tr class="hover:bg-[#F8F8F8] transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-[#080808]">{{ ucfirst(str_replace('_', ' ', $cmd->type)) }}</td>
                        <td class="px-6 py-4 text-sm text-[#555555]">{{ $cmd->date ? $cmd->date->format('d/m/Y') : '—' }}</td>
                        <td class="px-6 py-4 text-sm text-right font-semibold text-[#080808]">{{ number_format($cmd->montant, 0, ',', '.') }} FCFA</td>
                        <td class="px-6 py-4 text-sm text-right text-[#2E8B57]">{{ number_format($cmd->montant_paye, 0, ',', '.') }} FCFA</td>
                        <td class="px-6 py-4 text-center">
                            @if($cmd->statut_paiement === 'paye')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Payé</span>
                            @elseif($cmd->statut_paiement === 'partiel')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Partiel</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">À payer</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-[#888888]">Aucune commande enregistrée</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ══════════ CHART.JS ══════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Doughnut — répartition
    new Chart(document.getElementById('pieStatutChart'), {
        type: 'doughnut',
        data: {
            labels: ['Payé', 'Partiel', 'À payer'],
            datasets: [{
                data: [{{ $stats['paye']['count'] }}, {{ $stats['partiel']['count'] }}, {{ $stats['a_payer']['count'] }}],
                backgroundColor: ['#2E8B57', '#D97706', '#D64545'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { padding: 16, font: { size: 13 } } } },
            cutout: '65%',
        }
    });

    // Bar — évolution 30 jours
    new Chart(document.getElementById('evolutionStatutChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [
                { label: 'Payé', data: {!! json_encode($payes) !!}, backgroundColor: '#2E8B57', borderRadius: 4 },
                { label: 'Partiel', data: {!! json_encode($partiels) !!}, backgroundColor: '#D97706', borderRadius: 4 },
                { label: 'À payer', data: {!! json_encode($aPayer) !!}, backgroundColor: '#D64545', borderRadius: 4 },
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } } },
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } },
            }
        }
    });
});
</script>
@endsection
