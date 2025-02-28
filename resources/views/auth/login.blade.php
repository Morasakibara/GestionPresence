@if (Auth::check())
@extends('layouts.app')
@endif

@section('content')
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8 bg-gray-100">
  <div class="sm:mx-auto sm:w-full sm:max-w-sm">
    <img class="mx-auto h-20 w-auto" src="{{ asset('storage/avatars/logo-3HCIG.png') }}" alt="3HCIG COOP-CA">
    <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-3hcig-blue-dark">Connexion à votre compte</h2>
  </div>

  <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
    <form class="space-y-6" method="POST" action="{{ route('login') }}">
      @csrf

      <div>
        <label for="email" class="block text-sm font-medium leading-6 text-gray-700">Adresse email</label>
        <div class="mt-2">
          <input id="email" type="email" name="email" autocomplete="email" required
                 class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-3hcig-blue sm:text-sm sm:leading-6">
        </div>
      </div>

      <div>
        <div class="flex items-center justify-between">
          <label for="password" class="block text-sm font-medium leading-6 text-gray-700">Mot de passe</label>
          
        </div>
        <div class="mt-2 relative">
          <input id="password" type="password" name="password" autocomplete="current-password" required
                 class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-3hcig-blue sm:text-sm sm:leading-6">
          <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 bg-transparent border-none cursor-pointer p-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 opacity-50 hover:opacity-100 transition-opacity duration-300"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </button>
        </div>
      </div>

      <div>
        <button type="submit"
                class="flex w-full justify-center rounded-md bg-3hcig-blue px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-3hcig-blue-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-3hcig-blue">
          Se connecter
        </button>
      </div>
    </form>

  </div>
</div>

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
