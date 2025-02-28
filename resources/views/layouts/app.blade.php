<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', '3HCIG COOP-CA') }}</title>
    @vite('resources/css/app.css')
    @yield('additional_css')
</head>
<body class="h-full">
    @php
    $currentRole = session('current_role', null);
    $user = Auth::user();
    @endphp
    @if (Auth::check())
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-3hcig-blue-dark text-white">
            <!-- Logo in Sidebar Header -->
            <div class="flex h-16 items-center justify-center border-b border-3hcig-blue">
                <img class="h-20 w-auto" src="{{ asset('/storage/avatars/logo-3HCIG.png') }}" alt="3HCIG COOP-CA">
            </div>
            <!-- Nav Links -->
            <nav class="mt-5 px-2">
                @if ($user->role === 'Superviseur')
                    @if ($currentRole === 'Employer')
                        <a href="{{ route('user.dashboard') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('user.dashboard') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('user.dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Tableau de bord
                        </a>
                        <a href="{{ route('user.profile') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('user.profile') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('user.profile') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profil
                        </a>
                        <a href="{{ route('presence.index') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('presence.index') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('presence.index') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Présence
                        </a>
                        <a href="{{ route('user.presence.report') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('user.presence.report') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('user.presence.report') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Bilan de présence
                        </a>
                    @elseif ($currentRole === 'Superviseur')
                        <a href="{{ route('superviseur.supdashboard') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('superviseur.supdashboard') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('superviseur.supdashboard') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Tableau de bord
                        </a>
                        <a href="{{ route('superviseur.showFollowPresence') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('superviseur.showFollowPresence') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('superviseur.showFollowPresence') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Suivre les présences
                        </a>
                        <a href="{{ route('superviseur.generateReport2') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('superviseur.generateReport2') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('superviseur.generateReport2') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Générer un rapport
                        </a>
                        <a href="{{ route('superviseur.showAddMember') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('superviseur.showAddMember') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('superviseur.showAddMember') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Ajouter membre
                        </a>
                    @endif
                @elseif ($user->role === 'Employer')
                    <a href="{{ route('user.dashboard') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('user.dashboard') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                        <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('user.dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Tableau de bord
                    </a>
                    <a href="{{ route('user.profile') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('user.profile') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                        <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('user.profile') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profil
                    </a>
                    <a href="{{ route('presence.index') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('presence.index') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                        <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('presence.index') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Présence
                    </a>
                    <a href="{{ route('user.presence.report') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('user.presence.report') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                        <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('user.presence.report') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Bilan de présence
                    </a>
                @elseif ($user->role === 'administrateur')
                    <a href="{{ route('admin.dashboard') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                        <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Tableau de bord
                    </a>
                    <a href="{{ route('admin.addEmployee') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('admin.addEmployee') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                        <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('admin.addEmployee') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter employé
                    </a>
                    <a href="{{ route('admin.deleteEmployee') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('admin.deleteEmployee') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                        <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('admin.deleteEmployee') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Supprimer employé
                    </a>
                    <a href="{{ route('admin.generateReport') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('admin.generateReport') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                        <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('admin.generateReport') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Générer Bilan
                    </a>
                    <a href="{{ route('admin.showEmployeeList') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('admin.showEmployeeList') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                        <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('admin.showEmployeeList') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Liste des Employés
                    </a>
                @endif
            </nav>

            <!-- Settings at bottom -->
            <div class="absolute bottom-0 w-full p-4">
                @if(Auth::check() && Auth::user()->role === 'Superviseur')
                <a href="{{ route('role.switch') }}" class="group flex w-full items-center rounded-md px-2 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Changer de rôle
                </a>
                @endif
                <a href="{{ route('logouts') }}" class="group mt-2 flex w-full items-center rounded-md px-2 py-2 text-base font-medium text-gray-300 hover:bg-red-600 hover:text-white">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Déconnexion
                </a>
            </div>
            <div class="absolute bottom-0 w-full p-4">
                @if(Auth::check() && Auth::user()->role === 'administrateur')
                <a href="{{route('admin.showProfile')}}" onclick="openProfileModal()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="user-profile-button">
                    Votre Profil
                </a>

                <!-- Modal (hidden par défaut) -->
                <div id="profile-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Background overlay, show/hide basé sur l'état du modal -->
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                        <!-- Centered modal panel -->
                        <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                            <!-- Vue par défaut (affichage des informations) -->
                            <div id="profile-view" class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 w-full text-center sm:mt-0 sm:text-left">
                                        <h3 class="text-lg font-medium leading-6 text-3hcig-blue-dark" id="modal-title">
                                            Votre Profil
                                        </h3>

                                        <div class="mt-4 flex flex-col items-center">
                                            <!-- Photo de profil -->
                                            <div class="mb-4 h-24 w-24 overflow-hidden rounded-full border-4 border-3hcig-blue-light">
                                                <img id="profile-avatar-display" src="{{ auth()->user()->avatar ? asset('storage/avatars/'.auth()->user()->avatar) : asset('storage/avatars/default.png') }}" alt="Avatar" class="h-full w-full object-cover">
                                            </div>

                                            <!-- Informations de l'administrateur -->
                                            <div class="w-full space-y-3 pt-2">
                                                <div class="flex justify-between rounded-md bg-gray-50 p-3">
                                                    <span class="text-sm font-medium text-gray-500">Nom:</span>
                                                    <span id="profile-name-display" class="text-sm font-semibold text-gray-900">{{ auth()->user()->nom }}</span>
                                                </div>

                                                <div class="flex justify-between rounded-md bg-gray-50 p-3">
                                                    <span class="text-sm font-medium text-gray-500">Email:</span>
                                                    <span id="profile-email-display" class="text-sm font-semibold text-gray-900">{{ auth()->user()->email }}</span>
                                                </div>

                                                <div class="flex justify-between rounded-md bg-gray-50 p-3">
                                                    <span class="text-sm font-medium text-gray-500">Rôle:</span>
                                                    <span class="rounded-full bg-3hcig-blue px-2 py-0.5 text-xs font-medium text-white">
                                                        Administrateur
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vue d'édition (cachée par défaut) -->
                            <div id="profile-edit" class="hidden bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 w-full text-center sm:mt-0 sm:text-left">
                                        <h3 class="text-lg font-medium leading-6 text-3hcig-blue-dark" id="modal-title">
                                            Modifier votre profil
                                        </h3>

                                        <form id="profile-edit-form" class="mt-4" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            <div class="flex flex-col items-center">
                                                <!-- Preview de la photo de profil -->
                                                <div class="mb-4 h-24 w-24 overflow-hidden rounded-full border-4 border-3hcig-blue-light">
                                                    <img id="profile-avatar-preview" src="{{ auth()->user()->avatar ? asset('storage/avatars/'.auth()->user()->avatar) : asset('storage/avatars/default.png') }}" alt="Avatar" class="h-full w-full object-cover">
                                                </div>

                                                <!-- Champ pour l'upload d'avatar -->
                                                <div class="mb-4 w-full">
                                                    <label for="avatar" class="block text-sm font-medium text-gray-700">Changer la photo</label>
                                                    <input type="file" id="avatar" name="avatar" class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 text-sm shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue">
                                                </div>
                                            </div>

                                            <div class="space-y-4">
                                                <div>
                                                    <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                                                    <input type="text" id="nom" name="nom" value="{{ auth()->user()->nom }}" class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue sm:text-sm">
                                                </div>

                                                <div>
                                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                                    <input type="email" id="email" name="email" value="{{ auth()->user()->email }}" class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue sm:text-sm">
                                                </div>

                                                <div>
                                                    <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                                                    <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer" class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue sm:text-sm">
                                                </div>

                                                <div>
                                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmation mot de passe</label>
                                                    <input type="password" id="password_confirmation" name="password_confirmation" class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue sm:text-sm">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Messages de feedback (cachés par défaut) -->
                            <div id="profile-feedback" class="hidden rounded-md bg-3hcig-green-light/20 p-4 m-4 text-3hcig-green-dark">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-3hcig-green" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-3hcig-green-dark" id="feedback-message"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer du modal -->
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <!-- Bouttons pour vue d'affichage -->
                                <div id="view-buttons">
                                    <button type="button" onclick="showEditMode()" class="inline-flex w-full justify-center rounded-md border border-transparent bg-3hcig-blue px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                                        Éditer
                                    </button>
                                    <button type="button" onclick="closeProfileModal()" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Fermer
                                    </button>
                                </div>

                                <!-- Bouttons pour vue d'édition -->
                                <div id="edit-buttons" class="hidden">
                                    <button type="button" onclick="saveProfileChanges()" class="inline-flex w-full justify-center rounded-md border border-transparent bg-3hcig-green px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-3hcig-green-light focus:outline-none focus:ring-2 focus:ring-3hcig-green focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                                        Enregistrer
                                    </button>
                                    <button type="button" onclick="cancelEdit()" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                // Ouvrir le modal
                function openProfileModal() {
                    document.getElementById('profile-modal').classList.remove('hidden');
                    document.body.classList.add('overflow-hidden'); // Empêcher le scrolling
                }

                // Fermer le modal
                function closeProfileModal() {
                    document.getElementById('profile-modal').classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    // Cacher le message de feedback s'il est visible
                    document.getElementById('profile-feedback').classList.add('hidden');
                }

                // Afficher le mode édition
                function showEditMode() {
                    document.getElementById('profile-view').classList.add('hidden');
                    document.getElementById('profile-edit').classList.remove('hidden');
                    document.getElementById('view-buttons').classList.add('hidden');
                    document.getElementById('edit-buttons').classList.remove('hidden');
                    document.getElementById('profile-feedback').classList.add('hidden');
                }

                // Annuler l'édition et revenir à la vue d'affichage
                function cancelEdit() {
                    document.getElementById('profile-edit').classList.add('hidden');
                    document.getElementById('profile-view').classList.remove('hidden');
                    document.getElementById('edit-buttons').classList.add('hidden');
                    document.getElementById('view-buttons').classList.remove('hidden');

                    // Réinitialiser le formulaire
                    document.getElementById('profile-edit-form').reset();
                    // Réinitialiser la prévisualisation de l'avatar
                    document.getElementById('profile-avatar-preview').src = document.getElementById('profile-avatar-display').src;
                }

                // Enregistrer les modifications
                function saveProfileChanges() {
                    const form = document.getElementById('profile-edit-form');
                    const formData = new FormData(form);

                    // Ajouter une entête CSRF pour Laravel
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch('{{ route("admin.updateProfile") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Mettre à jour l'affichage
                            document.getElementById('profile-name-display').textContent = data.user.nom;
                            document.getElementById('profile-email-display').textContent = data.user.email;

                            // Mettre à jour l'avatar si un nouveau est téléchargé
                            if (data.user.avatar) {
                                const avatarUrl = '{{ asset("storage/avatars/") }}/' + data.user.avatar;
                                document.getElementById('profile-avatar-display').src = avatarUrl;

                                // Mettre à jour l'avatar dans la barre de navigation
                                const navAvatar = document.querySelector('.nav-profile-img') || document.querySelector('.profile-image');
                                if (navAvatar) navAvatar.src = avatarUrl;
                            }

                            // Afficher le message de succès
                            const feedbackDiv = document.getElementById('profile-feedback');
                            document.getElementById('feedback-message').textContent = "Profil mis à jour avec succès!";
                            feedbackDiv.classList.remove('hidden');

                            // Revenir à la vue d'affichage
                            document.getElementById('profile-edit').classList.add('hidden');
                            document.getElementById('profile-view').classList.remove('hidden');
                            document.getElementById('edit-buttons').classList.add('hidden');
                            document.getElementById('view-buttons').classList.remove('hidden');
                        } else {
                            // Afficher les erreurs
                            const feedbackDiv = document.getElementById('profile-feedback');
                            feedbackDiv.classList.remove('bg-3hcig-green-light/20', 'text-3hcig-green-dark');
                            feedbackDiv.classList.add('bg-red-50', 'text-red-800');

                            let errorMessage = 'Erreur lors de la mise à jour:';
                            if (data.errors) {
                                errorMessage += '<ul class="mt-1 list-disc pl-5">';
                                for (const error in data.errors) {
                                    errorMessage += `<li>${data.errors[error]}</li>`;
                                }
                                errorMessage += '</ul>';
                            } else {
                                errorMessage += ' ' + (data.message || 'Veuillez réessayer.');
                            }

                            document.getElementById('feedback-message').innerHTML = errorMessage;
                            feedbackDiv.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);

                        // Afficher un message d'erreur générique
                        const feedbackDiv = document.getElementById('profile-feedback');
                        feedbackDiv.classList.remove('bg-3hcig-green-light/20', 'text-3hcig-green-dark');
                        feedbackDiv.classList.add('bg-red-50', 'text-red-800');
                        document.getElementById('feedback-message').textContent = "Une erreur est survenue lors de la mise à jour du profil.";
                        feedbackDiv.classList.remove('hidden');
                    });
                }

                // Prévisualisation de l'avatar
                document.addEventListener('DOMContentLoaded', function() {
                    const avatarInput = document.getElementById('avatar');
                    const avatarPreview = document.getElementById('profile-avatar-preview');

                    if (avatarInput && avatarPreview) {
                        avatarInput.addEventListener('change', function() {
                            if (this.files && this.files[0]) {
                                const reader = new FileReader();

                                reader.onload = function(e) {
                                    avatarPreview.src = e.target.result;
                                }

                                reader.readAsDataURL(this.files[0]);
                            }
                        });
                    }

                    // Fermer le modal si on clique en dehors
                    window.addEventListener('click', function(event) {
                        const modal = document.getElementById('profile-modal');
                        if (event.target === modal) {
                            closeProfileModal();
                        }
                    });

                    // Fermer le modal avec la touche Échap
                    document.addEventListener('keydown', function(event) {
                        if (event.key === 'Escape' && !document.getElementById('profile-modal').classList.contains('hidden')) {
                            closeProfileModal();
                        }
                    });
                });
                </script>
                @endif
                <a href="{{ route('logouts') }}" class="group mt-2 flex w-full items-center rounded-md px-2 py-2 text-base font-medium text-gray-300 hover:bg-red-600 hover:text-white">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Déconnexion
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col pl-64">
            <!-- Top Navigation Bar -->
            <header class="bg-white shadow">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <h1 class="text-xl font-semibold text-3hcig-blue-dark">
                        @yield('title', config('app.name', '3HCIG COOP-CA'))
                    </h1>
                    <div class="flex items-center">
                        <!-- User Profile Menu -->
                        <div class="ml-3 relative">
                            <div>
                                <button type="button" class="relative flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="h-8 w-8 rounded-full" src="{{ $user->avatar ?? asset('storage/avatars/default.png') }}" alt="{{ $user->nom }}">
                                </button>
                            </div>
                            <div class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" id="user-dropdown-menu" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                                <a href="{{ route('user.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                    Mon Profil
                                </a>
                                <a href="{{ route('logouts') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                    Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 bg-gray-100 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white py-4 px-4 sm:px-6 lg:px-8 shadow-inner text-center text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} 3HCIG COOP-CA. Tous droits réservés.</p>
            </footer>
        </div>
    </div>
    @else
    <div class="min-h-screen bg-gray-100">
        @yield('content')
    </div>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // User profile dropdown toggle
        const userMenuButton = document.getElementById('user-menu-button');
        const userDropdownMenu = document.getElementById('user-dropdown-menu');

        if (userMenuButton && userDropdownMenu) {
            userMenuButton.addEventListener('click', function() {
                userDropdownMenu.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userMenuButton.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                    userDropdownMenu.classList.add('hidden');
                }
            });
        }

        // Mobile sidebar toggle for smaller screens - if needed
        const mobileSidebarButton = document.getElementById('mobile-sidebar-button');
        const mobileSidebar = document.getElementById('mobile-sidebar');

        if (mobileSidebarButton && mobileSidebar) {
            mobileSidebarButton.addEventListener('click', function() {
                mobileSidebar.classList.toggle('hidden');
            });
        }
    });
    </script>

    @yield('scripts')


</body>
</html>
