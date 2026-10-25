@extends('layouts.dashboard')

@section('content_shell', 'flush')

@section('breadcrumb', 'Mon espace')

@section('header')
<div class="flex w-full flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="console-title">Tableau de bord</h1>
        <p class="console-subtitle">Pointage, présence et évaluations — {{ now()->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('user.presence.report') }}" class="btn-secondary inline-flex items-center gap-2 text-sm">
        <svg class="h-4 w-4 text-pharaoh-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 3.5h7l4 4V20a.5.5 0 0 1-.5.5h-11A.5.5 0 0 1 6 20V4a.5.5 0 0 1 .5-.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 12h6M9.5 15.5h4" />
        </svg>
        Bilan de présence
    </a>
</div>
@endsection

@section('tabs')
<a href="#user-pointage" class="console-tab is-active" aria-current="page">Pointage</a>
<a href="#user-evaluation" class="console-tab">Évaluation</a>
<a href="#user-historique" class="console-tab">Historique</a>
<a href="#user-notifications" class="console-tab">Notifications</a>
@endsection

@section('content')
@php
    $evalTone = match ($evaluation['couleur']) {
        'vert' => 'success',
        'rouge' => 'danger',
        default => 'warning',
    };
    $evalBox = match ($evaluation['couleur']) {
        'vert' => 'border-green-200 bg-green-50 text-green-700',
        'rouge' => 'border-red-200 bg-red-50 text-red-700',
        default => 'border-amber-200 bg-amber-50 text-amber-700',
    };
    $evalBadge = match ($evaluation['couleur']) {
        'vert' => 'badge-success',
        'rouge' => 'badge-danger',
        default => 'badge-warning',
    };
    $evalMessage = match ($evaluation['couleur']) {
        'vert' => 'Excellent — discipline et rendement au rendez-vous',
        'rouge' => 'Critique — attention discipline et rendement',
        default => 'Satisfaisant — à surveiller',
    };
    $arriveeLabel = $lastArrival ? \Illuminate\Support\Carbon::parse($lastArrival)->format('d/m H:i') : '—';
    $departLabel = $lastDeparture ? \Illuminate\Support\Carbon::parse($lastDeparture)->format('d/m H:i') : '—';

    $notesEvolUser = array_values(array_filter(array_column($historique, 'note'), fn ($n) => $n > 0));
    $moyenneEvolUser = count($notesEvolUser) > 0 ? round(array_sum($notesEvolUser) / count($notesEvolUser), 1) : 0;
    $seqUser = array_column($historique, 'note');
    $dernierUser = (float) (end($seqUser) ?: 0);
    $avantUser = (float) (count($seqUser) >= 2 ? $seqUser[count($seqUser) - 2] : 0);
    if ($dernierUser <= 0 || $avantUser <= 0) {
        $tendanceUser = 'stable';
    } elseif ($dernierUser > $avantUser) {
        $tendanceUser = 'hausse';
    } elseif ($dernierUser < $avantUser) {
        $tendanceUser = 'baisse';
    } else {
        $tendanceUser = 'stable';
    }
    $deltaUser = round($dernierUser - $avantUser, 1);

    $tendanceKpi = match ($tendanceUser) {
        'hausse' => '▲ ' . ($deltaUser > 0 ? '+' . number_format($deltaUser, 1, ',', '') : '0'),
        'baisse' => '▼ ' . number_format(abs($deltaUser), 1, ',', ''),
        default => '—',
    };
    $tendanceTone = match ($tendanceUser) {
        'hausse' => 'success',
        'baisse' => 'danger',
        default => 'default',
    };

    $chartLabels = array_map(fn ($h) => ucfirst($h['label']), $historique);
    $chartNotes = array_map(fn ($h) => (float) $h['note'], $historique);
    $chartCouleurs = array_map(fn ($h) => $h['couleur'] === 'vert' ? '#2E8B57' : ($h['couleur'] === 'rouge' ? '#D64545' : '#D97706'), $historique);
    $dernieresNotifications = Auth::user()->notifications->take(4);
@endphp

<div class="space-y-5">
    <!-- ═══ Indicateurs clés ═══ -->
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-6">
        @include('partials.dashboard.kpi', ['label' => 'Présences ce mois', 'value' => $presenceCount ?? 0, 'meta' => 'Jours pointés', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'Note du mois', 'value' => $evaluation['note'] . '/20', 'meta' => 'Évaluation', 'tone' => $evalTone])
        @include('partials.dashboard.kpi', ['label' => 'Dernière arrivée', 'value' => $arriveeLabel, 'meta' => 'Pointage d\'arrivée'])
        @include('partials.dashboard.kpi', ['label' => 'Dernier départ', 'value' => $departLabel, 'meta' => 'Pointage de départ'])
        @include('partials.dashboard.kpi', ['label' => 'Moyenne 6 mois', 'value' => number_format($moyenneEvolUser, 1, ',', '') . '/20', 'meta' => 'Évolution'])
        @include('partials.dashboard.kpi', ['label' => 'Tendance', 'value' => $tendanceKpi, 'meta' => 'vs mois précédent', 'tone' => $tendanceTone])
    </div>

    <!-- ═══ Pointage + évaluation du mois ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="user-pointage" class="console-card lg:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Pointage du jour</h2>
                    <p class="console-card-sub">Arrivée, départ et fiche de rendement — géolocalisation obligatoire</p>
                </div>
                <span class="badge badge-gold">Géolocalisé</span>
            </div>
            <div class="console-card-body space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-[#080808] px-5 py-4">
                    <div>
                        <p class="text-[0.625rem] uppercase tracking-[0.22em] text-white/50">Heure actuelle</p>
                        <p id="current-time" class="mt-1 text-3xl font-extrabold tabular-nums tracking-tight text-pharaoh-gold">--:--:--</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[0.625rem] uppercase tracking-[0.18em] text-white/50">Aujourd'hui</p>
                        <p class="text-sm font-semibold capitalize text-white">{{ \Illuminate\Support\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
                    </div>
                </div>

                @if($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
                @endif

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Arrivée -->
                    <form method="POST" action="{{ route('presence.arrival') }}" id="arrival-form" class="flex flex-col rounded-2xl border border-gray-200/70 bg-gray-50/70 p-4">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude-arrival">
                        <input type="hidden" name="longitude" id="longitude-arrival">

                        <div class="mb-4 flex items-start gap-3">
                            <span class="console-quick-icon">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14" /><path stroke-linecap="round" stroke-linejoin="round" d="M17 16.5v1a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-11a3 3 0 0 1 3-3h7a3 3 0 0 1 3 3v1" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#080808]">Marquer l'arrivée</p>
                                <p class="text-xs text-gray-500">Après 08:00, la journée est comptée en retard</p>
                            </div>
                        </div>

                        <button type="button" onclick="getLocationAndSubmit('arrival-form')" class="btn-gold mt-auto w-full text-sm">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z" /><circle cx="12" cy="10" r="2.6" /></svg>
                            Marquer l'arrivée
                        </button>
                    </form>

                    <!-- Départ -->
                    <form method="POST" action="{{ route('presence.departure') }}" id="departure-form" class="flex flex-col rounded-2xl border border-gray-200/70 bg-gray-50/70 p-4">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude-departure">
                        <input type="hidden" name="longitude" id="longitude-departure">

                        <div class="mb-3 flex items-start gap-3">
                            <span class="console-quick-icon">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7" /><path stroke-linecap="round" stroke-linejoin="round" d="M13 16.5v1a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-11a3 3 0 0 1 3-3h3a3 3 0 0 1 3 3v1" /></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#080808]">Marquer le départ</p>
                                <p class="text-xs text-gray-500">La fiche de rendement est obligatoire</p>
                            </div>
                        </div>

                        <label for="rendement" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Fiche de rendement du jour <span class="text-red-600">*</span>
                        </label>
                        <textarea name="rendement" id="rendement" rows="3" required
                            placeholder="Décrivez ce que vous avez fait aujourd'hui…"
                            class="input-field mb-3 w-full text-sm">{{ old('rendement') }}</textarea>

                        <button type="button" onclick="getLocationAndSubmit('departure-form')" class="mt-auto inline-flex w-full items-center justify-center rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors duration-150 hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z" /><circle cx="12" cy="10" r="2.6" /></svg>
                            Marquer le départ
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <section id="user-evaluation" class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Mon évaluation du mois</h2>
                    <p class="console-card-sub">Note sur 20 et appréciation</p>
                </div>
                <span class="badge {{ $evalBadge }}">{{ ucfirst($evaluation['couleur']) }}</span>
            </div>
            <div class="console-card-body">
                <div class="rounded-2xl border p-6 text-center {{ $evalBox }}">
                    <p class="text-4xl font-extrabold tracking-tight">
                        {{ $evaluation['note'] }}<span class="text-base font-semibold opacity-70">/20</span>
                    </p>
                    <p class="mt-2 text-sm font-medium">{{ $evalMessage }}</p>
                    @if($evaluation['manuelle'])
                        <p class="mt-2 text-xs opacity-70">Évaluation manuelle de votre hiérarchie</p>
                    @endif
                </div>
                <p class="mt-3 text-center text-xs text-gray-500">{{ $evaluation['commentaire'] }}</p>
            </div>
        </section>
    </div>

    <!-- ═══ Historique d'évaluation ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section id="user-historique" class="console-card lg:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Historique d'évaluation</h2>
                    <p class="console-card-sub">Vos 6 derniers mois</p>
                </div>
                <span class="badge badge-gold">Note /20</span>
            </div>
            <div class="console-card-body">
                <div class="h-56"><canvas id="evaluationChart"></canvas></div>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="console-panel-row">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Moyenne (6 mois)</span>
                        <span class="text-sm font-semibold text-[#080808]">{{ number_format($moyenneEvolUser, 1, ',', '') }} / 20</span>
                    </div>
                    <div class="console-panel-row">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Tendance</span>
                        @if($tendanceUser === 'hausse')
                            <span class="text-sm font-semibold text-green-600">▲ Hausse {{ $deltaUser > 0 ? '+' . number_format($deltaUser, 1, ',', '') : '' }} pt</span>
                        @elseif($tendanceUser === 'baisse')
                            <span class="text-sm font-semibold text-red-600">▼ Baisse {{ number_format(abs($deltaUser), 1, ',', '') }} pt</span>
                        @else
                            <span class="text-sm font-semibold text-gray-500">— Stable</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Détail par mois</h2>
                    <p class="console-card-sub">Évolution mois par mois</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="console-scroll">
                    @foreach($historique as $i => $h)
                        @php
                            $prev = $historique[$i - 1] ?? null;
                            $tendance = $prev
                                ? ($h['note'] > $prev['note'] ? 'up' : ($h['note'] < $prev['note'] ? 'down' : 'flat'))
                                : 'flat';
                            $point = match ($h['couleur']) {
                                'vert' => 'bg-green-500',
                                'rouge' => 'bg-red-500',
                                default => 'bg-amber-500',
                            };
                        @endphp
                        <div class="console-panel-row">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="console-dot {{ $point }}"></span>
                                <span class="truncate text-sm capitalize text-gray-700">{{ $h['label'] }}</span>
                                @if($h['manuelle'])
                                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[0.625rem] text-gray-500">manuelle</span>
                                @endif
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-[#080808]">{{ $h['note'] }}/20</span>
                                @if($tendance === 'up')
                                    <span class="text-xs font-semibold text-green-600" title="En hausse vs mois précédent">▲</span>
                                @elseif($tendance === 'down')
                                    <span class="text-xs font-semibold text-red-600" title="En baisse vs mois précédent">▼</span>
                                @else
                                    <span class="text-xs text-gray-400" title="Stable vs mois précédent">—</span>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <!-- ═══ Résumé, notifications et accès rapides ═══ -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Résumé de présence</h2>
                    <p class="console-card-sub">Votre activité du mois</p>
                </div>
            </div>
            <div class="console-card-body">
                <div class="console-panel-row">
                    <span class="text-sm text-gray-600">Présences ce mois-ci</span>
                    <span class="text-sm font-semibold text-pharaoh-bronze">{{ $presenceCount ?? 0 }}</span>
                </div>
                <div class="console-panel-row">
                    <span class="text-sm text-gray-600">Dernière arrivée</span>
                    <span class="text-sm font-semibold text-[#080808]">{{ $arriveeLabel }}</span>
                </div>
                <div class="console-panel-row">
                    <span class="text-sm text-gray-600">Dernier départ</span>
                    <span class="text-sm font-semibold text-[#080808]">{{ $departLabel }}</span>
                </div>
                <a href="{{ route('user.presence.report') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">
                    Voir le bilan complet
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>
        </section>

        <section id="user-notifications" class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Notifications</h2>
                    <p class="console-card-sub">{{ Auth::user()->unreadNotifications->count() }} non lue(s)</p>
                </div>
                <a href="{{ route('notifications.index') }}" class="text-xs font-semibold text-pharaoh-bronze hover:text-pharaoh-gold">Voir tout →</a>
            </div>
            <div class="console-card-body">
                <div class="console-scroll">
                    @forelse($dernieresNotifications as $notification)
                        <div class="console-panel-row">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="console-dot {{ $notification->read_at ? 'bg-gray-300' : 'bg-pharaoh-gold' }}"></span>
                                <span class="truncate text-sm {{ $notification->read_at ? 'text-gray-600' : 'font-medium text-[#080808]' }}" title="{{ $notification->data['message'] ?? 'Notification' }}">
                                    {{ $notification->data['message'] ?? 'Notification' }}
                                </span>
                            </span>
                            <span class="whitespace-nowrap text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-gray-400">Vous n'avez aucune notification</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Accès rapides</h2>
                    <p class="console-card-sub">Votre espace employé</p>
                </div>
            </div>
            <div class="console-card-body space-y-2">
                <a href="{{ route('presence.index') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V12l3 1.8" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Ma présence</span>
                        <span class="block text-xs text-gray-500">Historique de pointage</span>
                    </span>
                </a>
                <a href="{{ route('user.rendement') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M6.5 15.5l3.5-4 3 2 4.5-5.5" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Mes rendements</span>
                        <span class="block text-xs text-gray-500">Fiches déposées</span>
                    </span>
                </a>
                <a href="{{ route('user.conges') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="5.5" width="16" height="14" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="M4 10h16M8.5 3.5v4M15.5 3.5v4" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Mes congés</span>
                        <span class="block text-xs text-gray-500">Demandes et suivi</span>
                    </span>
                </a>
                <a href="{{ route('notifications.index') }}" class="console-quick group">
                    <span class="console-quick-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-[#080808]">Notifications</span>
                        <span class="block text-xs text-gray-500">Alertes et rappels</span>
                    </span>
                </a>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ═══ Horloge ═══
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        const clock = document.getElementById('current-time');
        if (clock) {
            clock.textContent = `${hours}:${minutes}:${seconds}`;
        }
    }

    setInterval(updateClock, 1000);
    updateClock();

    // ═══ Géolocalisation et soumission ═══
    function getLocationAndSubmit(formId) {
        if (!navigator.geolocation) {
            displayError("La géolocalisation n'est pas prise en charge par votre navigateur.");
            return;
        }

        showLoadingMessage('Obtention de votre position...');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                hideLoadingMessage();

                if (formId === 'arrival-form') {
                    document.getElementById('latitude-arrival').value = position.coords.latitude;
                    document.getElementById('longitude-arrival').value = position.coords.longitude;
                } else {
                    document.getElementById('latitude-departure').value = position.coords.latitude;
                    document.getElementById('longitude-departure').value = position.coords.longitude;
                }

                document.getElementById(formId).submit();
            },
            function (error) {
                hideLoadingMessage();

                let errorMessage;
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = "Vous devez autoriser l'accès à votre position pour marquer votre présence.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = "Impossible de déterminer votre position. Veuillez réessayer.";
                        break;
                    case error.TIMEOUT:
                        errorMessage = "La demande de géolocalisation a expiré. Veuillez réessayer.";
                        break;
                    default:
                        errorMessage = "Une erreur inconnue s'est produite lors de la géolocalisation.";
                        break;
                }

                displayError(errorMessage);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function showLoadingMessage(message) {
        if (!document.getElementById('loading-message')) {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-message';
            loadingDiv.className = 'fixed inset-0 z-[9990] flex items-center justify-center bg-black/50';
            loadingDiv.innerHTML = `
                <div class="rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 animate-spin text-pharaoh-gold" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="loading-text" class="text-sm text-gray-700"></span>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingDiv);
        }

        document.getElementById('loading-text').textContent = message;
        document.getElementById('loading-message').style.display = 'flex';
    }

    function hideLoadingMessage() {
        const loadingMessage = document.getElementById('loading-message');
        if (loadingMessage) {
            loadingMessage.style.display = 'none';
        }
    }

    function displayError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed inset-x-0 top-0 z-[9991] mt-4 flex items-center justify-center px-4';
        errorDiv.innerHTML = `
            <div class="relative max-w-md rounded-xl border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-lg">
                <span class="block pr-6 text-sm">${message}</span>
                <span class="absolute inset-y-0 right-0 px-4 py-3" onclick="this.parentElement.parentElement.remove();">
                    <svg class="h-4 w-4 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <title>Fermer</title>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </div>
        `;
        document.body.appendChild(errorDiv);

        setTimeout(() => errorDiv.remove(), 5000);
    }

    // ═══ Historique d'évaluation ═══
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('evaluationChart');
        if (!ctx) {
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Note /20',
                    data: @json($chartNotes),
                    borderColor: '#D39B23',
                    backgroundColor: 'rgba(211, 155, 35, 0.10)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: @json($chartCouleurs),
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'Note : ' + context.parsed.y + '/20';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 20,
                        ticks: { stepSize: 4 },
                        title: { display: true, text: 'Note sur 20' }
                    },
                    x: {
                        ticks: { maxRotation: 45, minRotation: 0 }
                    }
                }
            }
        });
    });
</script>
@endpush
