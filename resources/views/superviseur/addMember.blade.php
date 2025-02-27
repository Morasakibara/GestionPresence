@extends('layouts.app')

@section('title', 'Ajouter des membres à votre équipe')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Ajouter des membres à votre équipe</h1>
        <p class="mt-2 text-sm text-gray-600">Recherchez et ajoutez de nouveaux membres à votre équipe</p>
    </div>

    @if(session('success'))
    <div class="mb-6 rounded-md bg-3hcig-green-light/20 p-4 text-3hcig-green-dark">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 rounded-md bg-red-50 p-4 text-red-800">
        {{ session('error') }}
    </div>
    @endif

    <div class="mb-6 rounded-lg bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('superviseur.showAddMemberForm') }}" class="space-y-4">
            @csrf
            <div>
                <label for="search" class="sr-only">Rechercher un employé</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" class="block w-full rounded-md border-gray-300 pl-10 py-3 shadow-sm focus:border-3hcig-blue focus:ring focus:ring-3hcig-blue focus:ring-opacity-20 sm:text-sm" placeholder="Rechercher par nom" value="{{ $search ?? '' }}">
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center rounded-md bg-3hcig-blue px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                    Rechercher
                </button>
            </div>
        </form>
    </div>

    <div class="mt-8 overflow-hidden rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-3hcig-blue-dark">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Nom</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($employers as $employer)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4">
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
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            <form action="{{ route('superviseur.addMemberToTeam', $employer->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-md bg-3hcig-blue px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                                    <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Ajouter à l'équipe
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                            <div class="py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun employé trouvé</h3>
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
@endsection
