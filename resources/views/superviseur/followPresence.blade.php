@extends('layouts.app')

@section('title', 'Suivi des Présences')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Suivi des Présences</h1>
        <p class="mt-2 text-sm text-gray-600">Consultez et gérez les présences des employés de votre équipe</p>
    </div>

    @if(count($utilisateurs) === 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Aucun employé n'a été trouvé dans votre équipe. Vous pouvez ajouter des membres à votre équipe via la section "Ajouter membre".
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="mt-8 overflow-hidden rounded-lg bg-white shadow">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[#080808]">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Photo de profil</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Nom</th>
                            <!-- Geolocalisation -->
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Dernière localisation</th>

                            <!-- Et dans le corps de la table, après la colonne nom et avant la colonne action pour chaque employé -->

                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($utilisateurs as $utilisateur)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="h-12 w-12 flex-shrink-0">
                                    @if($utilisateur->avatar)
                                        <img src="{{ asset('storage/avatars/'.$utilisateur->avatar) }}" alt="{{ $utilisateur->nom }}" class="h-12 w-12 rounded-full object-cover">
                                    @else
                                        <img src="{{ asset('storage/avatars/default.png') }}" alt="Default Avatar" class="h-12 w-12 rounded-full object-cover">
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $utilisateur->nom }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @php
                                    $lastPresence = App\Models\Presence::where('employerID', $utilisateur->id)
                                        ->whereNotNull('latitude_arrivee')
                                        ->orderBy('date', 'desc')
                                        ->first();
                                @endphp

                                @if($lastPresence && ($lastPresence->localisation_validee_arrivee || $lastPresence->localisation_validee_depart))
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Validée
                                    </span>
                                @elseif($lastPresence)
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Non validée
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                        <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Non disponible
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <button type="button" onclick="openPopup('{{ $utilisateur->id }}')" class="inline-flex items-center rounded-md bg-3hcig-blue px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                                    <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Suivre
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<!-- Modal - Version corrigée -->
<div id="userModal" class="modal hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay"></div>

    <!-- Modal panel -->
    <div class="flex min-h-full items-center justify-center p-4">
    <div class="relative bg-white rounded-lg text-left shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-t-lg">
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                        Informations de l'utilisateur
                    </h3>
                    <div class="mt-4" id="userDetails">
                        <!-- Dynamic content will be loaded here -->
                        <div class="animate-pulse">
                            <div class="flex items-center space-x-4">
                                <div class="h-12 w-12 rounded-full bg-gray-200"></div>
                                <div class="space-y-2">
                                    <div class="h-4 w-36 rounded bg-gray-200"></div>
                                    <div class="h-3 w-24 rounded bg-gray-200"></div>
                                </div>
                            </div>
                            <div class="mt-4 space-y-2">
                                <div class="h-4 w-full rounded bg-gray-200"></div>
                                <div class="h-4 w-3/4 rounded bg-gray-200"></div>
                                <div class="h-4 w-1/2 rounded bg-gray-200"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 rounded-b-lg">
            <a id="viewMoreLink" href="{{ route('viewUser', ['id' => ':id']) }}" class="inline-flex w-full justify-center rounded-md border border-transparent bg-3hcig-blue px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                Voir plus
            </a>
            <button type="button" class="close-modal mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                Fermer
            </button>
        </div>
    </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la fermeture de la modal
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', closeModal);
    });

    // Fermer la modal si on clique sur l'overlay
    document.querySelector('.modal-overlay').addEventListener('click', closeModal);

    // Fermer la modal avec la touche Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !document.getElementById('userModal').classList.contains('hidden')) {
            closeModal();
        }
    });
});

function openPopup(userId) {
    // Afficher la modal avec l'état de chargement
    document.getElementById('userModal').classList.remove('hidden');

    // Configuration de l'en-tête CSRF pour les requêtes AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Requête AJAX pour obtenir les détails de l'utilisateur
    $.ajax({
        url: '/superviseur/getUserDetails/' + userId,
        type: 'GET',
        success: function(data) {
            // Vérifier si les données sont bien reçues
            console.log('Données reçues:', data);

            // Afficher les données dans la modal
            if (data.detailsHtml) {
                $('#userDetails').html(data.detailsHtml);
            } else {
                $('#userDetails').html('<p class="text-red-500">Le format de données reçu n\'est pas correct.</p>');
                console.error('Format de données incorrect:', data);
            }

            // Mettre à jour le lien "Voir plus"
            $('#viewMoreLink').attr('href', $('#viewMoreLink').attr('href').replace(':id', userId));
        },
        error: function(err) {
            $('#userDetails').html('<div class="text-red-500 py-3">Erreur lors de la récupération des données. Veuillez réessayer.</div>');
            console.error('Erreur AJAX:', err);
        }
    });
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
}
</script>
@endsection
