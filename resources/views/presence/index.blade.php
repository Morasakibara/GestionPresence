@extends('layouts.app')

@section('title', 'Marquer la présence')

@section('content')
<div class="container px-4 py-6 mx-auto sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <div class="p-6 bg-white rounded-lg shadow-sm">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-3hcig-blue-dark">Marquer la présence</h1>
                <p class="mt-2 text-sm text-gray-600">Enregistrez vos heures d'arrivée et de départ</p>
            </div>

            <div class="space-y-6">
                <!-- Bouton pour marquer l'heure d'arrivée -->
                <div>
                    <form method="POST" action="{{ route('presence.arrival') }}" id="arrival-form">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude-arrival">
                        <input type="hidden" name="longitude" id="longitude-arrival">
                        <button type="button" onclick="getLocationAndSubmit('arrival-form')" class="flex items-center justify-center w-full px-4 py-3 text-base font-medium text-white rounded-md shadow-sm bg-3hcig-blue hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                            <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Marquer l'heure d'arrivée
                        </button>
                    </form>
                </div>

                <!-- Bouton pour marquer l'heure de départ avec fiche de rendement -->
                <div>
                    <form method="POST" action="{{ route('presence.departure') }}" id="departure-form">
                        @csrf
                        <input type="hidden" name="latitude" id="latitude-departure">
                        <input type="hidden" name="longitude" id="longitude-departure">
                        <label for="rendement" class="block text-sm font-medium text-gray-700 mb-1">
                            Fiche de rendement du jour <span class="text-red-600">*</span>
                        </label>
                        <textarea name="rendement" id="rendement" rows="4" required
                            placeholder="Décrivez ce que vous avez fait aujourd'hui : tâches effectuées, projets avancés, résultats obtenus..."
                            class="block w-full px-3 py-2 mb-3 text-sm border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue"></textarea>
                        <button type="button" onclick="getLocationAndSubmit('departure-form')" class="flex items-center justify-center w-full px-4 py-3 text-base font-medium text-white rounded-md shadow-sm bg-3hcig-green hover:bg-3hcig-green-light focus:outline-none focus:ring-2 focus:ring-3hcig-green focus:ring-offset-2">
                            <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Marquer l'heure de départ
                        </button>
                    </form>
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

            <!-- Horloge et informations -->
            <div class="p-4 mt-8 rounded-md bg-gray-50">
                <div class="flex justify-center">
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-500">Heure actuelle</div>
                        <div class="mt-1 text-xl font-semibold text-3hcig-blue-dark" id="current-time"></div>
                        <div class="mt-2 text-xs text-gray-500">
                            Pointage ouvert à toute heure — renseignez votre fiche de rendement avant de pointer votre départ.
                        </div>
                    </div>
                </div>
            </div>
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
@endsection
