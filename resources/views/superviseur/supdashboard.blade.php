@extends('layouts.dashboard')

@section('header')
Tableau de bord du superviseur de l'équipe {{ $equipe ?? 'Non définie' }}
@endsection

@section('navigation')
<!-- Current: "bg-3hcig-blue text-white", Default: "text-gray-300 hover:bg-3hcig-blue hover:text-white" -->
<a href="{{ route('superviseur.supdashboard') }}" class="rounded-md bg-3hcig-blue px-3 py-2 text-sm font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('superviseur.showAddMember') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Ajout de membre</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Suivre les présences</a>
<a href="{{ route('superviseur.generateReport2') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Générer Rapports</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('superviseur.supdashboard') }}" class="block rounded-md bg-3hcig-blue px-3 py-2 text-base font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('superviseur.showAddMember') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Ajout de membre</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Suivre les présences</a>
<a href="{{ route('superviseur.generateReport2') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Générer Rapports</a>
@endsection

@section('content')
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <a href="{{ route('superviseur.showAddMember') }}" class="flex flex-col items-center justify-center rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="rounded-full bg-3hcig-blue/10 p-3 text-3hcig-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-medium text-gray-900">Ajout de membre à l'équipe</h2>
        <p class="mt-1 text-sm text-gray-500">Ajouter un nouveau membre à votre équipe</p>
    </a>

    <a href="{{ route('superviseur.showFollowPresence') }}" class="flex flex-col items-center justify-center rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="rounded-full bg-3hcig-green/10 p-3 text-3hcig-green">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-medium text-gray-900">Suivre les présences</h2>
        <p class="mt-1 text-sm text-gray-500">Superviser les présences de votre équipe</p>
    </a>

    <a href="{{ route('superviseur.generateReport2') }}" class="flex flex-col items-center justify-center rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="rounded-full bg-3hcig-blue/10 p-3 text-3hcig-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-medium text-gray-900">Générer Rapports</h2>
        <p class="mt-1 text-sm text-gray-500">Créer des rapports détaillés sur votre équipe</p>
    </a>
</div>
@endsection