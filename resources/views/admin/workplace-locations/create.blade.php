@extends('layouts.dashboard')

@section('title', 'Ajouter un lieu de travail')
@section('breadcrumb', 'Lieux de travail / Ajouter')

@section('header')
    <div>
        <span>Ajouter un lieu de travail</span>
        <p class="console-subtitle">Définissez une zone géographique autorisée pour le pointage.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.workplace-locations.index') }}" class="btn-secondary">
            <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Retour à la liste
        </a>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="console-card xl:col-span-2">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Nouveau lieu</h2>
                <p class="console-card-sub">Coordonnées GPS et rayon de tolérance du site.</p>
            </div>
            <span class="badge badge-gold">Géolocalisation</span>
        </div>

        <div class="console-card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.workplace-locations.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="nom" class="input-label mb-1">Nom du lieu <span class="text-red-600">*</span></label>
                    <input type="text" name="nom" id="nom" value="{{ old('nom') }}" class="input-field" placeholder="Siège Le Pharaon">
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="latitude" class="input-label mb-1">Latitude <span class="text-red-600">*</span></label>
                        <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}" class="input-field" inputmode="decimal" placeholder="48.8566">
                    </div>
                    <div>
                        <label for="longitude" class="input-label mb-1">Longitude <span class="text-red-600">*</span></label>
                        <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}" class="input-field" inputmode="decimal" placeholder="2.3522">
                    </div>
                </div>

                <div>
                    <label for="rayon" class="input-label mb-1">Rayon (en mètres) <span class="text-red-600">*</span></label>
                    <input type="number" name="rayon" id="rayon" value="{{ old('rayon', 100) }}" min="10" max="1000" class="input-field no-spin">
                    <p class="mt-1 text-xs text-gray-500">Distance maximale autorisée pour le marquage de présence, mesurée en mètres depuis le point central.</p>
                </div>

                <div>
                    <input type="hidden" name="actif" value="0">
                    <label class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                        <input type="checkbox" name="actif" value="1" checked class="h-4 w-4 rounded border-gray-300 text-pharaoh-gold focus:ring-pharaoh-gold">
                        <span class="ml-2 text-sm font-medium text-gray-700">Actif</span>
                    </label>
                </div>

                <div>
                    <p class="mb-2 text-sm text-gray-700">Obtenir les coordonnées actuelles :</p>
                    <button type="button" id="getLocation" class="inline-flex items-center gap-2 rounded-lg bg-pharaoh-gold/10 px-4 py-2 text-sm font-semibold text-pharaoh-bronze-dark transition-colors hover:bg-pharaoh-gold/20">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Utiliser ma position actuelle
                    </button>
                </div>

                <div id="map-container" class="h-56 w-full overflow-hidden rounded-xl border border-gray-200 sm:h-72">
                    <div id="map" class="h-full w-full"></div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-100 pt-4">
                    <a href="{{ route('admin.workplace-locations.index') }}" class="btn-secondary">Annuler</a>
                    <button type="submit" class="btn-gold">Ajouter le lieu de travail</button>
                </div>
            </form>
        </div>
    </div>

    <aside class="space-y-5">
        <div class="console-card">
            <div class="console-card-body">
                <h2 class="console-card-title">Comment procéder</h2>
                <ul class="mt-3 space-y-2 text-sm text-gray-600">
                    <li class="console-panel-row">
                        <span>1. Position</span>
                        <span class="text-xs text-gray-500">Cliquez sur la carte</span>
                    </li>
                    <li class="console-panel-row">
                        <span>2. Rayon</span>
                        <span class="text-xs text-gray-500">Entre 10 et 1000 m</span>
                    </li>
                    <li class="console-panel-row">
                        <span>3. Statut</span>
                        <span class="badge badge-gold">Actif</span>
                    </li>
                </ul>
                <p class="mt-4 text-xs text-gray-500">Le marqueur peut être déplacé à la main ; la latitude et la longitude se mettent à jour automatiquement.</p>
            </div>
        </div>
    </aside>
</div>
@endsection

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyALsxmxyKnJHHlBIeb1po8TdV_MygPaPw8&callback=initMap" async defer></script>
<script>
    let map;
    let marker;

    function initMap() {
        // Coordonnées par défaut (peuvent être modifiées)
        const defaultLocation = { lat: 48.8566, lng: 2.3522 }; // Paris

        const mapElement = document.getElementById('map');
        if (!mapElement) {
            return;
        }

        map = new google.maps.Map(mapElement, {
            center: defaultLocation,
            zoom: 15,
        });

        // Créer un marqueur initial
        marker = new google.maps.Marker({
            position: defaultLocation,
            map: map,
            draggable: true,
        });

        // Mettre à jour les champs lorsque le marqueur est déplacé
        google.maps.event.addListener(marker, 'dragend', function() {
            updateFields(marker.getPosition());
        });

        // Permettre de cliquer sur la carte pour placer le marqueur
        google.maps.event.addListener(map, 'click', function(event) {
            marker.setPosition(event.latLng);
            updateFields(event.latLng);
        });
    }

    // Mettre à jour les champs de latitude et longitude
    function updateFields(position) {
        document.getElementById('latitude').value = position.lat();
        document.getElementById('longitude').value = position.lng();
    }

    // Utiliser la géolocalisation du navigateur
    document.getElementById('getLocation').addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Mettre à jour le marqueur et la carte
                    marker.setPosition(pos);
                    map.setCenter(pos);
                    updateFields(new google.maps.LatLng(pos.lat, pos.lng));
                },
                function() {
                    alert("Impossible d'obtenir votre position actuelle.");
                }
            );
        } else {
            alert("La géolocalisation n'est pas prise en charge par votre navigateur.");
        }
    });
</script>
@endpush
