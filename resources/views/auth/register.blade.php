@extends('layouts.app')

@section('content')
<div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8 bg-gray-100">
  <div class="sm:mx-auto sm:w-full sm:max-w-sm">
    <img class="mx-auto h-20 w-auto" src="http://localhost:8000/storage/avatars/logo-3HCIG.png" alt="3HCIG COOP-CA">
    <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-3hcig-blue-dark">Création de compte</h2>
  </div>

  <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
    <form class="space-y-6" method="POST" action="{{ route('register') }}">
      @csrf

      <div>
        <label for="name" class="block text-sm font-medium leading-6 text-gray-700">Nom</label>
        <div class="mt-2">
          <input id="name" type="text" name="name" required autofocus value="nom"
                 class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-3hcig-blue sm:text-sm sm:leading-6">
        </div>
      </div>

      <div>
        <label for="email" class="block text-sm font-medium leading-6 text-gray-700">Email</label>
        <div class="mt-2">
          <input id="email" type="email" name="email" required
                 class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-3hcig-blue sm:text-sm sm:leading-6">
        </div>
      </div>

      <div>
        <label for="password" class="block text-sm font-medium leading-6 text-gray-700">Mot de passe</label>
        <div class="mt-2">
          <input id="password" type="password" name="password" required
                 class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-3hcig-blue sm:text-sm sm:leading-6">
        </div>
      </div>

      <div>
        <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-700">Confirmez le mot de passe</label>
        <div class="mt-2">
          <input id="password_confirmation" type="password" name="password_confirmation" required
                 class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-3hcig-blue sm:text-sm sm:leading-6">
        </div>
      </div>

      <div>
        <label for="poste" class="block text-sm font-medium leading-6 text-gray-700">Poste</label>
        <div class="mt-2">
          <select name="poste" id="poste" required
                  class="block w-full rounded-md bg-white px-3 py-1.5 text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-3hcig-blue sm:text-sm sm:leading-6 appearance-none bg-no-repeat pr-10"
                  style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E'); background-position: right 0.5rem center; background-size: 1.5em 1.5em;">
            <option value="rien">Selectionne ton poste</option>
            <option value="administrateur">administrateur</option>
          </select>
        </div>
      </div>

      <div>
        <button type="submit"
                class="flex w-full justify-center rounded-md bg-3hcig-green px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-3hcig-green-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-3hcig-green">
          S'enregistrer
        </button>
      </div>

      <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-3hcig-blue hover:text-3hcig-blue-light">
          Déjà inscrit ? Connectez-vous
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
