@extends('layouts.dashboard')

@section('title', 'Ajouter Employé')
@section('breadcrumb', 'Ajouter Employé')

@section('header')
    <div>
        <span>Ajouter Employé</span>
        <p class="console-subtitle">Créez un compte employé ou superviseur et rattachez-le à son module.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.showEmployeeList') }}" class="btn-secondary">
            <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            Liste des employés
        </a>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="console-card xl:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Informations du compte</h2>
                    <p class="console-card-sub">Tous les champs sont obligatoires hors mention contraire.</p>
                </div>
                <span class="badge badge-gold">Nouveau</span>
            </div>

            <div class="console-card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-semibold">Il y a eu {{ count($errors) }} erreur(s) dans votre soumission</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.storeEmployee') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="nom" class="input-label">Nom</label>
                            <div class="mt-1">
                                <input type="text" id="nom" name="nom" required autocomplete="name" class="input-field">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="email" class="input-label">Email</label>
                            <div class="mt-1">
                                <input type="email" id="email" name="email" required autocomplete="email" class="input-field">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="input-label">Mot de passe</label>
                            <div class="mt-1">
                                <input type="password" id="password" name="password" required autocomplete="new-password" class="input-field">
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="input-label">Confirmer le mot de passe</label>
                            <div class="mt-1">
                                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" class="input-field">
                            </div>
                        </div>
                    </div>

                    <fieldset class="rounded-xl border border-gray-200/70 bg-[#FAFAF9] p-4">
                        <legend class="px-1 text-xs font-semibold uppercase tracking-[0.12em] text-gray-500">Rôle</legend>
                        <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:gap-8">
                            <label for="role_employer" class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" id="role_employer" name="role[]" value="Employer" onclick="toggleEquipeField()"
                                       class="h-4 w-4 rounded border-gray-300 text-pharaoh-gold focus:ring-pharaoh-gold">
                                Employé
                            </label>
                            <label for="role_superviseur" class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" id="role_superviseur" name="role[]" value="Superviseur" onclick="toggleEquipeField()"
                                       class="h-4 w-4 rounded border-gray-300 text-pharaoh-gold focus:ring-pharaoh-gold">
                                Superviseur
                            </label>
                        </div>
                    </fieldset>

                    <div id="equipeField" class="hidden">
                        <label for="equipe" class="input-label">Nom de l'équipe</label>
                        <div class="mt-1">
                            <input type="text" id="equipe" name="equipe" class="input-field">
                        </div>
                    </div>

                    <div id="typeSuperviseurField" class="hidden">
                        <label for="type_superviseur" class="input-label">Type de superviseur</label>
                        <div class="mt-1">
                            <select id="type_superviseur" name="type_superviseur" class="select-field">
                                <option value="">— Sélectionner un type —</option>
                                <option value="superviseur_a">Superviseur A (Superviseur classique)</option>
                                <option value="directrice">Directrice (Caisse &amp; Finances)</option>
                                <option value="secretaire">Secrétaire (Services Photo)</option>
                                <option value="gestionnaire_stock">Gestionnaire de Stock</option>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Choisissez le module adapté au poste de ce superviseur.</p>
                    </div>

                    <div class="flex flex-col gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end">
                        <button type="reset" class="btn-secondary order-2 sm:order-1">Réinitialiser</button>
                        <button type="submit" class="btn-gold order-1 sm:order-2">
                            <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Enregistrer l'employé
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="space-y-5">
            <div class="console-card">
                <div class="console-card-body">
                    <span class="console-quick-icon" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </span>
                    <h2 class="mt-3 console-card-title">Créer un accès</h2>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600">
                        <li class="console-panel-row">
                            <span>Employé</span>
                            <span class="badge badge-info">Espace employé</span>
                        </li>
                        <li class="console-panel-row">
                            <span>Superviseur</span>
                            <span class="badge badge-gold">Pilotage</span>
                        </li>
                        <li class="console-panel-row">
                            <span>Mot de passe</span>
                            <span class="text-xs text-gray-500">Haché automatiquement</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="console-card">
                <div class="console-card-body">
                    <h2 class="console-card-title">Après la création</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Le nouvel utilisateur apparaît immédiatement dans la liste des employés. Il peut se connecter
                        avec son email et son mot de passe, puis définir son code PIN de récupération.
                    </p>
                    <a href="{{ route('admin.auditLogs') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-[#885910] transition-colors hover:text-pharaoh-gold">
                        Consulter le journal d'audit
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleEquipeField() {
            var superviseurCheckbox = document.getElementById('role_superviseur');
            var equipeField = document.getElementById('equipeField');
            var typeField = document.getElementById('typeSuperviseurField');

            if (!superviseurCheckbox || !equipeField || !typeField) return;

            if (superviseurCheckbox.checked) {
                equipeField.classList.remove('hidden');
                typeField.classList.remove('hidden');
            } else {
                equipeField.classList.add('hidden');
                typeField.classList.add('hidden');
            }
        }
    </script>
@endpush
