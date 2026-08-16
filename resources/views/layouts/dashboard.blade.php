<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Le Pharaon') }}</title>

    <!-- Styles -->
    @vite('resources/css/app.css')
</head>
<body class="relative h-full antialiased">
    <!-- Arrière-plan avec overlay spécifique au rôle -->
    <div class="fixed inset-0 z-[-10]">
        @if(Auth::user()->role === 'Administrateur')
            <img src="{{ asset('storage/avatars/adminBackground.jpg') }}" alt="Admin Background" class="absolute inset-0 object-cover w-full h-full">
        @elseif(Auth::user()->role === 'Superviseur')
            <img src="{{ asset('storage/avatars/supBackground.jpg') }}" alt="Superviseur Background" class="absolute inset-0 object-cover w-full h-full">
        @else
            <img src="{{ asset('storage/avatars/userBackground.jpg') }}" alt="Employé Background" class="absolute inset-0 object-cover w-full h-full">
        @endif
        <div class="absolute inset-0 bg-black bg-opacity-60"></div>
    </div>

    <div class="min-h-full relative z-[1]">
        <nav class="bg-3hcig-blue-dark">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <img class="w-auto h-20" src="{{ asset('/storage/avatars/logo-3HCIG.png') }}" alt="Le Pharaon">
                        </div>
                        <div class="hidden md:block">
                            <div class="flex items-baseline ml-10 space-x-4">
                                @yield('navigation')
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="flex items-center ml-4 md:ml-6">
                            <!-- Profile dropdown -->
                            <div class="relative ml-3">
                                <div>
                                    <button type="button" class="relative flex items-center max-w-xs text-sm rounded-full profile-button bg-3hcig-blue-dark focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-3hcig-blue focus:outline-none" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                        <span class="absolute -inset-1.5"></span>
                                        <span class="sr-only">Open user menu</span>
                                        <img class="w-8 h-8 rounded-full" src="{{ $user->avatar ?? asset('storage/avatars/default.png') }}" alt="{{ auth()->user()->nom }}">
                                    </button>
                                </div>

                                <!-- Dropdown menu -->
                                <div id="profile-dropdown" class="absolute right-0 z-10 hidden w-48 py-1 mt-2 origin-top-right bg-white rounded-md shadow-lg profile-dropdown ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">

                                    @if(Auth::check() && Auth::user()->role === 'Administrateur')
                                    <a href="#" onclick="openProfileModal()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="user-profile-button">
                                        Votre Profil
                                    </a>

                                    <!-- Modal (hidden par défaut) -->
                                    <div id="profile-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                            <!-- Background overlay, show/hide basé sur l'état du modal -->
                                            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>

                                            <!-- Centered modal panel -->
                                            <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                                                <!-- Vue par défaut (affichage des informations) -->
                                                <div id="profile-view" class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                                                    <div class="sm:flex sm:items-start">
                                                        <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                                                            <h3 class="text-lg font-medium leading-6 text-3hcig-blue-dark" id="modal-title">
                                                                Votre Profil
                                                            </h3>

                                                            <div class="flex flex-col items-center mt-4">
                                                                <!-- Photo de profil -->
                                                                <div class="w-24 h-24 mb-4 overflow-hidden border-4 rounded-full border-3hcig-blue-light">
                                                                    <img id="profile-avatar-display" src="{{ auth()->user()->avatar ? asset('storage/avatars/'.auth()->user()->avatar) : asset('storage/avatars/default.png') }}" alt="Avatar" class="object-cover w-full h-full">
                                                                </div>

                                                                <!-- Informations de l'administrateur -->
                                                                <div class="w-full pt-2 space-y-3">
                                                                    <div class="flex justify-between p-3 rounded-md bg-gray-50">
                                                                        <span class="text-sm font-medium text-gray-500">Nom:</span>
                                                                        <span id="profile-name-display" class="text-sm font-semibold text-gray-900">{{ auth()->user()->nom }}</span>
                                                                    </div>

                                                                    <div class="flex justify-between p-3 rounded-md bg-gray-50">
                                                                        <span class="text-sm font-medium text-gray-500">Email:</span>
                                                                        <span id="profile-email-display" class="text-sm font-semibold text-gray-900">{{ auth()->user()->email }}</span>
                                                                    </div>

                                                                    <div class="flex justify-between p-3 rounded-md bg-gray-50">
                                                                        <span class="text-sm font-medium text-gray-500">Rôle:</span>
                                                                        <span class="rounded-full bg-3hcig-blue px-2 py-0.5 text-xs font-medium text-white">
                                                                            Administrateur
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Vue d'édition (cachée par défaut) -->
                                                <div id="profile-edit" class="hidden px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                                                    <div class="sm:flex sm:items-start">
                                                        <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                                                            <h3 class="text-lg font-medium leading-6 text-3hcig-blue-dark" id="modal-title">
                                                                Modifier votre profil
                                                            </h3>

                                                            <form id="profile-edit-form" class="mt-4" enctype="multipart/form-data">
                                                                @csrf
                                                                @method('PUT')

                                                                <div class="flex flex-col items-center">
                                                                    <!-- Preview de la photo de profil -->
                                                                    <div class="w-24 h-24 mb-4 overflow-hidden border-4 rounded-full border-3hcig-blue-light">
                                                                        <img id="profile-avatar-preview" src="{{ auth()->user()->avatar ? asset('storage/avatars/'.auth()->user()->avatar) : asset('storage/avatars/default.png') }}" alt="Avatar" class="object-cover w-full h-full">
                                                                    </div>

                                                                    <!-- Champ pour l'upload d'avatar -->
                                                                    <div class="w-full mb-4">
                                                                        <label for="avatar" class="block text-sm font-medium text-gray-700">Changer la photo</label>
                                                                        <input type="file" id="avatar" name="avatar" class="block w-full px-3 py-2 mt-1 text-sm border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue">
                                                                    </div>
                                                                </div>

                                                                <div class="space-y-4">
                                                                    <div>
                                                                        <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                                                                        <input type="text" id="nom" name="nom" value="{{ auth()->user()->nom }}" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue sm:text-sm">
                                                                    </div>

                                                                    <div>
                                                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                                                        <input type="email" id="email" name="email" value="{{ auth()->user()->email }}" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue sm:text-sm">
                                                                    </div>

                                                                    <div>
                                                                        <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                                                                        <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue sm:text-sm">
                                                                    </div>

                                                                    <div>
                                                                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmation mot de passe</label>
                                                                        <input type="password" id="password_confirmation" name="password_confirmation" class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue sm:text-sm">
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Messages de feedback (cachés par défaut) -->
                                                <div id="profile-feedback" class="hidden p-4 m-4 rounded-md bg-3hcig-green-light/20 text-3hcig-green-dark">
                                                    <div class="flex">
                                                        <div class="flex-shrink-0">
                                                            <svg class="w-5 h-5 text-3hcig-green" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                        <div class="ml-3">
                                                            <p class="text-sm font-medium text-3hcig-green-dark" id="feedback-message"></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Footer du modal -->
                                                <div class="px-4 py-3 bg-gray-50 sm:flex sm:flex-row-reverse sm:px-6">
                                                    <!-- Bouttons pour vue d'affichage -->
                                                    <div id="view-buttons">
                                                        <button type="button" onclick="showEditMode()" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm bg-3hcig-blue hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                                                            Éditer
                                                        </button>
                                                        <button type="button" onclick="closeProfileModal()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                            Fermer
                                                        </button>
                                                    </div>

                                                    <!-- Bouttons pour vue d'édition -->
                                                    <div id="edit-buttons" class="hidden">
                                                        <button type="button" onclick="saveProfileChanges()" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm bg-3hcig-green hover:bg-3hcig-green-light focus:outline-none focus:ring-2 focus:ring-3hcig-green focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                                                            Enregistrer
                                                        </button>
                                                        <button type="button" onclick="cancelEdit()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                            Annuler
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <script>
                                    // Ouvrir le modal
                                    function openProfileModal() {
                                        document.getElementById('profile-modal').classList.remove('hidden');
                                        document.body.classList.add('overflow-hidden'); // Empêcher le scrolling
                                    }

                                    // Fermer le modal
                                    function closeProfileModal() {
                                        document.getElementById('profile-modal').classList.add('hidden');
                                        document.body.classList.remove('overflow-hidden');
                                        // Cacher le message de feedback s'il est visible
                                        document.getElementById('profile-feedback').classList.add('hidden');
                                    }

                                    // Afficher le mode édition
                                    function showEditMode() {
                                        document.getElementById('profile-view').classList.add('hidden');
                                        document.getElementById('profile-edit').classList.remove('hidden');
                                        document.getElementById('view-buttons').classList.add('hidden');
                                        document.getElementById('edit-buttons').classList.remove('hidden');
                                        document.getElementById('profile-feedback').classList.add('hidden');
                                    }

                                    // Annuler l'édition et revenir à la vue d'affichage
                                    function cancelEdit() {
                                        document.getElementById('profile-edit').classList.add('hidden');
                                        document.getElementById('profile-view').classList.remove('hidden');
                                        document.getElementById('edit-buttons').classList.add('hidden');
                                        document.getElementById('view-buttons').classList.remove('hidden');

                                        // Réinitialiser le formulaire
                                        document.getElementById('profile-edit-form').reset();
                                        // Réinitialiser la prévisualisation de l'avatar
                                        document.getElementById('profile-avatar-preview').src = document.getElementById('profile-avatar-display').src;
                                    }

                                    // Enregistrer les modifications
                                    function saveProfileChanges() {
                                        const form = document.getElementById('profile-edit-form');
                                        const formData = new FormData(form);

                                        // Ajouter une entête CSRF pour Laravel
                                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                                        fetch('{{ route("admin.updateProfile") }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': csrfToken
                                            },
                                            body: formData
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                // Mettre à jour l'affichage
                                                document.getElementById('profile-name-display').textContent = data.user.nom;
                                                document.getElementById('profile-email-display').textContent = data.user.email;

                                                // Mettre à jour l'avatar si un nouveau est téléchargé
                                                if (data.user.avatar) {
                                                    const avatarUrl = '{{ asset("storage/avatars/") }}/' + data.user.avatar;
                                                    document.getElementById('profile-avatar-display').src = avatarUrl;

                                                    // Mettre à jour l'avatar dans la barre de navigation
                                                    const navAvatar = document.querySelector('.nav-profile-img') || document.querySelector('.profile-image');
                                                    if (navAvatar) navAvatar.src = avatarUrl;
                                                }

                                                // Afficher le message de succès
                                                const feedbackDiv = document.getElementById('profile-feedback');
                                                document.getElementById('feedback-message').textContent = "Profil mis à jour avec succès!";
                                                feedbackDiv.classList.remove('hidden');

                                                // Revenir à la vue d'affichage
                                                document.getElementById('profile-edit').classList.add('hidden');
                                                document.getElementById('profile-view').classList.remove('hidden');
                                                document.getElementById('edit-buttons').classList.add('hidden');
                                                document.getElementById('view-buttons').classList.remove('hidden');
                                            } else {
                                                // Afficher les erreurs
                                                const feedbackDiv = document.getElementById('profile-feedback');
                                                feedbackDiv.classList.remove('bg-3hcig-green-light/20', 'text-3hcig-green-dark');
                                                feedbackDiv.classList.add('bg-red-50', 'text-red-800');

                                                let errorMessage = 'Erreur lors de la mise à jour:';
                                                if (data.errors) {
                                                    errorMessage += '<ul class="pl-5 mt-1 list-disc">';
                                                    for (const error in data.errors) {
                                                        errorMessage += `<li>${data.errors[error]}</li>`;
                                                    }
                                                    errorMessage += '</ul>';
                                                } else {
                                                    errorMessage += ' ' + (data.message || 'Veuillez réessayer.');
                                                }

                                                document.getElementById('feedback-message').innerHTML = errorMessage;
                                                feedbackDiv.classList.remove('hidden');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Erreur:', error);

                                            // Afficher un message d'erreur générique
                                            const feedbackDiv = document.getElementById('profile-feedback');
                                            feedbackDiv.classList.remove('bg-3hcig-green-light/20', 'text-3hcig-green-dark');
                                            feedbackDiv.classList.add('bg-red-50', 'text-red-800');
                                            document.getElementById('feedback-message').textContent = "Une erreur est survenue lors de la mise à jour du profil.";
                                            feedbackDiv.classList.remove('hidden');
                                        });
                                    }

                                    // Prévisualisation de l'avatar
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const avatarInput = document.getElementById('avatar');
                                        const avatarPreview = document.getElementById('profile-avatar-preview');

                                        if (avatarInput && avatarPreview) {
                                            avatarInput.addEventListener('change', function() {
                                                if (this.files && this.files[0]) {
                                                    const reader = new FileReader();

                                                    reader.onload = function(e) {
                                                        avatarPreview.src = e.target.result;
                                                    }

                                                    reader.readAsDataURL(this.files[0]);
                                                }
                                            });
                                        }

                                        // Fermer le modal si on clique en dehors
                                        window.addEventListener('click', function(event) {
                                            const modal = document.getElementById('profile-modal');
                                            if (event.target === modal) {
                                                closeProfileModal();
                                            }
                                        });

                                        // Fermer le modal avec la touche Échap
                                        document.addEventListener('keydown', function(event) {
                                            if (event.key === 'Escape' && !document.getElementById('profile-modal').classList.contains('hidden')) {
                                                closeProfileModal();
                                            }
                                        });
                                    });
                                    </script>
                                    @endif
                                    @if(Auth::check() && Auth::user()->role === 'Superviseur')
                                        <a href="{{ route('role.switch') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Changer de rôle</a>
                                    @endif
                                    <a href="{{ route('logouts') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Déconnexion</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex -mr-2 md:hidden">
                        <!-- Mobile menu button -->
                        <button type="button" id="mobile-menu-button" class="relative inline-flex items-center justify-center p-2 text-gray-400 rounded-md mobile-menu-button bg-3hcig-blue-dark hover:bg-3hcig-blue hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-3hcig-blue-dark" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="absolute -inset-0.5"></span>
                            <span class="sr-only">Open main menu</span>
                            <!-- Menu open: "hidden", Menu closed: "block" -->
                            <svg class="block w-6 h-6 menu-icon-closed" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <!-- Menu open: "block", Menu closed: "hidden" -->
                            <svg class="hidden w-6 h-6 menu-icon-open" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state. -->
            <div class="hidden mobile-menu md:hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    @yield('mobile-navigation')
                </div>
                <div class="pt-4 pb-3 border-t border-3hcig-blue">
                    <div class="flex items-center px-5">
                        <div class="shrink-0">
                            <img class="w-10 h-10 rounded-full" src="{{ auth()->user()->avatar ?? asset('storage/avatars/default.png') }}" alt="{{ auth()->user()->nom }}">
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-white">{{ auth()->user()->nom }}</div>
                            <div class="text-sm font-medium text-gray-400">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="px-2 mt-3 space-y-1">
                        <a href="#" class="block px-3 py-2 text-base font-medium text-gray-400 rounded-md hover:bg-3hcig-blue hover:text-white">Votre Profil</a>
                        @if(Auth::check() && Auth::user()->role === 'Superviseur')
                            <a href="{{ route('role.switch') }}" class="block px-3 py-2 text-base font-medium text-gray-400 rounded-md hover:bg-3hcig-blue hover:text-white">Changer de rôle</a>
                        @endif
                        <a href="{{ route('logouts') }}" class="block px-3 py-2 text-base font-medium text-gray-400 rounded-md hover:bg-3hcig-blue hover:text-white">Déconnexion</a>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow-sm bg-opacity-90">
            <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-3hcig-blue-dark">@yield('header')</h1>
            </div>
        </header>

        <main>
            <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                @if (session('success'))
                <div class="p-4 mb-6 rounded-md bg-3hcig-green-light/20 text-3hcig-green-dark">
                    {{ session('success') }}
                </div>
                @endif

                @if (session('error'))
                <div class="p-4 mb-6 text-red-600 rounded-md bg-red-50">
                    {{ session('error') }}
                </div>
                @endif

                <div class="p-6 bg-white rounded-lg shadow-sm bg-opacity-90">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle user dropdown menu
        const profileButton = document.querySelector('.profile-button');
        const profileDropdown = document.querySelector('.profile-dropdown');

        if (profileButton && profileDropdown) {
            profileButton.addEventListener('click', function() {
                profileDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!profileButton.contains(event.target) && !profileDropdown.contains(event.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });
        }

        // Mobile menu toggle
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenu = document.querySelector('.mobile-menu');
        const menuIconClosed = document.querySelector('.menu-icon-closed');
        const menuIconOpen = document.querySelector('.menu-icon-open');

        if (mobileMenuButton && mobileMenu && menuIconClosed && menuIconOpen) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                menuIconClosed.classList.toggle('hidden');
                menuIconOpen.classList.toggle('hidden');
            });
        }
    });
    </script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
