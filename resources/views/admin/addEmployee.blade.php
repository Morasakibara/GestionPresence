@extends('layouts.app')

@section('title', 'Ajouter Employé')

@section('content')
<div class="max-w-md px-4 py-8 mx-auto sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-[#080808]">Ajouter Employé</h1>
        <p class="mt-2 text-sm text-gray-600">Créez un nouveau compte employé ou superviseur</p>
    </div>

    @if(session('success'))
    <div class="p-4 mb-6 rounded-md bg-3hcig-green-light/20 text-3hcig-green-dark">
        {{ session('success') }}
    </div>
    @endif

    <div class="p-6 overflow-hidden bg-white rounded-2xl border border-gray-200/70 shadow-card">
        <form action="{{ route('admin.storeEmployee') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom:</label>
                <div class="mt-1">
                    <input type="text" id="nom" name="nom" required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <div class="mt-1">
                    <input type="email" id="email" name="email" required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de Passe:</label>
                <div class="mt-1">
                    <input type="password" id="password" name="password" required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer Mot de Passe:</label>
                <div class="mt-1">
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Rôle:</label>
                <div class="flex mt-2 space-x-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="role_employer" name="role[]" value="Employer" onclick="toggleEquipeField()"
                               class="w-4 h-4 border-gray-300 rounded text-3hcig-blue focus:ring-3hcig-blue">
                        <label for="role_employer" class="ml-2 text-sm text-gray-700">Employé</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="role_superviseur" name="role[]" value="Superviseur" onclick="toggleEquipeField()"
                               class="w-4 h-4 border-gray-300 rounded text-3hcig-blue focus:ring-3hcig-blue">
                        <label for="role_superviseur" class="ml-2 text-sm text-gray-700">Superviseur</label>
                    </div>
                </div>
            </div>

            <div id="equipeField" class="hidden">
                <label for="equipe" class="block text-sm font-medium text-gray-700">Nom de l'équipe:</label>
                <div class="mt-1">
                    <input type="text" id="equipe" name="equipe"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white rounded-md shadow-sm bg-3hcig-blue hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                    Enregistrer l'employé
                </button>
            </div>
        </form>

        @if ($errors->any())
        <div class="p-4 mt-6 rounded-md bg-red-50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Il y a eu {{ count($errors) }} erreur(s) dans votre soumission</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="pl-5 space-y-1 list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    function toggleEquipeField() {
        var superviseurCheckbox = document.getElementById("role_superviseur");
        var equipeField = document.getElementById("equipeField");

        if (superviseurCheckbox.checked) {
            equipeField.classList.remove("hidden");
        } else {
            equipeField.classList.add("hidden");
        }
    }
</script>
@endsection
