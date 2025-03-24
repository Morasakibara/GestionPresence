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
    <div class="flex flex-col h-full md:flex-row">
        <!-- Mobile Navbar Toggle -->
        <div class="block p-3 text-white md:hidden bg-3hcig-blue-dark">
            <button id="mobile-menu-button" class="flex items-center" aria-expanded="false" aria-controls="mobile-sidebar">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span class="ml-2">Menu</span>
            </button>
        </div>

        <!-- Sidebar pour Desktop - visible uniquement sur les écrans moyens et plus grands -->
        <div id="desktop-sidebar" class="hidden md:block fixed inset-y-0 left-0 z-50 w-64 text-white bg-3hcig-blue-dark">
            <!-- Logo in Sidebar Header -->
            <div class="flex items-center justify-center h-16 border-b border-3hcig-blue">
                <img class="w-auto h-20" src="{{ asset('/storage/avatars/logo-3HCIG.png') }}" alt="3HCIG COOP-CA">
            </div>

            <!-- Nav Links pour Desktop -->
            <nav class="flex flex-col flex-1 px-2 mt-5 overflow-y-auto h-[calc(100%-144px)]">
                @if ($user->role === 'Superviseur' && $currentRole === 'Employer')
                <a href="{{ route('user.dashboard') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('user.dashboard') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('user.dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Tableau de bord
                </a>
                <!-- Autres liens pour Superviseur en mode Employer -->
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
                @elseif ($user->role === 'Superviseur' && $currentRole === 'Superviseur')
                <!-- Liens pour Superviseur en mode Superviseur -->
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
                <a href="{{ route('notifications.index') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('notifications.index') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }} relative">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('notifications.index') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="absolute flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full -top-1 -right-1">
                            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                @elseif ($user->role === 'Employer')
                <!-- Liens pour Employer -->
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
                <a href="{{ route('notifications.index') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('notifications.index') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }} relative">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('notifications.index') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="absolute flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full -top-1 -right-1">
                            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                @elseif ($user->role === 'administrateur')
                <!-- Liens pour Administrateur -->
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
                <a href="{{ route('admin.workplace-locations.index') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('admin.workplace-locations.*') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('admin.workplace-locations.*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Lieux de travail
                </a>
                <a href="{{ route('notifications.index') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('notifications.index') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }} relative">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('notifications.index') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="absolute flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full -top-1 -right-1">
                            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                @endif
            </nav>

            <!-- Settings at bottom pour Desktop -->
            <div class="bottom-0 w-full p-4 mt-auto border-t border-3hcig-blue">
                @if(Auth::check() && $user->role === 'Superviseur')
                <a href="{{ route('role.switch') }}" class="flex items-center w-full px-2 py-2 text-base font-medium text-gray-300 rounded-md group hover:bg-3hcig-blue hover:text-white">
                    <svg class="flex-shrink-0 w-6 h-6 mr-3 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Changer de rôle
                </a>
                @endif
                <a href="{{ route('logouts') }}" class="flex items-center w-full px-2 py-2 mt-2 text-base font-medium text-gray-300 rounded-md group hover:bg-red-600 hover:text-white">
                    <svg class="flex-shrink-0 w-6 h-6 mr-3 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Déconnexion
                </a>
            </div>
        </div>

        <!-- Sidebar pour Mobile - visible uniquement sur les petits écrans -->
        <div id="mobile-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 text-white transition-transform duration-300 ease-in-out transform -translate-x-full bg-3hcig-blue-dark md:hidden">
            <!-- Logo in Sidebar Header -->
            <div class="flex items-center justify-center h-16 border-b border-3hcig-blue">
                <img class="w-auto h-20" src="{{ asset('/storage/avatars/logo-3HCIG.png') }}" alt="3HCIG COOP-CA">
            </div>

            <!-- Nav Links pour Mobile -->
            <nav class="flex flex-col flex-1 px-2 mt-5 overflow-y-auto h-[calc(100%-144px)]">
                @if ($user->role === 'Superviseur' && $currentRole === 'Employer')
                <a href="{{ route('user.dashboard') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('user.dashboard') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('user.dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Tableau de bord
                </a>
                <!-- Autres liens pour Superviseur en mode Employer (mobile) -->
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
                @elseif ($user->role === 'Superviseur' && $currentRole === 'Superviseur')
                <!-- Liens pour Superviseur en mode Superviseur (mobile) -->
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
                <a href="{{ route('notifications.index') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('notifications.index') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }} relative">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('notifications.index') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="absolute flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full -top-1 -right-1">
                            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                @elseif ($user->role === 'Employer')
                <!-- Liens pour Employer (mobile) -->
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
                <a href="{{ route('notifications.index') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('notifications.index') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }} relative">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('notifications.index') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="absolute flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full -top-1 -right-1">
                            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                @elseif ($user->role === 'administrateur')
                <!-- Liens pour Administrateur (mobile) -->
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
                <a href="{{ route('admin.workplace-locations.index') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('admin.workplace-locations.*') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }}">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('admin.workplace-locations.*') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Lieux de travail
                </a>
                <a href="{{ route('notifications.index') }}" class="group mt-1 flex items-center rounded-md px-2 py-2 text-base font-medium {{ request()->routeIs('notifications.index') ? 'bg-3hcig-blue text-white' : 'text-gray-300 hover:bg-3hcig-blue hover:text-white' }} relative">
                    <svg class="mr-3 h-6 w-6 flex-shrink-0 {{ request()->routeIs('notifications.index') ? 'text-white' : 'text-gray-400 group-hover:text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Notifications
                    @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="absolute flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full -top-1 -right-1">
                            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                @endif
            </nav>

            <!-- Settings at bottom pour Mobile -->
            <div class="absolute bottom-0 w-full p-4 border-t border-3hcig-blue">
                @if(Auth::check() && $user->role === 'Superviseur')
                <a href="{{ route('role.switch') }}" class="flex items-center w-full px-2 py-2 text-base font-medium text-gray-300 rounded-md group hover:bg-3hcig-blue hover:text-white">
                    <svg class="flex-shrink-0 w-6 h-6 mr-3 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Changer de rôle
                </a>
                @endif
                <a href="{{ route('logouts') }}" class="flex items-center w-full px-2 py-2 mt-2 text-base font-medium text-gray-300 rounded-md group hover:bg-red-600 hover:text-white">
                    <svg class="flex-shrink-0 w-6 h-6 mr-3 text-gray-400 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Déconnexion
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 md:pl-64">
            <!-- Top Navigation Bar -->
            <header class="bg-white shadow">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-xl font-semibold text-3hcig-blue-dark">
                        @yield('title', config('app.name', '3HCIG COOP-CA'))
                    </h1>
                    <div class="flex items-center">
                        <!-- Notification Icon -->
                        <a href="{{ route('notifications.index') }}" class="relative mr-4">
                            <svg class="w-6 h-6 text-gray-500 hover:text-3hcig-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                                <span class="absolute flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-red-600 rounded-full -top-1 -right-1">
                                    {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>

                        <!-- User Profile Menu -->
                        <div class="relative ml-3">
                            <div>
                                <button type="button" class="relative flex items-center max-w-xs text-sm bg-white rounded-full focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2"
                                        id="user-menu-button"
                                        aria-expanded="false"
                                        aria-haspopup="true"
                                        aria-controls="user-dropdown-menu">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="w-8 h-8 rounded-full" src="{{ Auth::user()->avatar ? asset('storage/avatars/' . Auth::user()->avatar) : asset('storage/avatars/default.png') }}" alt="{{ $user->nom }}">
                                </button>
                            </div>
                            <div class="absolute right-0 z-10 hidden w-48 py-1 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" id="user-dropdown-menu" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                                @if(Auth::check() && Auth::user()->role === 'administrateur')
                                <a href="{{route('admin.showProfile')}}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                    Votre Profil
                                </a>
                                @else
                                <a href="{{ route('user.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                    Mon Profil
                                </a>
                                @endif
                                <a href="{{ route('notifications.index') }}" class="relative block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">
                                    Notifications
                                    @if(Auth::user()->unreadNotifications->count() > 0)
                                        <span class="inline-flex items-center justify-center w-4 h-4 ml-2 text-xs font-bold text-white bg-red-600 rounded-full">
                                            {{ Auth::user()->unreadNotifications->count() }}
                                        </span>
                                    @endif
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
            <main class="flex-1 p-0 overflow-x-hidden bg-gray-100">
                <div class="container mx-auto">
                    @yield('content')
                </div>
            </main>

            <!-- Footer -->
            <footer class="px-4 py-4 text-sm text-center text-gray-500 bg-white shadow-inner sm:px-6 lg:px-8">
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
                const isExpanded = !userDropdownMenu.classList.contains('hidden');
                userDropdownMenu.classList.toggle('hidden');
                userMenuButton.setAttribute('aria-expanded', !isExpanded);
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userMenuButton.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                    userDropdownMenu.classList.add('hidden');
                    userMenuButton.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // Mobile sidebar toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileSidebar = document.getElementById('mobile-sidebar');

        if (mobileMenuButton && mobileSidebar) {
            mobileMenuButton.addEventListener('click', function() {
                const isExpanded = !mobileSidebar.classList.contains('-translate-x-full');
                mobileSidebar.classList.toggle('-translate-x-full');
                mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 768 &&
                    !mobileMenuButton.contains(event.target) &&
                    !mobileSidebar.contains(event.target) &&
                    !mobileSidebar.classList.contains('-translate-x-full')) {
                    mobileSidebar.classList.add('-translate-x-full');
                    mobileMenuButton.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // Handle window resize event to ensure proper sidebar visibility
        window.addEventListener('resize', function() {
            if (mobileSidebar && window.innerWidth >= 768) {
                mobileSidebar.classList.add('-translate-x-full');
                if (mobileMenuButton) {
                    mobileMenuButton.setAttribute('aria-expanded', 'false');
                }
            }
        });
    });
    </script>

    @yield('scripts')

</body>
</html>
