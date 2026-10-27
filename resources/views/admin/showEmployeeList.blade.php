@extends('layouts.dashboard')

@section('title', 'Liste des Employés')
@section('breadcrumb', 'Liste des employés')

@section('header')
    <div>
        <span>Liste des Employés</span>
        <p class="console-subtitle">Gérez les employés et superviseurs de votre organisation.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.addEmployee') }}" class="btn-gold">
            <svg class="-ml-1 mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Ajouter un employé
        </a>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="space-y-5">
    <div class="rounded-2xl border border-gray-200/70 bg-white p-4 shadow-card sm:p-6">
        <form method="GET" action="{{ route('admin.showEmployeeList') }}" class="space-y-4">
            @csrf
            <div>
                <label for="search" class="sr-only">Rechercher un employé</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" class="input-field pl-10" placeholder="Rechercher par nom, email, poste..." value="{{ old('search', $search) }}">
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-pharaoh-gold focus:ring-pharaoh-gold" name="roles[]" value="Employer" {{ in_array('Employer', $roles) ? 'checked' : '' }}>
                    <span>Employés</span>
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-pharaoh-gold focus:ring-pharaoh-gold" name="roles[]" value="Superviseur" {{ in_array('Superviseur', $roles) ? 'checked' : '' }}>
                    <span>Superviseurs</span>
                </label>
                <button type="submit" class="btn-gold ml-auto px-4 py-2 text-sm">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <div class="table-scroll">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-head">
                    <tr>
                        <th scope="col">Nom</th>
                        <th scope="col">Email</th>
                        <th scope="col">Rôle</th>
                        <th scope="col" class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="table-body divide-y divide-gray-200 bg-white">
                    @forelse ($employees as $employee)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ $employee->avatar ?? asset('storage/avatars/default.png') }}" alt="{{ $employee->nom }}">
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-gray-900">{{ $employee->nom }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $employee->email }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                @if(strtolower($employee->role) == 'administrateur')
                                    <span class="badge badge-danger">Administrateur</span>
                                @elseif(strtolower($employee->role) == 'superviseur')
                                    <span class="badge badge-warning">Superviseur</span>
                                @else
                                    <span class="badge badge-success">Employé</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                @php $managed = $employee->role === 'Superviseur' ? (int) ($managedCounts[$employee->id] ?? 0) : 0; @endphp
                                <button type="button"
                                        onclick="ouvrirSuppressionModal({ id: {{ $employee->id }}, email: '{{ e($employee->email) }}', nom: '{{ e($employee->nom) }}', role: '{{ e($employee->role) }}', encadrés: {{ $managed }} })"
                                        class="btn-danger-soft ring-1 ring-inset ring-red-600/20">
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Supprimer
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    </div>
                                    <h3 class="mt-4 text-sm font-semibold text-gray-900">Aucun employé trouvé</h3>
                                    <p class="mt-1 text-sm text-gray-500">Modifiez vos filtres de recherche ou ajoutez un employé.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
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
