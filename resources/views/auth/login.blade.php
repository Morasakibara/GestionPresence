@if (Auth::check())
@extends('layouts.app')
@endif

@section('content')
<div class="flex min-h-screen">
  <!-- Bloc gauche avec image en arrière-plan et texte d'accueil -->
  <div class="hidden md:flex md:w-1/2 relative bg-gray-900 overflow-hidden">
    <!-- Image en arrière-plan -->
    <div class="absolute inset-0 z-0">
      <img src="{{ asset('storage/avatars/loginBackground.jpg') }}" alt="Background" class="w-full h-full object-cover">
    </div>
    
    <!-- Couche noir transparente -->
    <div class="absolute inset-0 bg-black bg-opacity-20 z-10"></div>
    
    <!-- Contenu du bloc gauche -->
    <div class="relative z-20 flex flex-col justify-center items-start px-12 w-full h-full">            <img class="h-24 mb-8" src="{{ asset('storage/avatars/logo-3HCIG.png') }}" alt="Le Pharaon">
      
      <!-- Texte d'accueil animé -->
      <h1 class="text-4xl font-bold mb-4 text-white text-shadow animate-fade-in">Bienvenue au Pharaon</h1>
      <p class="text-xl text-white text-shadow mb-6 max-w-md animate-slide-up">
        Connectez-vous pour accéder à votre espace personnel et gérer vos activités au sein de notre coopérative.
      </p>
      <div class="animate-bounce-slow mt-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
      </div>
    </div>
  </div>

  <!-- Bloc droit avec formulaire de connexion -->
  <div class="w-full md:w-1/2 flex flex-col justify-center bg-gray-100 px-6 py-12 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
      <div class="md:hidden">
        <img class="mx-auto h-20 w-auto" src="{{ asset('storage/avatars/logo-3HCIG.png') }}" alt="Le Pharaon">
      </div>
      <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-3hcig-blue-dark">Connexion à votre compte</h2>
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

      <form class="space-y-6" method="POST" action="{{ route('login') }}">
        @csrf

        <div>
          <label for="email" class="block text-sm font-medium leading-6 text-gray-700">Adresse email</label>
          <div class="mt-2">
            <input id="email" type="email" name="email" autocomplete="email" required
                  class="block w-full rounded-md bg-white px-3 py-2 text-gray-900 border-2 border-gray-300 placeholder:text-gray-400 focus:border-3hcig-blue focus:outline-none focus:ring-1 focus:ring-3hcig-blue sm:text-sm sm:leading-6 transition-colors 
                  @error('email') border-red-600 bg-red-600/5 @enderror"
                  value="{{ old('email') }}">
          </div>
          @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label for="password" class="block text-sm font-medium leading-6 text-gray-700">Mot de passe</label>
          </div>
          <div class="mt-2 relative">
            <input id="password" type="password" name="password" autocomplete="current-password" required
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
          <button type="submit"
                  class="flex w-full justify-center rounded-md bg-3hcig-blue px-3 py-2 text-sm font-semibold leading-6 text-white shadow-md hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-3hcig-blue transition-colors duration-300">
            Se connecter
          </button>
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
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);

        // Change the eye icon
        if (type === 'text') {
            this.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 opacity-50 hover:opacity-100 transition-opacity duration-300"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            `;
        } else {
            this.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 opacity-50 hover:opacity-100 transition-opacity duration-300"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            `;
        }
    });
});
</script>
@endsection