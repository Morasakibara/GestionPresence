@extends('layouts.dashboard')

@section('title', 'Modifier un lieu de travail')
@section('breadcrumb', 'Lieux de travail / Modifier')

@section('header')
    <div>
        <span>Modifier le lieu de travail</span>
        <p class="console-subtitle">Ajustez la zone géographique autorisée pour le pointage.</p>
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
                <h2 class="console-card-title">{{ $workplaceLocation->nom }}</h2>
                <p class="console-card-sub">Coordonnées GPS et rayon de tolérance du site.</p>
            </div>
            <span class="badge {{ $workplaceLocation->actif ? 'badge-success' : 'badge-danger' }}">
                {{ $workplaceLocation->actif ? 'Actif' : 'Inactif' }}
            </span>
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

            <form action="{{ route('admin.workplace-locations.update', $workplaceLocation) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="nom" class="input-label mb-1">Nom du lieu <span class="text-red-600">*</span></label>
                    <input type="text" name="nom" id="nom" value="{{ old('nom', $workplaceLocation->nom) }}" class="input-field">
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="latitude" class="input-label mb-1">Latitude <span class="text-red-600">*</span></label>
                        <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $workplaceLocation->latitude) }}" class="input-field" inputmode="decimal">
                    </div>
                    <div>
                        <label for="longitude" class="input-label mb-1">Longitude <span class="text-red-600">*</span></label>
                        <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $workplaceLocation->longitude) }}" class="input-field" inputmode="decimal">
                    </div>
                </div>

                <div>
                    <label for="rayon" class="input-label mb-1">Rayon (en mètres) <span class="text-red-600">*</span></label>
                    <input type="number" name="rayon" id="rayon" value="{{ old('rayon', $workplaceLocation->rayon) }}" min="10" max="1000" class="input-field no-spin">
                    <p class="mt-1 text-xs text-gray-500">Distance maximale autorisée pour le marquage de présence, mesurée en mètres depuis le point central.</p>
                </div>

                <div>
                    <input type="hidden" name="actif" value="0">
                    <label class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                        <input type="checkbox" name="actif" value="1" {{ $workplaceLocation->actif ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-pharaoh-gold focus:ring-pharaoh-gold">
                        <span class="ml-2 text-sm font-medium text-gray-700">Actif</span>
                    </label>
                </div>

                <div id="map-container" class="h-56 w-full overflow-hidden rounded-xl border border-gray-200 sm:h-72">
                    <div id="map" class="h-full w-full"></div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-100 pt-4">
                    <a href="{{ route('admin.workplace-locations.index') }}" class="btn-secondary">Annuler</a>
                    <button type="submit" class="btn-gold">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>

    <aside class="space-y-5">
        <div class="console-card">
            <div class="console-card-body">
                <h2 class="console-card-title">Zone autorisée</h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="console-panel-row">
                        <dt class="text-gray-500">Latitude</dt>
                        <dd class="font-medium text-[#080808]">{{ $workplaceLocation->latitude }}</dd>
                    </div>
                    <div class="console-panel-row">
                        <dt class="text-gray-500">Longitude</dt>
                        <dd class="font-medium text-[#080808]">{{ $workplaceLocation->longitude }}</dd>
                    </div>
                    <div class="console-panel-row">
                        <dt class="text-gray-500">Rayon</dt>
                        <dd><span class="badge badge-info">{{ $workplaceLocation->rayon }} m</span></dd>
                    </div>
                </dl>
                <p class="mt-4 text-xs text-gray-500">Déplacez le marqueur ou cliquez sur la carte pour repositionner le lieu. Le rayon du cercle suit la valeur saisie.</p>
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
    let initialLocation = {
        lat: {{ $workplaceLocation->latitude }},
        lng: {{ $workplaceLocation->longitude }}
    };

    function initMap() {
        const mapElement = document.getElementById('map');
        if (!mapElement) {
            return;
        }

        map = new google.maps.Map(mapElement, {
            center: initialLocation,
            zoom: 15,
        });

        // Créer un marqueur
        marker = new google.maps.Marker({
            position: initialLocation,
            map: map,
            draggable: true,
        });

        // Afficher un cercle avec le rayon
        const circle = new google.maps.Circle({
            strokeColor: "#D39B23",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#D39B23",
            fillOpacity: 0.2,
            map: map,
            center: initialLocation,
            radius: {{ $workplaceLocation->rayon }}
        });

        // Mettre à jour les champs lorsque le marqueur est déplacé
        google.maps.event.addListener(marker, 'dragend', function() {
            const position = marker.getPosition();
            updateFields(position);
            circle.setCenter(position);
        });

        // Permettre de cliquer sur la carte pour placer le marqueur
        google.maps.event.addListener(map, 'click', function(event) {
            marker.setPosition(event.latLng);
            circle.setCenter(event.latLng);
            updateFields(event.latLng);
        });

        // Mettre à jour le rayon du cercle quand la valeur change
        document.getElementById('rayon').addEventListener('input', function() {
            circle.setRadius(parseInt(this.value));
        });
    }

    // Mettre à jour les champs de latitude et longitude
    function updateFields(position) {
        document.getElementById('latitude').value = position.lat();
        document.getElementById('longitude').value = position.lng();
    }
</script>
@endpush
