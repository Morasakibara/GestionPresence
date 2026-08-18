@extends('layouts.app')

@section('title', 'Ajouter Employé')

@section('content')
<div class="max-w-md px-4 py-8 mx-auto sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-[#FBF3E6]">
            <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        </div>
        <h1 class="page-heading-title">Ajouter Employé</h1>
        <p class="mt-2 text-sm text-gray-500">Créez un nouveau compte employé ou superviseur</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success mb-6">
        <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="p-6 overflow-hidden bg-white rounded-2xl border border-gray-200/70 shadow-card">
        <form action="{{ route('admin.storeEmployee') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom:</label>
                <div class="mt-1">
                    <input type="text" id="nom" name="nom" required
                           class="input-field">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <div class="mt-1">
                    <input type="email" id="email" name="email" required
                           class="input-field">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Mot de Passe:</label>
                <div class="mt-1">
                    <input type="password" id="password" name="password" required
                           class="input-field">
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer Mot de Passe:</label>
                <div class="mt-1">
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="input-field">
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
                           class="input-field">
                </div>
            </div>

            <div id="typeSuperviseurField" class="hidden">
                <label for="type_superviseur" class="block text-sm font-medium text-gray-700">Type de superviseur:</label>
                <div class="mt-1">
                    <select id="type_superviseur" name="type_superviseur" class="input-field">
                        <option value="">— Sélectionner un type —</option>
                        <option value="directrice">Directrice (Caisse & Finances)</option>
                        <option value="secretaire">Secrétaire (Services Photo)</option>
                        <option value="gestionnaire_stock">Gestionnaire de Stock</option>
                    </select>
                </div>
                <p class="mt-1 text-xs text-gray-400">Choisissez le module adapté au poste de ce superviseur.</p>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-gold w-full">
                    <svg class="-ml-1 mr-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Enregistrer l'employé
                </button>
            </div>
        </form>

        @if ($errors->any())
        <div class="alert alert-danger mt-6">
            <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
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
        var typeField = document.getElementById("typeSuperviseurField");

        if (superviseurCheckbox.checked) {
            equipeField.classList.remove("hidden");
            typeField.classList.remove("hidden");
        } else {
            equipeField.classList.add("hidden");
            typeField.classList.add("hidden");
        }
    }
</script>
@endsection
