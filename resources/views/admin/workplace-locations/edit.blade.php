@extends('layouts.app')

@section('header')
<div class="flex items-center justify-between">
    <span>Modifier un lieu de travail</span>
</div>
@endsection

@section('content')
<div class="mx-auto sm:px-6 lg:px-8 px-4 py-8">
    <div class="p-6 bg-white rounded-2xl border border-gray-200/70 shadow-card">
        <div class="page-heading mb-6">
            <div>
                <h1 class="page-heading-title">Modifier le lieu de travail</h1>
                <p class="page-heading-sub">Ajustez la zone géographique autorisée pour le pointage</p>
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
    
        <form action="{{ route('admin.workplace-locations.update', $workplaceLocation) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom du lieu</label>
                <input type="text" name="nom" id="nom" value="{{ old('nom', $workplaceLocation->nom) }}" class="input-field mt-1">
            </div>
    
            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                    <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $workplaceLocation->latitude) }}" class="input-field mt-1">
                </div>
                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                    <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $workplaceLocation->longitude) }}" class="input-field mt-1">
                </div>
            </div>
    
            <div class="mb-4">
                <label for="rayon" class="block text-sm font-medium text-gray-700">Rayon (en mètres)</label>
                <input type="number" name="rayon" id="rayon" value="{{ old('rayon', $workplaceLocation->rayon) }}" min="10" max="1000" class="input-field mt-1">
                <p class="mt-1 text-sm text-gray-500">Distance maximale autorisée pour le marquage de présence, mesurée en mètres depuis le point central.</p>
            </div>
    
            <div class="mb-6">
                <label class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                    <input type="checkbox" name="actif" value="1" {{ $workplaceLocation->actif ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-pharaoh-gold focus:ring-pharaoh-gold">
                    <span class="ml-2 text-sm font-medium text-gray-700">Actif</span>
                </label>
            </div>
    
            <div class="w-full h-64 mt-8 overflow-hidden rounded-lg" id="map-container">
                <div id="map" class="w-full h-full"></div>
            </div>
    
            <div class="flex justify-end mt-6 space-x-3">
                <button type="submit" class="btn-gold btn-press">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyALsxmxyKnJHHlBIeb1po8TdV_MygPaPw8&callback=initMap" async defer></script>
<script>
    let map;
    let marker;
    let initialLocation = {
        lat: {{ $workplaceLocation->latitude }},
        lng: {{ $workplaceLocation->longitude }}
    };

    function initMap() {
        map = new google.maps.Map(document.getElementById("map"), {
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
@endsection
