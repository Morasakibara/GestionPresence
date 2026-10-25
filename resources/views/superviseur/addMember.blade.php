@extends('layouts.dashboard')

@section('title', 'Ajouter des membres à votre équipe')
@section('breadcrumb', 'Ajout de membre')

@section('header')
    <div>
        <span>Gestion des membres de votre équipe</span>
        <p class="console-subtitle">Recherchez, ajoutez ou retirez des membres de votre équipe.</p>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
    <div class="space-y-5">
        <div class="console-card">
            <div class="console-card-body">
                <form method="GET" action="{{ route('superviseur.showAddMemberForm') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="search" class="sr-only">Rechercher un employé</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" name="search" id="search" class="input-field pl-10" placeholder="Rechercher par nom" value="{{ $search ?? '' }}">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-gold">Rechercher</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Employés disponibles</h2>
                    <p class="console-card-sub">Ajoutez ou retirez un employé de votre équipe.</p>
                </div>
            </div>

            <div class="console-card-body">
                <div class="table-scroll overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="table-head">
                            <tr>
                                <th scope="col">Nom</th>
                                <th scope="col">Statut</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody class="table-body divide-y divide-gray-200 bg-white">
                            @forelse($employers as $employer)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-4 sm:px-6">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $employer->avatar ?? asset('storage/avatars/default.png') }}" alt="{{ $employer->nom }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-medium text-gray-900">{{ $employer->nom }}</div>
                                            <div class="text-sm text-gray-500">{{ $employer->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm sm:px-6">
                                    @if(isset($teamMemberIds) && in_array($employer->id, $teamMemberIds))
                                        <span class="badge badge-success">Membre de l'équipe</span>
                                    @else
                                        <span class="badge badge-info">Non membre</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm sm:px-6">
                                    @if(isset($teamMemberIds) && in_array($employer->id, $teamMemberIds))
                                        <form action="{{ route('superviseur.removeMemberFromTeam', $employer->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-danger px-3 py-2 text-xs sm:text-sm">
                                                <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Retirer de l'équipe
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('superviseur.addMemberToTeam', $employer->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-gold px-3 py-2 text-xs sm:text-sm">
                                                <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Ajouter à l'équipe
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <span class="empty-state-icon">
                                            <svg class="h-6 w-6 text-[#B77F1D]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </span>
                                        <h3 class="mt-3 text-sm font-semibold text-gray-900">Aucun employé trouvé</h3>
                                        <p class="mt-1 text-sm text-gray-500">Aucun résultat pour votre recherche.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
