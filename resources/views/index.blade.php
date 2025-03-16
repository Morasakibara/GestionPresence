@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header avec le logo -->
    <header class="py-6 bg-white shadow-md z-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 flex justify-center">
            <img class="h-20" src="{{ asset('storage/avatars/logo-3HCIG.png') }}" alt="3HCIG COOP-CA">
        </div>
    </header>

    <!-- Section principale avec image de fond -->
    <main class="flex-grow flex flex-col">
        <div class="relative flex-grow">
            <!-- Image de fond avec overlay -->
            <div class="absolute inset-0 z-0">
                <img src="{{ asset('storage/avatars/loginBackground.jpg') }}" alt="Background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-white bg-opacity-80"></div>
            </div>

            <!-- Contenu -->
            <div class="relative z-10 container mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Titre et sous-titre -->
                <div class="text-center mb-16">
                    <h1 class="text-4xl font-bold text-3hcig-blue-dark mb-4">Bienvenue sur l'application de gestion de présence 3HCIG</h1>
                    <p class="text-xl text-gray-700 max-w-3xl mx-auto">
                        Suivez et gérez facilement les présences, générez des rapports détaillés et optimisez le suivi de vos collaborateurs au sein de notre coopérative.
                    </p>
                </div>

                <!-- Cartes pour connexion et inscription -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto">
    <!-- Carte connexion -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-xl hover:scale-105">
        <div class="h-64 relative overflow-hidden">
            <!-- Image d'arrière-plan avec couche noire transparente -->
            <img src="{{ asset('storage/avatars/loginBackground.jpg') }}" alt="Connexion" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <h2 class="text-4xl font-bold text-white z-10">Espace connexion</h2>
            </div>
        </div>
        <div class="p-8">
            <p class="text-gray-700 mb-8">
                Déjà membre ? Connectez-vous pour accéder à votre espace personnel et gérer vos présences ou celles de votre équipe.
            </p>
            <a href="{{ route('login') }}" class="block w-full bg-3hcig-blue hover:bg-3hcig-blue-light text-white font-bold py-4 px-6 rounded-lg text-center transition-colors duration-300">
                Se connecter
            </a>
        </div>
    </div>

    <!-- Carte inscription -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 transition-all duration-300 hover:shadow-xl hover:scale-105">
        <div class="h-64 relative overflow-hidden">
            <!-- Image d'arrière-plan avec couche noire transparente -->
            <img src="{{ asset('storage/avatars/regiterBackground.jpg') }}" alt="Inscription" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <h2 class="text-4xl font-bold text-white z-10">Nouvel utilisateur</h2>
            </div>
        </div>
        <div class="p-8">
            <p class="text-gray-700 mb-8">
                Nouveau membre ? Rejoignez notre coopérative pour bénéficier de notre outil de gestion de présence. Un code d'accès est requis.
            </p>
            <button
                onclick="document.getElementById('accessCodeModal').classList.remove('hidden')"
                class="block w-full bg-3hcig-green hover:bg-3hcig-green-light text-white font-bold py-4 px-6 rounded-lg text-center transition-colors duration-300">
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
            <p class="text-gray-600">&copy; {{ date('Y') }} 3HCIG COOP-CA. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Modal pour code d'accès -->
    <div id="accessCodeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full mx-4 relative">
            <button type="button" onclick="document.getElementById('accessCodeModal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div class="text-center mb-6">
                <h3 class="text-2xl font-bold text-3hcig-blue-dark">Accès restreint</h3>
                <p class="text-gray-600 mt-2">Veuillez saisir le code d'accès pour continuer</p>
            </div>

            <form action="{{ route('verify.registration.access') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label for="access_code" class="block text-sm font-medium text-gray-700 mb-2">Code d'accès</label>
                    <input
                        id="access_code"
                        type="password"
                        name="access_code"
                        class="block w-full rounded-md bg-white px-4 py-3 text-gray-900 border-2 border-gray-300 focus:border-3hcig-green focus:outline-none focus:ring-1 focus:ring-3hcig-green transition-colors"
                        placeholder="Entrez votre code d'accès"
                        required
                    >
                </div>
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        onclick="document.getElementById('accessCodeModal').classList.add('hidden')"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors duration-300">
                        Annuler
                    </button>
                    <button
                        type="submit"
                        class="bg-3hcig-green hover:bg-3hcig-green-light text-white font-bold py-3 px-6 rounded-lg transition-colors duration-300">
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
