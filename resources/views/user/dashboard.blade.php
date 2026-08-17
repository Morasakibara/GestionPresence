@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Tableau de bord Employé</span>
    <!-- Indicateur de notifications -->
    <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center px-2 py-1 text-sm font-medium rounded-md text-3hcig-blue hover:bg-gray-100">
        <svg class="w-6 h-6 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        Notifications
        @if(Auth::user()->unreadNotifications->count() > 0)
            <span class="absolute right-0 flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full -top-1">
                {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
            </span>
        @endif
    </a>
</div>
@endsection

@section('navigation')
<!-- Current: "bg-3hcig-blue text-white", Default: "text-gray-300 hover:bg-3hcig-blue hover:text-white" -->
<a href="{{ route('user.dashboard') }}" class="px-3 py-2 text-sm font-medium text-white rounded-md bg-3hcig-blue" aria-current="page">Tableau de bord</a>
<a href="{{ route('presence.index') }}" class="px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-3hcig-blue hover:text-white">Présence</a>
<a href="{{ route('user.presence.report') }}" class="px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-3hcig-blue hover:text-white">Bilan de présence</a>
<a href="{{ route('user.rendement') }}" class="px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-3hcig-blue hover:text-white">Mes rendements</a>
<a href="{{ route('notifications.index') }}" class="relative px-3 py-2 text-sm font-medium text-gray-300 rounded-md hover:bg-3hcig-blue hover:text-white">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full -top-1 -right-1">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('user.dashboard') }}" class="block px-3 py-2 text-base font-medium text-white rounded-md bg-3hcig-blue" aria-current="page">Tableau de bord</a>
<a href="{{ route('presence.index') }}" class="block px-3 py-2 text-base font-medium text-gray-300 rounded-md hover:bg-3hcig-blue hover:text-white">Présence</a>
<a href="{{ route('user.presence.report') }}" class="block px-3 py-2 text-base font-medium text-gray-300 rounded-md hover:bg-3hcig-blue hover:text-white">Bilan de présence</a>
<a href="{{ route('user.rendement') }}" class="block px-3 py-2 text-base font-medium text-gray-300 rounded-md hover:bg-3hcig-blue hover:text-white">Mes rendements</a>
<a href="{{ route('notifications.index') }}" class="relative block px-3 py-2 text-base font-medium text-gray-300 rounded-md hover:bg-3hcig-blue hover:text-white">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full top-2 right-2">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
    <div class="pharaoh-card p-6">
        <h2 class="mb-4 text-xl font-semibold text-gray-900">Marquer la présence</h2>

        <div class="space-y-4">
            @php
                // Pointage libre : plus de restriction horaire ni de week-end
                $isWeekend = false;
            @endphp

            @if($isWeekend)
                <div class="p-4 rounded-md bg-red-50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                Le marquage de présence n'est pas disponible pendant le week-end.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <!-- Bouton pour marquer l'heure d'arrivée -->
                <div>
                    @if(true)
                        <form method="POST" action="{{ route('presence.arrival') }}" id="arrival-form">
                            @csrf
                            <input type="hidden" name="latitude" id="latitude-arrival">
                            <input type="hidden" name="longitude" id="longitude-arrival">
                            <button type="button" onclick="getLocationAndSubmit('arrival-form')" class="btn-gold w-full px-4 py-2.5 text-base">
                                <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Marquer l'arrivée
                            </button>
                        </form>
                    @else
                        <div class="p-4 rounded-md bg-yellow-50">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Le bouton d'arrivée est actif entre 7h et 10h
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Bouton pour marquer l'heure de départ -->
                <div>
                    @if(true)
                        <form method="POST" action="{{ route('presence.departure') }}" id="departure-form">
                            @csrf
                            <input type="hidden" name="latitude" id="latitude-departure">
                            <input type="hidden" name="longitude" id="longitude-departure">
                            <label for="rendement" class="block text-sm font-medium text-gray-700 mb-1">Fiche de rendement du jour <span class="text-red-600">*</span></label>
                            <textarea name="rendement" id="rendement" rows="3" required
                                placeholder="Décrivez ce que vous avez fait aujourd'hui..."
                                class="block w-full px-3 py-2 mb-3 text-sm border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue"></textarea>
                            <button type="button" onclick="getLocationAndSubmit('departure-form')" class="btn-press inline-flex items-center justify-center w-full rounded-lg bg-green-600 px-4 py-2.5 text-base font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                                <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Marquer le départ
                            </button>
                        </form>
                    @else
                        <div class="p-4 rounded-md bg-yellow-50">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Le bouton de départ est actif entre 17h et 18h30
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Horloge et informations -->
            <div class="p-4 mt-2 rounded-md bg-gray-50">
                <div class="flex justify-center">
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-500">Heure actuelle</div>
                        <div class="mt-1 text-xl font-semibold text-3hcig-blue-dark" id="current-time"></div>
                    </div>
                </div>
            </div>

            <!-- Messages de feedback -->
            @if(session('success'))
                <div class="p-4 rounded-md bg-3hcig-green-light/20 text-3hcig-green-dark">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-3hcig-green" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-3hcig-green-dark">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-md bg-red-50">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                {{ $errors->first() }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="pharaoh-card p-6">
        <h2 class="mb-4 text-xl font-semibold text-gray-900">Résumé de présence</h2>

        <div class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <span class="text-sm font-medium text-gray-500">Présences ce mois-ci</span>
                <span class="text-lg font-semibold text-3hcig-blue">{{ isset($presenceCount) ? $presenceCount : '0' }}</span>
            </div>

            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <span class="text-sm font-medium text-gray-500">Dernière arrivée</span>
                <span class="text-gray-700">{{ isset($lastArrival) ? $lastArrival->format('d/m/Y H:i') : 'Aucune donnée' }}</span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Dernier départ</span>
                <span class="text-gray-700">{{ isset($lastDeparture) ? $lastDeparture->format('d/m/Y H:i') : 'Aucune donnée' }}</span>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('user.presence.report') }}" class="inline-flex items-center text-sm font-medium text-3hcig-blue hover:text-3hcig-blue-light">
                Voir le bilan complet
                <svg class="w-5 h-5 ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>

    @php
        $couleurClasses = [
            'vert' => ['bg-green-50 text-green-700 ring-green-600/20', '🟢'],
            'orange' => ['bg-yellow-50 text-yellow-700 ring-yellow-600/20', '🟠'],
            'rouge' => ['bg-red-50 text-red-700 ring-red-600/20', '🔴'],
        ];
        $c = $couleurClasses[$evaluation['couleur']] ?? $couleurClasses['orange'];
    @endphp
    <div class="pharaoh-card p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-[#080808]">Mon évaluation du mois</h2>
            <span class="badge {{ $c[0] }} ring-1 ring-inset">{{ $evaluation['couleur'] }}</span>
        </div>
        <div class="flex items-center justify-center">
            <div class="w-full rounded-2xl ring-1 ring-inset p-6 text-center {{ $c[0] }}">
                <div class="text-4xl">{{ $c[1] }}</div>
                <div class="mt-2 text-4xl font-bold tracking-tight">{{ $evaluation['note'] }}/20</div>
                <div class="mt-2 text-sm font-medium">
                    {{ $evaluation['couleur'] === 'vert' ? 'Excellent — discipline et rendement au rendez-vous' : ($evaluation['couleur'] === 'orange' ? 'Satisfaisant — à surveiller' : 'Critique — attention discipline et rendement') }}
                </div>
                @if($evaluation['manuelle'])
                    <div class="mt-2 text-xs">Évaluation manuelle de votre hiérarchie</div>
                @endif
            </div>
        </div>
        <p class="mt-3 text-xs text-gray-500 text-center">{{ $evaluation['commentaire'] }}</p>
    </div>

    <div class="pharaoh-card p-6">
        <h2 class="mb-4 text-xl font-semibold text-gray-900">Mon historique d'évaluation (6 mois)</h2>
        <div class="space-y-2">
            @php
                $histoClasses = [
                    'vert' => ['bg-green-50 border-green-200 text-green-800', 'bg-green-500'],
                    'orange' => ['bg-orange-50 border-orange-200 text-orange-800', 'bg-orange-500'],
                    'rouge' => ['bg-red-50 border-red-200 text-red-800', 'bg-red-500'],
                ];
            @endphp
            @foreach($historique as $i => $h)
                @php
                    $hc = $histoClasses[$h['couleur']] ?? $histoClasses['orange'];
                    $prev = $historique[$i - 1] ?? null;
                    $tendance = $prev
                        ? ($h['note'] > $prev['note'] ? 'up' : ($h['note'] < $prev['note'] ? 'down' : 'flat'))
                        : 'flat';
                @endphp
                <div class="flex items-center justify-between rounded-lg border px-4 py-2.5 {{ $hc[0] }}">
                    <div class="flex items-center gap-3">
                        <span class="inline-block h-3 w-3 rounded-full {{ $hc[1] }}"></span>
                        <span class="text-sm font-medium capitalize">{{ $h['label'] }}</span>
                        @if($h['manuelle'])
                            <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-500">manuelle</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold">{{ $h['note'] }}/20</span>
                        @if($tendance === 'up')
                            <span class="text-xs font-semibold text-green-600" title="En hausse vs mois précédent">▲</span>
                        @elseif($tendance === 'down')
                            <span class="text-xs font-semibold text-red-600" title="En baisse vs mois précédent">▼</span>
                        @else
                            <span class="text-xs text-gray-400" title="Stable vs mois précédent">—</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-gray-500">Évolution de votre note sur 20 mois par mois. <span class="text-green-600">▲ hausse</span> · <span class="text-red-600">▼ baisse</span> · <span class="text-gray-400">— stable</span></p>
        <div class="mt-4">
            <canvas id="evaluationChart" height="120"></canvas>
        </div>
    </div>

    <div class="pharaoh-card p-6">
        <h2 class="flex items-center justify-between mb-4 text-xl font-semibold text-gray-900">
            <span>Notifications</span>
            @if(Auth::user()->unreadNotifications->count() > 0)
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-medium text-white bg-red-600 rounded-full">
                    {{ Auth::user()->unreadNotifications->count() }}
                </span>
            @endif
        </h2>

        <div class="space-y-3">
            @if(Auth::user()->notifications->count() > 0)
                @foreach(Auth::user()->notifications->take(3) as $notification)
                    <div class="border-l-4 {{ $notification->read_at ? 'border-gray-300 bg-gray-50' : 'border-3hcig-blue bg-blue-50' }} p-3">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-700 {{ $notification->read_at ? '' : 'font-medium' }}">
                                {{ isset($notification->data['message']) ? $notification->data['message'] : 'Notification' }}
                            </p>
                            <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="mt-4 text-center">
                    <a href="{{ route('notifications.index') }}" class="inline-flex items-center text-sm font-medium text-3hcig-blue hover:text-3hcig-blue-light">
                        Voir toutes les notifications
                        <svg class="w-5 h-5 ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <p class="mt-3 text-sm text-gray-500">Vous n'avez aucune notification</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Affichage et mise à jour de l'heure actuelle
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
    }

    // Mettre à jour l'heure chaque seconde
    setInterval(updateClock, 1000);
    updateClock(); // Appel initial

    // Fonction pour obtenir la géolocalisation et soumettre le formulaire
    function getLocationAndSubmit(formId) {
        if (navigator.geolocation) {
            // Afficher un message de chargement
            showLoadingMessage('Obtention de votre position...');

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Position obtenue avec succès
                    hideLoadingMessage();

                    // Remplir les champs cachés
                    if (formId === 'arrival-form') {
                        document.getElementById('latitude-arrival').value = position.coords.latitude;
                        document.getElementById('longitude-arrival').value = position.coords.longitude;
                    } else {
                        document.getElementById('latitude-departure').value = position.coords.latitude;
                        document.getElementById('longitude-departure').value = position.coords.longitude;
                    }

                    // Soumettre le formulaire
                    document.getElementById(formId).submit();
                },
                function(error) {
                    // Erreur lors de l'obtention de la position
                    hideLoadingMessage();

                    let errorMessage;
                    switch(error.code) {
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
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            displayError("La géolocalisation n'est pas prise en charge par votre navigateur.");
        }
    }

    // Fonction pour afficher un message de chargement
    function showLoadingMessage(message) {
        // Créer un div pour le message de chargement s'il n'existe pas déjà
        if (!document.getElementById('loading-message')) {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-message';
            loadingDiv.className = 'fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50';
            loadingDiv.innerHTML = `
                <div class="p-6 bg-white rounded-lg shadow-xl">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 -ml-1 animate-spin text-3hcig-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="loading-text" class="text-gray-700"></span>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingDiv);
        }

        document.getElementById('loading-text').textContent = message;
        document.getElementById('loading-message').style.display = 'flex';
    }

    // Fonction pour masquer le message de chargement
    function hideLoadingMessage() {
        const loadingMessage = document.getElementById('loading-message');
        if (loadingMessage) {
            loadingMessage.style.display = 'none';
        }
    }

    // Fonction pour afficher un message d'erreur
    function displayError(message) {
        // Créer un div pour le message d'erreur
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed inset-x-0 top-0 flex items-center justify-center mt-4 z-50';
        errorDiv.innerHTML = `
            <div class="relative max-w-md px-4 py-3 text-red-700 bg-red-100 border border-red-400 rounded">
                <span class="block sm:inline">${message}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.parentElement.remove();">
                    <svg class="w-6 h-6 text-red-500 fill-current" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <title>Fermer</title>
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </span>
            </div>
        `;
        document.body.appendChild(errorDiv);

        // Supprimer le message après 5 secondes
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
</script>

@php
    $chartLabels = array_map(fn ($h) => ucfirst($h['label']), $historique);
    $chartNotes = array_map(fn ($h) => (float) $h['note'], $historique);
    $chartCouleurs = array_map(function ($h) {
        return $h['couleur'] === 'vert' ? '#22c55e' : ($h['couleur'] === 'rouge' ? '#ef4444' : '#f97316');
    }, $historique);
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('evaluationChart');
        if (!ctx) {
            return;
        }
        const labels = @json($chartLabels);
        const data = @json($chartNotes);
        const couleurs = @json($chartCouleurs);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Note /20',
                    data: data,
                    borderColor: '#D39B23',
                    backgroundColor: 'rgba(211, 155, 35, 0.10)',
                    borderWidth: 2,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: couleurs,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
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
@endsection
