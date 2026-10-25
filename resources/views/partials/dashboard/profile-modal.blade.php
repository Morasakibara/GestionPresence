{{-- Modale de profil de l'administrateur (vue + édition), ouverte depuis le menu du compte. --}}
<div id="profile-modal" class="fixed inset-0 z-[10000] hidden overflow-y-auto" aria-labelledby="profile-modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-[#080808]/70 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

        <div class="inline-block overflow-hidden rounded-2xl bg-white text-left align-bottom shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
            <!-- Vue par défaut -->
            <div id="profile-view" class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:mt-0 sm:text-left">
                        <h3 class="section-title" id="profile-modal-title">Votre Profil</h3>

                        <div class="mt-4 flex flex-col items-center">
                            <div class="mb-4 h-24 w-24 overflow-hidden rounded-full border-4 border-pharaoh-gold-light">
                                <img id="profile-avatar-display" src="{{ auth()->user()->avatar ? asset('storage/avatars/'.auth()->user()->avatar) : asset('storage/avatars/default.png') }}" alt="Avatar" class="h-full w-full object-cover">
                            </div>

                            <div class="w-full space-y-3 pt-2">
                                <div class="flex justify-between rounded-xl bg-gray-50 p-3">
                                    <span class="text-sm font-medium text-gray-500">Nom:</span>
                                    <span id="profile-name-display" class="text-sm font-semibold text-gray-900">{{ auth()->user()->nom }}</span>
                                </div>

                                <div class="flex justify-between rounded-xl bg-gray-50 p-3">
                                    <span class="text-sm font-medium text-gray-500">Email:</span>
                                    <span id="profile-email-display" class="text-sm font-semibold text-gray-900">{{ auth()->user()->email }}</span>
                                </div>

                                <div class="flex justify-between rounded-xl bg-gray-50 p-3">
                                    <span class="text-sm font-medium text-gray-500">Rôle:</span>
                                    <span class="rounded-full bg-pharaoh-gold px-2 py-0.5 text-xs font-medium text-white">Administrateur</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vue d'édition -->
            <div id="profile-edit" class="hidden bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 w-full text-center sm:mt-0 sm:text-left">
                        <h3 class="section-title">Modifier votre profil</h3>

                        <form id="profile-edit-form" class="mt-4" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="flex flex-col items-center">
                                <div class="mb-4 h-24 w-24 overflow-hidden rounded-full border-4 border-pharaoh-gold-light">
                                    <img id="profile-avatar-preview" src="{{ auth()->user()->avatar ? asset('storage/avatars/'.auth()->user()->avatar) : asset('storage/avatars/default.png') }}" alt="Avatar" class="h-full w-full object-cover">
                                </div>

                                <div class="mb-4 w-full">
                                    <label for="avatar" class="block text-sm font-medium text-gray-700">Changer la photo</label>
                                    <input type="file" id="avatar" name="avatar" class="input-field mt-1 block w-full">
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                                    <input type="text" id="nom" name="nom" value="{{ auth()->user()->nom }}" class="input-field mt-1 block w-full">
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" id="email" name="email" value="{{ auth()->user()->email }}" class="input-field mt-1 block w-full">
                                </div>

                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                                    <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer" class="input-field mt-1 block w-full">
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmation mot de passe</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="input-field mt-1 block w-full">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Messages de feedback -->
            <div id="profile-feedback" class="m-4 hidden rounded-xl bg-pharaoh-green-light/20 p-4 text-pharaoh-green-dark">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-pharaoh-green" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-pharaoh-green-dark" id="feedback-message"></p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <div id="view-buttons">
                    <button type="button" onclick="showEditMode()" class="btn-gold btn-press w-full px-4 py-2 text-base sm:ml-3 sm:w-auto sm:text-sm">Éditer</button>
                    <button type="button" onclick="closeProfileModal()" class="btn-secondary btn-press mt-3 w-full px-4 py-2 text-base sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Fermer</button>
                </div>

                <div id="edit-buttons" class="hidden">
                    <button type="button" onclick="saveProfileChanges()" class="btn-press inline-flex w-full justify-center rounded-lg bg-green-600 px-4 py-2 text-base font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">Enregistrer</button>
                    <button type="button" onclick="cancelEdit()" class="btn-secondary btn-press mt-3 w-full px-4 py-2 text-base sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Annuler</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openProfileModal() {
        document.getElementById('profile-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeProfileModal() {
        document.getElementById('profile-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        document.getElementById('profile-feedback').classList.add('hidden');
    }

    function showEditMode() {
        document.getElementById('profile-view').classList.add('hidden');
        document.getElementById('profile-edit').classList.remove('hidden');
        document.getElementById('view-buttons').classList.add('hidden');
        document.getElementById('edit-buttons').classList.remove('hidden');
        document.getElementById('profile-feedback').classList.add('hidden');
    }

    function cancelEdit() {
        document.getElementById('profile-edit').classList.add('hidden');
        document.getElementById('profile-view').classList.remove('hidden');
        document.getElementById('edit-buttons').classList.add('hidden');
        document.getElementById('view-buttons').classList.remove('hidden');

        document.getElementById('profile-edit-form').reset();
        document.getElementById('profile-avatar-preview').src = document.getElementById('profile-avatar-display').src;
    }

    function saveProfileChanges() {
        const form = document.getElementById('profile-edit-form');
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route("admin.updateProfile") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const feedbackDiv = document.getElementById('profile-feedback');

            if (data.success) {
                document.getElementById('profile-name-display').textContent = data.user.nom;
                document.getElementById('profile-email-display').textContent = data.user.email;

                if (data.user.avatar) {
                    const avatarUrl = '{{ asset("storage/avatars/") }}/' + data.user.avatar;
                    document.getElementById('profile-avatar-display').src = avatarUrl;

                    document.querySelectorAll('[data-nav-avatar]').forEach(img => { img.src = avatarUrl; });
                }

                feedbackDiv.classList.remove('hidden', 'bg-red-50', 'text-red-800');
                feedbackDiv.classList.add('bg-pharaoh-green-light/20', 'text-pharaoh-green-dark');
                document.getElementById('feedback-message').textContent = "Profil mis à jour avec succès!";

                document.getElementById('profile-edit').classList.add('hidden');
                document.getElementById('profile-view').classList.remove('hidden');
                document.getElementById('edit-buttons').classList.add('hidden');
                document.getElementById('view-buttons').classList.remove('hidden');
            } else {
                feedbackDiv.classList.remove('hidden', 'bg-pharaoh-green-light/20', 'text-pharaoh-green-dark');
                feedbackDiv.classList.add('bg-red-50', 'text-red-800');

                let errorMessage = 'Erreur lors de la mise à jour:';
                if (data.errors) {
                    errorMessage += '<ul class="mt-1 list-disc pl-5">';
                    for (const error in data.errors) {
                        errorMessage += `<li>${data.errors[error]}</li>`;
                    }
                    errorMessage += '</ul>';
                } else {
                    errorMessage += ' ' + (data.message || 'Veuillez réessayer.');
                }

                document.getElementById('feedback-message').innerHTML = errorMessage;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);

            const feedbackDiv = document.getElementById('profile-feedback');
            feedbackDiv.classList.remove('bg-pharaoh-green-light/20', 'text-pharaoh-green-dark');
            feedbackDiv.classList.add('bg-red-50', 'text-red-800');
            document.getElementById('feedback-message').textContent = "Une erreur est survenue lors de la mise à jour du profil.";
            feedbackDiv.classList.remove('hidden');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('profile-avatar-preview');

        if (avatarInput && avatarPreview) {
            avatarInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) { avatarPreview.src = e.target.result; };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        window.addEventListener('click', function (event) {
            const modal = document.getElementById('profile-modal');
            if (event.target === modal) {
                closeProfileModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            const modal = document.getElementById('profile-modal');
            if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeProfileModal();
            }
        });
    });
</script>
