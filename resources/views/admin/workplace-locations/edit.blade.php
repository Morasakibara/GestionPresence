@extends('layouts.app')

@section('header')
<div class="flex items-center justify-between">
    <span>Modifier un lieu de travail</span>
</div>
@endsection

@section('content')
<div class="mx-auto sm:px-6 lg:px-8 px-4 py-8">
    <div class="p-6 bg-white rounded-2xl border border-gray-200/70 shadow-card">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-[#080808]">Modifier le lieu de travail</h1>
            <a href="{{ route('admin.workplace-locations.index') }}" class="btn-press inline-flex items-center rounded-lg bg-pharaoh-gold/10 px-3 py-1.5 text-sm font-semibold text-pharaoh-bronze-dark hover:bg-pharaoh-gold/20">
                Retour à la liste
            </a>
        </div>
    
        @if ($errors->any())
            <div class="px-4 py-3 mb-4 text-red-700 bg-red-50 border border-red-200 rounded-lg">
                <ul>
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
                <input type="text" name="nom" id="nom" value="{{ old('nom', $workplaceLocation->nom) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-3hcig-blue focus:border-3hcig-blue sm:text-sm">
            </div>
    
            <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                    <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $workplaceLocation->latitude) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-3hcig-blue focus:border-3hcig-blue sm:text-sm">
                </div>
                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                    <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $workplaceLocation->longitude) }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-3hcig-blue focus:border-3hcig-blue sm:text-sm">
                </div>
            </div>
    
            <div class="mb-4">
                <label for="rayon" class="block text-sm font-medium text-gray-700">Rayon (en mètres)</label>
                <input type="number" name="rayon" id="rayon" value="{{ old('rayon', $workplaceLocation->rayon) }}" min="10" max="1000" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-3hcig-blue focus:border-3hcig-blue sm:text-sm">
                <p class="mt-1 text-sm text-gray-500">Distance maximale autorisée pour le marquage de présence, mesurée en mètres depuis le point central.</p>
            </div>
    
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="actif" value="1" {{ $workplaceLocation->actif ? 'checked' : '' }} class="w-4 h-4 border-gray-300 rounded focus:ring-3hcig-blue text-3hcig-blue">
                    <span class="ml-2 text-sm text-gray-700">Actif</span>
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
