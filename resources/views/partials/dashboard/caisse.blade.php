{{--
    Dashboard de caisse partagé par la Directrice (imprimerie) et la Secrétaire (photo).
    Variables attendues : $module ('directrice'|'secretaire'), $types (libellés des types de caisse),
    plus toutes les données de CaisseService::dashboardData().
--}}
@php
    $module = $module ?? 'directrice';
    $types = $types ?? [];
    $impayees = $impayees ?? ($commandesImpayees ?? collect());
    $retraits = $derniersRetraits ?? collect();
    $commandesParType = $commandesParType ?? collect();
    $servicesParType = $servicesParType ?? collect();

    $activiteTotale = ($totalCommandes ?? 0) + ($totalServices ?? 0) + ($totalRetraits ?? 0);

    // Courbe cumulée des entrées sur 7 jours (comme le graphe « Affaires cumulées »).
    $cumulEntrees = [];
    $running = 0;
    foreach (($dataEntrees7j ?? []) as $valeur) {
        $running += (float) $valeur;
        $cumulEntrees[] = $running;
    }
@endphp

<div class="space-y-5">
    <!-- ═══ Indicateurs clés ═══ -->
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-6">
        @include('partials.dashboard.kpi', ['label' => 'Solde en caisse', 'value' => number_format($sommeEnCaisse ?? 0, 0, ',', ' '), 'meta' => 'FCFA disponibles', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'Impayés', 'value' => number_format($impayeTotal ?? 0, 0, ',', ' '), 'meta' => 'FCFA non encaissés', 'tone' => 'danger'])
        @include('partials.dashboard.kpi', ['label' => 'Reste à encaisser', 'value' => number_format($resteAEncaisser ?? 0, 0, ',', ' '), 'meta' => $impayees->count() . ' commande(s) — voir le détail', 'tone' => 'warning', 'onclick' => 'openImpayes()'])
        @include('partials.dashboard.kpi', ['label' => 'Commandes', 'value' => number_format($totalCommandes ?? 0, 0, ',', ' '), 'meta' => 'FCFA cumulés', 'tone' => 'success'])
        @include('partials.dashboard.kpi', ['label' => 'Services', 'value' => number_format($totalServices ?? 0, 0, ',', ' '), 'meta' => 'FCFA cumulés', 'tone' => 'info'])
        @include('partials.dashboard.kpi', ['label' => 'Retraits', 'value' => number_format($totalRetraits ?? 0, 0, ',', ' '), 'meta' => 'FCFA sortis', 'tone' => 'danger'])
    </div>

    <!-- ═══ Entrées / sorties + répartition ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="caisse-flux" class="console-card lg:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Entrées / sorties — 7 derniers jours</h2>
                    <p class="console-card-sub">Flux quotidien de la caisse</p>
                </div>
                <span class="badge badge-gold">FCFA</span>
            </div>
            <div class="console-card-body">
                <div class="h-56"><canvas id="chart7j"></canvas></div>
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Répartition de l'activité</h2>
                    <p class="console-card-sub">Commandes, services et retraits</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="relative h-40">
                    <canvas id="caisseActiviteChart"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-base font-extrabold text-[#080808]">{{ number_format($activiteTotale, 0, ',', ' ') }}</span>
                        <span class="text-[0.625rem] uppercase tracking-wide text-gray-400">FCFA</span>
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <div class="console-panel-row">
                        <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-green-600"></span>Commandes</span>
                        <span class="text-sm font-semibold text-[#080808]">{{ number_format($totalCommandes ?? 0, 0, ',', ' ') }}</span>
                    </div>
                    <div class="console-panel-row">
                        <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-blue-500"></span>Services</span>
                        <span class="text-sm font-semibold text-[#080808]">{{ number_format($totalServices ?? 0, 0, ',', ' ') }}</span>
                    </div>
                    <div class="console-panel-row">
                        <span class="flex items-center gap-2 text-sm text-gray-600"><span class="console-dot bg-red-500"></span>Retraits</span>
                        <span class="text-sm font-semibold text-[#080808]">{{ number_format($totalRetraits ?? 0, 0, ',', ' ') }}</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- ═══ Cumul + derniers retraits ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="caisse-cumul" class="console-card lg:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Encaissements cumulés</h2>
                    <p class="console-card-sub">Progression des entrées sur 7 jours</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="h-48"><canvas id="caisseCumulChart"></canvas></div>
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Derniers retraits</h2>
                    <p class="console-card-sub">Motifs et montants</p>
                </div>
                <a href="{{ route($module . '.retraits') }}" class="text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">Voir tout →</a>
            </div>
            <div class="console-card-body">
                <div class="console-scroll">
                    @forelse($retraits->take(6) as $rt)
                        <div class="console-panel-row">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="console-dot bg-red-500"></span>
                                <span class="truncate text-sm text-gray-700" title="{{ $rt->motif }}">{{ $rt->motif }}</span>
                            </span>
                            <span class="whitespace-nowrap text-xs font-semibold text-red-600">-{{ number_format($rt->montant, 0, ',', ' ') }}</span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-gray-400">Aucun retrait enregistré</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <!-- ═══ Détail par type ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="caisse-types" class="console-card lg:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Commandes par type</h2>
                    <p class="console-card-sub">Montants cumulés par prestation</p>
                </div>
            </div>
            <div class="console-card-body">
                @if($commandesParType->isNotEmpty())
                    <div class="h-52"><canvas id="commandesTypeChart"></canvas></div>
                @else
                    <div class="console-empty">
                        <p class="text-sm text-gray-400">Aucune commande enregistrée.</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Services par type</h2>
                    <p class="console-card-sub">Prestations réalisées aujourd'hui</p>
                </div>
            </div>
            <div class="console-card-body">
                @forelse($servicesParType as $type => $montant)
                    <div class="console-panel-row">
                        <span class="truncate text-sm text-gray-600">{{ $types[$type] ?? ucfirst($type) }}</span>
                        <span class="whitespace-nowrap text-sm font-semibold text-[#080808]">{{ number_format($montant, 0, ',', ' ') }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Aucun service aujourd'hui</p>
                @endforelse
            </div>
        </section>
    </div>

    <!-- ═══ Catégories + accès rapides ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Commandes par catégorie</h2>
                    <p class="console-card-sub">Cumul et nombre de ventes</p>
                </div>
            </div>
            <div class="console-card-body">
                @forelse($commandesParCategorie as $type => $data)
                    <div class="console-panel-row">
                        <span class="truncate text-sm text-gray-600">{{ $types[$type] ?? ucfirst($type) }}</span>
                        <span class="whitespace-nowrap text-sm font-semibold text-[#080808]">
                            {{ number_format($data['total'], 0, ',', ' ') }}
                            <span class="ml-1 text-xs font-normal text-gray-400">({{ $data['count'] }})</span>
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Aucune commande enregistrée</p>
                @endforelse
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Services par catégorie</h2>
                    <p class="console-card-sub">Cumul et nombre de prestations</p>
                </div>
            </div>
            <div class="console-card-body">
                @forelse($servicesParCategorie as $type => $data)
                    <div class="console-panel-row">
                        <span class="truncate text-sm text-gray-600">{{ $types[$type] ?? ucfirst($type) }}</span>
                        <span class="whitespace-nowrap text-sm font-semibold text-[#080808]">
                            {{ number_format($data['total'], 0, ',', ' ') }}
                            <span class="ml-1 text-xs font-normal text-gray-400">({{ $data['count'] }})</span>
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-400">Aucun service enregistré</p>
                @endforelse
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Accès rapides</h2>
                    <p class="console-card-sub">Gestes du quotidien</p>
                </div>
            </div>
            <div class="console-card-body space-y-2">
                <a href="{{ route($module . '.commandes') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 8h14l-1 12H6z" /><path d="M9 8V6.5a3 3 0 0 1 6 0V8" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Nouvelle commande</span>
                        <span class="block text-xs text-gray-500">Enregistrer une vente</span>
                    </span>
                </a>
                <a href="{{ route($module . '.services') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5.5" y="9" width="13" height="7" rx="1.5" /><path d="M8 9V4.5h8V9M8 16v3.5h8V16" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Nouveau service</span>
                        <span class="block text-xs text-gray-500">Impressions et prestations</span>
                    </span>
                </a>
                <a href="{{ route($module . '.retraits') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 4v9M8.5 9.5L12 13l3.5-3.5" /><path d="M4.5 16.5v1.75A1.75 1.75 0 0 0 6.25 20h11.5a1.75 1.75 0 0 0 1.75-1.75V16.5" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Retrait de caisse</span>
                        <span class="block text-xs text-gray-500">Enregistrer une sortie</span>
                    </span>
                </a>
                <a href="{{ route($module . '.rapport') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3.75A8.25 8.25 0 1 0 20.25 12H12z" /><path d="M14.75 3.9A8.25 8.25 0 0 1 20.1 9.5h-5.35z" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Rapport</span>
                        <span class="block text-xs text-gray-500">Exporter l'état de caisse</span>
                    </span>
                </a>
            </div>
        </section>
    </div>

    <!-- ═══ Commandes en attente d'encaissement ═══ -->
    @if($impayees->isNotEmpty())
        <section id="caisse-attente" class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Commandes en attente d'encaissement</h2>
                    <p class="console-card-sub">{{ $impayees->count() }} commande(s) · reste {{ number_format($resteAEncaisser ?? 0, 0, ',', ' ') }} FCFA</p>
                </div>
                <button type="button" onclick="openImpayes()" class="btn-gold text-xs">Encaisser</button>
            </div>
            <div class="console-card-body">
                <div class="table-scroll overflow-hidden rounded-xl border border-gray-200/70">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="table-head">
                            <tr>
                                <th>Type</th>
                                <th>Date</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Payé</th>
                                <th class="text-right">Reste</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @foreach($impayees as $cmd)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $types[$cmd->type] ?? ucfirst($cmd->type) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $cmd->date }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-700">{{ number_format($cmd->montant, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-green-700">{{ number_format($cmd->montant_paye, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-orange-600">{{ number_format($cmd->montant - $cmd->montant_paye, 0, ',', ' ') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $cmd->statut_paiement === 'a_payer' ? 'badge-danger' : 'badge-warning' }}">{{ $cmd->statut_paiement === 'a_payer' ? 'À payer' : 'Partiel' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif
</div>

<!-- ═══ MODALE COMMANDES IMPAYÉES ═══ -->
@if($impayees->isNotEmpty())
<div id="modalImpayes" class="fixed inset-0 z-[9990] hidden items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
    <div class="max-h-[85vh] w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-100 bg-[#FBF3E6] px-5 py-4">
            <h3 class="text-base font-bold text-[#080808]">Commandes en attente — {{ $impayees->count() }}</h3>
            <button type="button" onclick="closeImpayes()" class="rounded-lg p-1 text-gray-400 transition-colors hover:bg-white hover:text-gray-600" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="max-h-[60vh] overflow-y-auto px-5 py-4">
            @foreach($impayees as $cmd)
                <div class="mb-3 rounded-xl border border-orange-200 bg-white p-4 transition-shadow hover:shadow-sm">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="font-semibold text-[#080808]">{{ $types[$cmd->type] ?? ucfirst($cmd->type) }}</span>
                            <span class="ml-2 text-sm text-gray-500">#{{ $cmd->id }} — {{ $cmd->date }}</span>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $cmd->statut_paiement === 'a_payer' ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $cmd->statut_paiement === 'a_payer' ? 'À payer' : 'Partiel' }}
                        </span>
                    </div>
                    <div class="mb-3 flex flex-wrap items-center gap-4 text-sm">
                        <span>Total : <b class="text-[#080808]">{{ number_format($cmd->montant, 0, ',', ' ') }} FCFA</b></span>
                        <span>Payé : <b class="text-green-600">{{ number_format($cmd->montant_paye, 0, ',', ' ') }} FCFA</b></span>
                        <span class="font-bold text-orange-600">Reste : {{ number_format($cmd->montant - $cmd->montant_paye, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <form action="{{ route($module . '.encaisserReste') }}" method="POST" class="flex flex-wrap items-center gap-2" onsubmit="return confirm('Confirmer l\'encaissement ?')">
                        @csrf
                        <input type="hidden" name="commande_id" value="{{ $cmd->id }}">
                        <input type="number" name="montant_verse" min="0.01" step="any" max="{{ $cmd->montant - $cmd->montant_paye }}" value="{{ $cmd->montant - $cmd->montant_paye }}" required
                               class="input-field w-36 py-2 text-sm">
                        <span class="text-xs text-gray-400">FCFA</span>
                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-green-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Encaisser
                        </button>
                    </form>
                </div>
            @endforeach

            @if(isset($historiqueEncaissements) && $historiqueEncaissements->count() > 0)
                <div class="mt-4 border-t border-gray-100 pt-4">
                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Derniers encaissements</h4>
                    @foreach($historiqueEncaissements as $log)
                        <div class="console-panel-row">
                            <span class="flex items-center gap-2">
                                <span class="text-xs text-gray-400">{{ $log->created_at->format('d/m H:i') }}</span>
                                <span class="text-sm text-[#080808]">{{ $log->description }}</span>
                            </span>
                            <span class="text-xs text-gray-400">{{ $log->user?->nom }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50 px-5 py-3">
            <span class="text-sm font-semibold text-orange-700">Total reste : {{ number_format($resteAEncaisser ?? 0, 0, ',', ' ') }} FCFA</span>
            <button type="button" onclick="closeImpayes()" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium transition-colors hover:bg-gray-300">Fermer</button>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function openImpayes() {
        const modal = document.getElementById('modalImpayes');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeImpayes() {
        const modal = document.getElementById('modalImpayes');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.getElementById('modalImpayes')?.addEventListener('click', function (event) {
        if (event.target === this) closeImpayes();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !document.getElementById('modalImpayes')?.classList.contains('hidden')) {
            closeImpayes();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const fmt = v => Number(v).toLocaleString('fr-FR') + ' FCFA';

        // Entrées / sorties 7 jours
        const ctxFlux = document.getElementById('chart7j');
        if (ctxFlux) {
            new Chart(ctxFlux, {
                type: 'bar',
                data: {
                    labels: @json($labels7j ?? []),
                    datasets: [
                        { label: 'Entrées', data: @json($dataEntrees7j ?? []), backgroundColor: '#D39B23', borderRadius: 6, maxBarThickness: 20 },
                        { label: 'Sorties', data: @json($dataSorties7j ?? []), backgroundColor: '#111111', borderRadius: 6, maxBarThickness: 20 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, font: { size: 11 } } },
                        tooltip: { callbacks: { label: ctx => ctx.dataset.label + ' : ' + fmt(ctx.parsed.y) } }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { callback: v => Number(v).toLocaleString('fr-FR') } }
                    }
                }
            });
        }

        // Répartition de l'activité (donut)
        const ctxActivite = document.getElementById('caisseActiviteChart');
        if (ctxActivite) {
            new Chart(ctxActivite, {
                type: 'doughnut',
                data: {
                    labels: ['Commandes', 'Services', 'Retraits'],
                    datasets: [{
                        data: [{{ (float) ($totalCommandes ?? 0) }}, {{ (float) ($totalServices ?? 0) }}, {{ (float) ($totalRetraits ?? 0) }}],
                        backgroundColor: ['#2E8B57', '#3B82C4', '#D64545'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false } } }
            });
        }

        // Encaissements cumulés (ligne)
        const ctxCumul = document.getElementById('caisseCumulChart');
        if (ctxCumul) {
            new Chart(ctxCumul, {
                type: 'line',
                data: {
                    labels: @json($labels7j ?? []),
                    datasets: [{
                        label: 'Cumul des entrées',
                        data: @json($cumulEntrees),
                        borderColor: '#D39B23',
                        backgroundColor: 'rgba(211, 155, 35, 0.12)',
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#D39B23',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => 'Cumul : ' + fmt(ctx.parsed.y) } }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { callback: v => Number(v).toLocaleString('fr-FR') } }
                    }
                }
            });
        }

        // Commandes par type (barres horizontales)
        const ctxTypes = document.getElementById('commandesTypeChart');
        if (ctxTypes) {
            const types = @json($types);
            const commandes = @json($commandesParType);
            const cles = Object.keys(commandes);

            new Chart(ctxTypes, {
                type: 'bar',
                data: {
                    labels: cles.map(cle => types[cle] || cle),
                    datasets: [{
                        label: 'Montant',
                        data: cles.map(cle => Number(commandes[cle])),
                        backgroundColor: '#111111',
                        borderRadius: 6,
                        maxBarThickness: 18
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => fmt(ctx.parsed.x) } }
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { callback: v => Number(v).toLocaleString('fr-FR') } },
                        y: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endpush
