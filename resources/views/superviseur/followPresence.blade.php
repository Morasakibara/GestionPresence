@extends('layouts.dashboard')

@section('header')
<div class="flex w-full flex-wrap items-center justify-between gap-3">
    <div>
        <span>Suivi des Présences</span>
        <p class="console-subtitle">Pointages, retards et absences de votre équipe.</p>
    </div>
    <a href="{{ route('superviseur.showAddMember') }}" class="btn-gold btn-press">
        <svg class="h-4 w-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        Ajouter membre
    </a>
</div>
@endsection

@section('content')

@if(count($utilisateurs) === 0)
    <div class="alert alert-warning mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    Aucun employé dans votre équipe. Ajoutez des membres via « Ajouter membre ».
                </p>
            </div>
        </div>
    </div>
@else
    <!-- Tableau des présences -->
    <div class="pharaoh-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Photo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Statut localisation</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($utilisateurs as $utilisateur)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="h-10 w-10 flex-shrink-0">
                                @if($utilisateur->avatar)
                                    <img src="{{ asset('storage/avatars/'.$utilisateur->avatar) }}" alt="{{ $utilisateur->nom }}" class="h-10 w-10 rounded-full object-cover ring-2 ring-pharaoh-gold/20">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-pharaoh-gold/10 flex items-center justify-center text-sm font-bold text-pharaoh-gold">{{ strtoupper(substr($utilisateur->nom, 0, 2)) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="text-sm font-semibold text-[#080808]">{{ $utilisateur->nom }}</div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            @php
                                $lastPresence = App\Models\Presence::where('employerID', $utilisateur->id)
                                    ->whereNotNull('latitude_arrivee')
                                    ->orderBy('date', 'desc')
                                    ->first();
                            @endphp
                            @if($lastPresence && ($lastPresence->localisation_validee_arrivee || $lastPresence->localisation_validee_depart))
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500 mr-1.5"></span>
                                    Validée
                                </span>
                            @elseif($lastPresence)
                                <span class="inline-flex items-center rounded-full bg-yellow-50 px-2.5 py-0.5 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-500 mr-1.5"></span>
                                    Non validée
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                    Non disponible
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <button type="button" onclick="openPopup('{{ $utilisateur->id }}')" class="btn-gold btn-press inline-flex items-center px-3 py-1.5 text-sm">
                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

<!-- Modal -->
<div id="userModal" class="modal hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity modal-overlay"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl text-left shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4 sm:pb-6 rounded-t-2xl">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="section-title" id="modal-title">Informations de l'utilisateur</h3>
                        <div class="mt-4" id="userDetails">
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-6 rounded-b-2xl">
                <a id="viewMoreLink" href="{{ route('viewUser', ['id' => ':id']) }}" class="btn-gold btn-press w-full sm:ml-3 sm:w-auto">
                    Voir plus
                </a>
                <button type="button" class="close-modal btn-press mt-3 inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto">
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
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', closeModal);
    });
    document.querySelector('.modal-overlay').addEventListener('click', closeModal);
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !document.getElementById('userModal').classList.contains('hidden')) {
            closeModal();
        }
    });
});

function openPopup(userId) {
    document.getElementById('userModal').classList.remove('hidden');
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    $.ajax({
        url: '/superviseur/getUserDetails/' + userId,
        type: 'GET',
        success: function(data) {
            if (data.detailsHtml) {
                $('#userDetails').html(data.detailsHtml);
            } else {
                $('#userDetails').html('<p class="text-red-500">Format de données incorrect.</p>');
            }
            $('#viewMoreLink').attr('href', $('#viewMoreLink').attr('href').replace(':id', userId));
        },
        error: function(err) {
            $('#userDetails').html('<div class="text-red-500 py-3">Erreur lors de la récupération des données.</div>');
        }
    });
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
}
</script>
@endsection
