@extends('layouts.app')

@section('content')
<div class="flex min-h-screen">
  <!-- Bloc gauche avec image en arrière-plan et texte d'accueil -->
  <div class="hidden md:flex md:w-1/2 relative bg-[#080808] overflow-hidden">
    <!-- Image en arrière-plan -->
    <div class="absolute inset-0 z-0">
      <img src="{{ asset('storage/avatars/regiterBackground.jpg') }}" alt="Background" class="w-full h-full object-cover">
    </div>
    
    <!-- Couche noir + dégradé or -->
    <div class="absolute inset-0 bg-gradient-to-br from-black/85 via-black/70 to-black/40 z-10"></div>
    <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-pharaoh-gold/20 blur-3xl z-10"></div>
    
    <!-- Contenu du bloc gauche -->
    <div class="relative z-20 flex flex-col justify-center items-start px-12 w-full h-full">
      <img class="h-28 mb-10 rounded-2xl bg-white/10 p-3 shadow-gold backdrop-blur-md" src="{{ asset('storage/avatars/logo-pharaon.png') }}" alt="Le Pharaon">
      
      <!-- Texte d'accueil animé -->
      <div class="inline-flex items-center gap-2 mb-4 rounded-full border border-pharaoh-gold/40 bg-pharaoh-gold/10 px-4 py-1.5 text-sm font-medium text-pharaoh-gold-light animate-fade-in">
        <span class="h-2 w-2 rounded-full bg-pharaoh-gold-bright"></span>
        Création de compte
      </div>
      <h1 class="text-4xl font-extrabold leading-tight mb-4 text-white text-shadow animate-fade-in">Rejoignez <span class="text-pharaoh-gold-bright">Le Pharaon</span></h1>
      <p class="text-lg text-gray-200 mb-6 max-w-md animate-slide-up">
        Créez votre compte pour faire partie de notre structure et contribuer à nos projets communs.
      </p>
      <div class="animate-bounce-slow mt-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-pharaoh-gold-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
      </div>
    </div>
  </div>

  <!-- Bloc droit avec formulaire d'inscription -->
  <div class="w-full md:w-1/2 flex flex-col justify-center bg-[#F8F8F8] px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <div class="md:hidden">
        <img class="mx-auto h-20 w-auto" src="{{ asset('storage/avatars/logo-pharaon.png') }}" alt="Le Pharaon">
      </div>
      <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-[#080808]">Création de compte</h2>
      <p class="mt-2 text-center text-sm text-gray-500">Un code d'accès est requis pour continuer</p>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
      @if ($errors->any())
      <div class="bg-red-600/10 border-2 border-red-600 text-red-600 px-4 py-3 rounded-md mb-6 shadow-md">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

      <form class="space-y-6" method="POST" action="{{ route('register') }}">
        @csrf

        <div>
          <label for="name" class="block text-sm font-medium leading-6 text-gray-700">Nom</label>
          <div class="mt-2">
            <input id="name" type="text" name="name" required autofocus 
                  value="{{ old('name') }}"
                  class="block w-full rounded-md bg-white px-3 py-2 text-gray-900 border-2 border-gray-300 placeholder:text-gray-400 focus:border-3hcig-blue focus:outline-none focus:ring-1 focus:ring-3hcig-blue sm:text-sm sm:leading-6 transition-colors
                  @error('name') border-red-600 bg-red-600/5 @enderror">
          </div>
          @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="email" class="block text-sm font-medium leading-6 text-gray-700">Email</label>
          <div class="mt-2">
            <input id="email" type="email" name="email" required
                  value="{{ old('email') }}"
                  class="block w-full rounded-md bg-white px-3 py-2 text-gray-900 border-2 border-gray-300 placeholder:text-gray-400 focus:border-3hcig-blue focus:outline-none focus:ring-1 focus:ring-3hcig-blue sm:text-sm sm:leading-6 transition-colors
                  @error('email') border-red-600 bg-red-600/5 @enderror">
          </div>
          @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="password" class="block text-sm font-medium leading-6 text-gray-700">Mot de passe</label>
          <div class="mt-2 relative">
            <input id="password" type="password" name="password" required
                  class="block w-full rounded-md bg-white px-3 py-2 text-gray-900 border-2 border-gray-300 placeholder:text-gray-400 focus:border-3hcig-blue focus:outline-none focus:ring-1 focus:ring-3hcig-blue sm:text-sm sm:leading-6 transition-colors
                  @error('password') border-red-600 bg-red-600/5 @enderror">
            <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-transparent border-none cursor-pointer p-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 opacity-50 hover:opacity-100 transition-opacity duration-300"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
          @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-700">Confirmez le mot de passe</label>
          <div class="mt-2 relative">
            <input id="password_confirmation" type="password" name="password_confirmation" required
                  class="block w-full rounded-md bg-white px-3 py-2 text-gray-900 border-2 border-gray-300 placeholder:text-gray-400 focus:border-3hcig-blue focus:outline-none focus:ring-1 focus:ring-3hcig-blue sm:text-sm sm:leading-6 transition-colors
                  @error('password_confirmation') border-red-600 bg-red-600/5 @enderror">
            <button type="button" id="togglePasswordConfirmation" class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-transparent border-none cursor-pointer p-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 opacity-50 hover:opacity-100 transition-opacity duration-300"
                  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>

        <div>
          <label for="poste" class="block text-sm font-medium leading-6 text-gray-700">Poste</label>
          <div class="mt-2">
            <select name="poste" id="poste" required
                    class="block w-full rounded-md bg-white px-3 py-2 text-gray-900 border-2 border-gray-300 placeholder:text-gray-400 focus:border-3hcig-green focus:outline-none focus:ring-1 focus:ring-3hcig-green sm:text-sm sm:leading-6 appearance-none bg-no-repeat pr-10 transition-colors
                    @error('poste') border-red-600 bg-red-600/5 @enderror"
                    style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.5em 1.5em;">
              <option value="" {{ old('poste') ? '' : 'selected' }}>Sélectionnez votre poste</option>
              <option value="administrateur" {{ old('poste') == 'administrateur' ? 'selected' : '' }}>Administrateur</option>
            </select>
          </div>
          @error('poste')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <button type="submit"
                  class="btn-press flex w-full justify-center rounded-xl bg-3hcig-green px-3 py-2.5 text-sm font-semibold leading-6 text-white shadow-lg transition-colors duration-150 hover:bg-3hcig-green-light">
            S'enregistrer
          </button>
        </div>

        <div class="mt-4 text-center">
          <a href="{{ route('login') }}" class="text-sm font-semibold text-3hcig-blue hover:text-3hcig-blue-light transition-colors duration-150">
            Déjà inscrit ? Connectez-vous
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .text-shadow {
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
  }
  
  @keyframes fadeIn {
    0% { opacity: 0; }
    100% { opacity: 1; }
  }
  
  @keyframes slideUp {
    0% { transform: translateY(20px); opacity: 0; }
    100% { transform: translateY(0); opacity: 1; }
  }
  
  @keyframes bounceSlow {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(10px); }
  }
  
  .animate-fade-in {
    animation: fadeIn 1.5s ease-in-out;
  }
  
  .animate-slide-up {
    animation: slideUp 1.5s ease-out 0.5s both;
  }
  
  .animate-bounce-slow {
    animation: bounceSlow 2s infinite;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle pour le mot de passe
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        toggleIcon(this, type);
    });

    // Toggle pour la confirmation de mot de passe
    const togglePasswordConfirmation = document.querySelector('#togglePasswordConfirmation');
    const passwordConfirmation = document.querySelector('#password_confirmation');

    togglePasswordConfirmation.addEventListener('click', function() {
        const type = passwordConfirmation.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirmation.setAttribute('type', type);
        toggleIcon(this, type);
    });

    // Fonction pour changer l'icône
    function toggleIcon(element, type) {
        if (type === 'text') {
            element.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 opacity-50 hover:opacity-100 transition-opacity duration-300"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            `;
        } else {
            element.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 opacity-50 hover:opacity-100 transition-opacity duration-300"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            `;
        }
    }
});
</script>
@endsection