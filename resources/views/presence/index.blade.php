@extends('layouts.app')

@section('title', 'Marquer la présence')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-md">
        <div class="rounded-lg bg-white p-6 shadow-sm">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-3hcig-blue-dark">Marquer la présence</h1>
                <p class="mt-2 text-sm text-gray-600">Enregistrez vos heures d'arrivée et de départ</p>
            </div>

            <div class="space-y-6">
                @php
                    $currentDay = now()->dayOfWeek;
                    $isWeekend = $currentDay === 0 || $currentDay === 6; // 0 = dimanche, 6 = samedi
                @endphp

                @if($isWeekend)
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">
                                    Le marquage de présence n'est pas disponible pendant le week-end.
                                </p>
                                <p class="mt-1 text-sm text-red-700">
                                    Veuillez revenir durant les jours ouvrables (lundi à vendredi).
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Bouton pour marquer l'heure d'arrivée -->
                    <div>
                        @if(now()->hour >= 7 && now()->hour <= 10)
                            <form method="POST" action="{{ route('presence.arrival') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center justify-center rounded-md bg-3hcig-blue px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    Marquer l'heure d'arrivée
                                </button>
                            </form>
                        @else
                            <div class="rounded-md bg-yellow-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            Le bouton d'arrivée est actif uniquement entre 7h et 10h.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Bouton pour marquer l'heure de départ -->
                    <div>
                        @if(now()->hour >= 17 && now()->hour <= 18 && now()->minute <= 30)
                            <form method="POST" action="{{ route('presence.departure') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center justify-center rounded-md bg-3hcig-green px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-3hcig-green-light focus:outline-none focus:ring-2 focus:ring-3hcig-green focus:ring-offset-2">
                                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Marquer l'heure de départ
                                </button>
                            </form>
                        @else
                            <div class="rounded-md bg-yellow-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            Le bouton de départ est actif uniquement entre 17h et 18h30.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Messages de feedback -->
                @if(session('success'))
                    <div class="rounded-md bg-3hcig-green-light/20 p-4 text-3hcig-green-dark">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-3hcig-green" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
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
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
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
            <div class="mt-8 rounded-md bg-gray-50 p-4">
                <div class="flex justify-center">
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-500">Heure actuelle</div>
                        <div class="mt-1 text-xl font-semibold text-3hcig-blue-dark" id="current-time"></div>
                        <div class="mt-2 text-xs text-gray-500">
                            @if($isWeekend)
                                Week-end: Système non disponible
                            @else
                                Arrivée: 7h00 - 10h00 | Départ: 17h00 - 18h30
                            @endif
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
</script>
@endsection
