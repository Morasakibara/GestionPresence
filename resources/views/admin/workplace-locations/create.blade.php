@extends('layouts.app')

@section('header')
<div class="flex items-center justify-between">
    <span>Ajouter un lieu de travail</span>
</div>
@endsection

@section('content')
<div class="px-4 py-8 mx-auto sm:px-6 lg:px-8">
    <div class="p-6 bg-white rounded-2xl border border-gray-200/70 shadow-card">
        <div class="page-heading mb-6">
            <div>
                <h1 class="page-heading-title">Ajouter un lieu de travail</h1>
                <p class="page-heading-sub">Définissez une zone géographique autorisée pour le pointage</p>
            </div>
            <a href="{{ route('admin.workplace-locations.index') }}" class="btn-secondary">
                Retour à la liste
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.workplace-locations.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom du lieu</label>
                <input type="text" name="nom" id="nom" value="{{ old('nom') }}" class="input-field mt-1">
            </div>

            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                    <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}" class="input-field mt-1">
                </div>
                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                    <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}" class="input-field mt-1">
                </div>
            </div>

            <div class="mb-4">
                <label for="rayon" class="block text-sm font-medium text-gray-700">Rayon (en mètres)</label>
                <input type="number" name="rayon" id="rayon" value="{{ old('rayon', 100) }}" min="10" max="1000" class="input-field mt-1">
                <p class="mt-1 text-sm text-gray-500">Distance maximale autorisée pour le marquage de présence, mesurée en mètres depuis le point central.</p>
            </div>

            <div class="mb-6">
                <label class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                    <input type="checkbox" name="actif" value="1" checked class="h-4 w-4 rounded border-gray-300 text-pharaoh-gold focus:ring-pharaoh-gold">
                    <span class="ml-2 text-sm font-medium text-gray-700">Actif</span>
                </label>
            </div>

            <div class="mb-4">
                <p class="mb-2 text-sm text-gray-700">Obtenir les coordonnées actuelles:</p>
                <button type="button" id="getLocation" class="btn-press px-4 py-2 text-pharaoh-bronze-dark bg-pharaoh-gold/10 rounded-lg hover:bg-pharaoh-gold/20 transition-colors duration-150">
                    Utiliser ma position actuelle
                </button>
            </div>

            <div class="w-full h-64 mt-8 overflow-hidden rounded-lg" id="map-container">
                <div id="map" class="w-full h-full"></div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="btn-gold btn-press">
                    Ajouter le lieu de travail
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyALsxmxyKnJHHlBIeb1po8TdV_MygPaPw8&callback=initMap" async defer></script>
<script>
    let map;
    let marker;

    function initMap() {
        // Coordonnées par défaut (peuvent être modifiées)
        const defaultLocation = { lat: 48.8566, lng: 2.3522 }; // Paris

        map = new google.maps.Map(document.getElementById("map"), {
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
@endsection
