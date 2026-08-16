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
<a href="{{ route('admin.workplace-locations.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">
    Lieux de travail
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
<a href="{{ route('admin.workplace-locations.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">
    Lieux de travail
</a>
@endsection

@section('content')
<!-- Vue d'ensemble -->
<div class="mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-3">Vue d'ensemble du système</h2>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total des employés -->
        <div class="pharaoh-card p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total des employés</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $totalEmployees ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Total des superviseurs -->
        <div class="pharaoh-card p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total des superviseurs</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $totalSupervisors ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Présents aujourd'hui -->
        <div class="pharaoh-card p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Présents aujourd'hui</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $presentToday ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Absents aujourd'hui -->
        <div class="pharaoh-card p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Absents aujourd'hui</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $absentToday ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques mensuelles -->
<div class="mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-3">Statistiques mensuelles</h2>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Total des présences ce mois -->
        <div class="pharaoh-card p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Présences ce mois</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $monthlyPresences ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Total des absences ce mois -->
        <div class="pharaoh-card p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Absences ce mois</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $monthlyAbsences ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Total des retards ce mois -->
        <div class="pharaoh-card p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Retards ce mois</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $monthlyLates ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accès rapides -->
<div class="mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-3">Accès rapides</h2>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('admin.addEmployee') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow">
            <div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Ajouter employé</h2>
            <p class="mt-1 text-sm text-gray-500">Ajouter un nouvel employé au système</p>
        </a>

        <a href="{{ route('admin.showEmployeeList') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow">
            <div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Liste des employés</h2>
            <p class="mt-1 text-sm text-gray-500">Voir et gérer tous les employés</p>
        </a>

        <a href="{{ route('admin.generateReport') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow">
            <div class="rounded-full bg-3hcig-green/10 p-3 text-3hcig-green">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Générer Bilan</h2>
            <p class="mt-1 text-sm text-gray-500">Créer des rapports détaillés</p>
        </a>

        <a href="{{ route('notifications.index') }}" class="pharaoh-card btn-press flex flex-col items-center justify-center p-6 hover:shadow-lg transition-shadow relative">
            <div class="rounded-full bg-pharaoh-gold/10 p-3 text-pharaoh-gold">
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
</div>

<!-- Dernières notifications -->
@if(Auth::user()->notifications->count() > 0)
<div class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-gray-800">Dernières notifications</h2>
        <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-3hcig-blue hover:text-3hcig-blue-dark">
            Voir toutes
        </a>
    </div>
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        @foreach(Auth::user()->notifications->take(3) as $notification)
            <div class="border-l-4 {{ $notification->read_at ? 'border-gray-300' : 'border-3hcig-blue' }} p-4 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                <div class="flex justify-between">
                    <p class="text-sm text-gray-700 {{ $notification->read_at ? '' : 'font-medium' }}">
                        {{ isset($notification->data['message']) ? $notification->data['message'] : 'Notification' }}
                    </p>
                    <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection
