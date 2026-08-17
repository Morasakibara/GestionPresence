@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header avec le logo -->
    <header class="py-6 bg-white shadow-md z-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex justify-center">
            <img class="h-16 w-auto" src="{{ asset('storage/avatars/logo-pharaon.png') }}" alt="Le Pharaon">
        </div>
    </header>

    <!-- Section principale avec image de fond -->
    <main class="flex-grow flex flex-col">
        <div class="relative flex-grow">
            <!-- Image de fond avec overlay -->
            <div class="absolute inset-0 z-0">
                <img src="{{ asset('storage/avatars/indexBackground.jpg') }}" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-white bg-opacity-80"></div>
            </div>

            <!-- Contenu -->
            <div class="relative z-10 container mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Titre et sous-titre -->
                <div class="text-center mb-16">
                    <div class="mx-auto mb-6 inline-flex items-center gap-2 rounded-full border border-pharaoh-gold/40 bg-pharaoh-gold/10 px-4 py-1.5 text-sm font-semibold text-pharaoh-bronze">
                        <span class="h-2 w-2 rounded-full bg-pharaoh-gold"></span>
                        Gestion de présence · Rendement · Évaluation
                    </div>
                    <h1 class="text-4xl font-extrabold text-[#080808] mb-4 tracking-tight">Bienvenue sur <span class="text-pharaoh-bronze">Le Pharaon</span></h1>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Suivez et gérez facilement les présences, générez des rapports détaillés et optimisez le suivi du rendement de vos collaborateurs.
                    </p>
                </div>

                <!-- Cartes pour connexion et inscription -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto">
    <!-- Carte connexion -->
    <div class="group overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-card transition-shadow duration-200 hover:shadow-lg">
        <div class="relative h-56 overflow-hidden">
            <img src="{{ asset('storage/avatars/loginBackground.jpg') }}" alt="Connexion" class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/40 to-black/20"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <h2 class="text-4xl font-bold text-white drop-shadow">Espace connexion</h2>
            </div>
        </div>
        <div class="p-8">
            <p class="mb-8 text-gray-600">
                Déjà membre ? Connectez-vous pour accéder à votre espace personnel et gérer vos présences ou celles de votre équipe.
            </p>
            <a href="{{ route('login') }}" class="btn-gold block w-full px-6 py-4 text-center text-base">
                Se connecter
            </a>
        </div>
    </div>

    <!-- Carte inscription -->
    <div class="group overflow-hidden rounded-2xl border border-gray-200/70 bg-white shadow-card transition-shadow duration-200 hover:shadow-lg">
        <div class="relative h-56 overflow-hidden">
            <img src="{{ asset('storage/avatars/regiterBackground.jpg') }}" alt="Inscription" class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/40 to-black/20"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <h2 class="text-4xl font-bold text-white drop-shadow">Nouvel administrateur</h2>
            </div>
        </div>
        <div class="p-8">
            <p class="mb-8 text-gray-600">
                Nouveau membre ? Rejoignez notre coopérative pour bénéficier de notre outil de gestion de présence. Un code d'accès est requis.
            </p>
            <button
                onclick="document.getElementById('accessCodeModal').classList.remove('hidden')"
                class="btn-press block w-full rounded-lg bg-green-600 px-6 py-4 text-center text-base font-bold text-white shadow-lg transition-colors duration-150 hover:bg-green-500">
                S'enregistrer
            </button>
        </div>
    </div>
</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 bg-white border-t border-gray-200 shadow-inner mt-auto">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-600">&copy; {{ date('Y') }} Le Pharaon. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Modal pour code d'accès -->
    <div id="accessCodeModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-60 backdrop-blur-sm">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md rounded-lg bg-white p-8 shadow-xl">
                <button type="button" onclick="document.getElementById('accessCodeModal').classList.add('hidden')" class="absolute right-4 top-4 text-gray-400 transition-colors hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <div class="mb-6 text-center">
                    <h3 class="text-2xl font-bold text-3hcig-blue-dark">Accès restreint</h3>
                    <p class="mt-2 text-gray-600">Veuillez saisir le code d'accès pour continuer</p>
                </div>

                <form action="{{ route('verify.registration.access') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="access_code" class="mb-2 block text-sm font-medium text-gray-700">Code d'accès</label>
                        <input
                            id="access_code"
                            type="password"
                            name="access_code"
                            class="block w-full rounded-md border-2 border-gray-300 bg-white px-4 py-3 text-gray-900 transition-colors focus:border-3hcig-green focus:outline-none focus:ring-1 focus:ring-3hcig-green"
                            placeholder="Entrez votre code d'accès"
                            required
                        >
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            onclick="document.getElementById('accessCodeModal').classList.add('hidden')"
                            class="rounded-lg bg-gray-200 px-6 py-3 font-bold text-gray-800 transition-colors duration-300 hover:bg-gray-300">
                            Annuler
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-3hcig-green px-6 py-3 font-bold text-white transition-colors duration-300 hover:bg-3hcig-green-light">
                            Valider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@if(session('error'))
<div id="errorAlert" class="fixed top-4 right-4 bg-red-600 text-white px-6 py-4 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p>{{ session('error') }}</p>
    </div>
</div>
<script>
    setTimeout(function() {
        document.getElementById('errorAlert').style.opacity = '0';
        setTimeout(function() {
            document.getElementById('errorAlert').style.display = 'none';
        }, 300);
    }, 5000);
</script>
@endif
@endsection
