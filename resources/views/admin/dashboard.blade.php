@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Tableau de bord de l'administrateur</span>
    <!-- Indicateur de notifications -->
    <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center px-2 py-1 text-sm font-medium text-3hcig-blue hover:bg-gray-100 rounded-md">
        <svg class="h-6 w-6 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        Notifications
        @if(Auth::user()->unreadNotifications->count() > 0)
            <span class="absolute -top-1 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
                {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
            </span>
        @endif
    </a>
</div>
@endsection

@section('navigation')
<!-- Current: "bg-3hcig-blue text-white", Default: "text-gray-300 hover:bg-3hcig-blue hover:text-white" -->
<a href="{{ route('admin.dashboard') }}" class="rounded-md bg-3hcig-blue px-3 py-2 text-sm font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('admin.addEmployee') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Ajouter employé</a>
<a href="{{ route('admin.generateReport') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Générer Bilan</a>
<a href="{{ route('admin.showEmployeeList') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Liste des employés</a>
<a href="{{ route('notifications.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white relative">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('admin.dashboard') }}" class="block rounded-md bg-3hcig-blue px-3 py-2 text-base font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('admin.addEmployee') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Ajouter employé</a>
<a href="{{ route('admin.generateReport') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Générer Bilan</a>
<a href="{{ route('admin.showEmployeeList') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Liste des employés</a>
<a href="{{ route('notifications.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white relative">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute top-2 right-2 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
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

    <a href="{{ route('admin.showEmployeeList') }}" class="flex flex-col items-center justify-center rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="rounded-full bg-3hcig-blue/10 p-3 text-3hcig-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-medium text-gray-900">Liste des employés</h2>
        <p class="mt-1 text-sm text-gray-500">Voir et gérer tous les employés</p>
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

    <a href="{{ route('notifications.index') }}" class="flex flex-col items-center justify-center rounded-lg bg-white p-6 shadow-sm hover:shadow-md transition-shadow relative">
        <div class="rounded-full bg-3hcig-blue/10 p-3 text-3hcig-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @if(Auth::user()->unreadNotifications->count() > 0)
                <span class="absolute top-2 right-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
                    {{ Auth::user()->unreadNotifications->count() }}
                </span>
            @endif
        </div>
        <h2 class="mt-4 text-lg font-medium text-gray-900">Notifications</h2>
        <p class="mt-1 text-sm text-gray-500">Voir les alertes d'absence et retards</p>
    </a>
</div>
@endsection
