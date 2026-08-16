@extends('layouts.app')

@section('title', 'Liste des Employés')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Liste des Employés</h1>
        <p class="mt-2 text-sm text-gray-600">Gérez les employés et superviseurs de votre organisation</p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-md bg-3hcig-green-light/20 p-4 text-3hcig-green-dark">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-md bg-red-50 p-4 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 rounded-lg bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.showEmployeeList') }}" class="space-y-4">
            @csrf
            <div>
                <label for="search" class="sr-only">Rechercher un employé</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" class="block w-full rounded-md border-gray-300 pl-10 py-3 shadow-sm focus:border-3hcig-blue focus:ring focus:ring-3hcig-blue focus:ring-opacity-20 sm:text-sm" placeholder="Rechercher par nom, email, poste..." value="{{ old('search', $search) }}">
                </div>
            </div>

            <div class="flex flex-wrap gap-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-3hcig-blue shadow-sm focus:border-3hcig-blue focus:ring focus:ring-3hcig-blue focus:ring-opacity-20" name="roles[]" value="Employer" {{ in_array('Employer', $roles) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Employés</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-3hcig-blue shadow-sm focus:border-3hcig-blue focus:ring focus:ring-3hcig-blue focus:ring-opacity-20" name="roles[]" value="Superviseur" {{ in_array('Superviseur', $roles) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700">Superviseurs</span>
                </label>
                <button type="submit" class="ml-auto rounded-md bg-3hcig-blue px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                    Filtrer
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Rôle</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Présences suspectes</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-white">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($employees as $employee)
                        <tr class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="{{ $employee->avatar ?? asset('storage/avatars/default.png') }}" alt="{{ $employee->nom }}">
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-gray-900">{{ $employee->nom }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $employee->email }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @if(strtolower($employee->role) == 'administrateur')
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                        Administrateur
                                    </span>
                                @elseif(strtolower($employee->role) == 'superviseur')
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                        Superviseur
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        Employé
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @if(isset($employee->suspect_count) && $employee->suspect_count > 0)
                                    <a href="{{ route('admin.suspectPresences') . '?search=' . urlencode($employee->nom) }}" class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 hover:bg-red-200">
                                        {{ $employee->suspect_count }} suspecte(s)
                                    </a>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">
                                        0
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <form method="POST" action="{{ route('admin.deleteEmployee.fromList') }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.');">
                                    @csrf
                                    <input type="hidden" name="email" value="{{ $employee->email }}">
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination if available -->
        @if ($employees instanceof \Illuminate\Pagination\LengthAwarePaginator && $employees->hasPages())
        <div class="border-t border-gray-200 px-4 py-3 sm:px-6">
            {{ $employees->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
