@extends('layouts.dashboard')

@section('header')
Tableau de bord de l'administrateur
@endsection

@section('navigation')
<!-- Current: "bg-3hcig-blue text-white", Default: "text-gray-300 hover:bg-3hcig-blue hover:text-white" -->
<a href="{{ route('admin.dashboard') }}" class="rounded-md bg-3hcig-blue px-3 py-2 text-sm font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('admin.addEmployee') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Ajouter employé</a>
<a href="{{ route('admin.deleteEmployee') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Supprimer employé</a>
<a href="{{ route('admin.generateReport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Générer Bilan</a>
<a href="{{ route('admin.showEmployeeList') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Liste des employés</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('admin.dashboard') }}" class="block rounded-md bg-3hcig-blue px-3 py-2 text-base font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('admin.addEmployee') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Ajouter employé</a>
<a href="{{ route('admin.deleteEmployee') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Supprimer employé</a>
<a href="{{ route('admin.generateReport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Générer Bilan</a>
<a href="{{ route('admin.showEmployeeList') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Liste des employés</a>
@endsection

@section('content')
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('admin.addEmployee') }}" class="flex flex-col items-center justify-center rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="rounded-full bg-3hcig-blue/10 p-3 text-3hcig-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-medium text-gray-900">Ajouter employé</h2>
        <p class="mt-1 text-sm text-gray-500">Ajouter un nouvel employé au système</p>
    </a>

    <a href="{{ route('admin.deleteEmployee') }}" class="flex flex-col items-center justify-center rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="rounded-full bg-3hcig-blue/10 p-3 text-3hcig-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-medium text-gray-900">Supprimer employé</h2>
        <p class="mt-1 text-sm text-gray-500">Supprimer un employé du système</p>
    </a>

    <a href="{{ route('admin.generateReport') }}" class="flex flex-col items-center justify-center rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="rounded-full bg-3hcig-green/10 p-3 text-3hcig-green">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-medium text-gray-900">Générer Bilan</h2>
        <p class="mt-1 text-sm text-gray-500">Créer des rapports détaillés</p>
    </a>

    <a href="{{ route('admin.showEmployeeList') }}" class="flex flex-col items-center justify-center rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="rounded-full bg-3hcig-blue/10 p-3 text-3hcig-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-medium text-gray-900">Liste des employés</h2>
        <p class="mt-1 text-sm text-gray-500">Voir tous les employés</p>
    </a>
</div>
@endsection
