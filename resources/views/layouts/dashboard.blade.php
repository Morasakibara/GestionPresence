<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', '3HCIG COOP-CA') }}</title>

    <!-- Styles -->
    @vite('resources/css/app.css')
</head>
<body class="h-full antialiased">
    <div class="min-h-full">
        <nav class="bg-3hcig-blue-dark">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <img class="h-20 w-auto" src="{{ asset('/storage/avatars/logo-3HCIG.png') }}" alt="3HCIG COOP-CA">
                        </div>
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                @yield('navigation')
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-4 flex items-center md:ml-6">
                            <!-- Profile dropdown -->
                            <div class="relative ml-3">
                                <div>
                                    <button type="button" class="profile-button relative flex max-w-xs items-center rounded-full bg-3hcig-blue-dark text-sm focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-3hcig-blue focus:outline-none" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                        <span class="absolute -inset-1.5"></span>
                                        <span class="sr-only">Open user menu</span>
                                        <img class="h-8 w-8 rounded-full" src="{{ $user->avatar ?? asset('storage/avatars/default.png') }}" alt="{{ auth()->user()->nom }}">
                                    </button>
                                </div>

                                <!-- Dropdown menu -->
                                <div id="profile-dropdown" class="profile-dropdown hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Votre Profil</a>
                                    @if(Auth::check() && Auth::user()->role === 'Superviseur')
                                        <a href="{{ route('role.switch') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Changer de rôle</a>
                                    @endif
                                    <a href="{{ route('logouts') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1">Déconnexion</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="-mr-2 flex md:hidden">
                        <!-- Mobile menu button -->
                        <button type="button" id="mobile-menu-button" class="mobile-menu-button relative inline-flex items-center justify-center rounded-md bg-3hcig-blue-dark p-2 text-gray-400 hover:bg-3hcig-blue hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-3hcig-blue-dark" aria-controls="mobile-menu" aria-expanded="false">
                            <span class="absolute -inset-0.5"></span>
                            <span class="sr-only">Open main menu</span>
                            <!-- Menu open: "hidden", Menu closed: "block" -->
                            <svg class="menu-icon-closed block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <!-- Menu open: "block", Menu closed: "hidden" -->
                            <svg class="menu-icon-open hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state. -->
            <div class="mobile-menu hidden md:hidden" id="mobile-menu">
                <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
                    @yield('mobile-navigation')
                </div>
                <div class="border-t border-3hcig-blue pt-4 pb-3">
                    <div class="flex items-center px-5">
                        <div class="shrink-0">
                            <img class="h-10 w-10 rounded-full" src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->nom }}">
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-white">{{ auth()->user()->nom }}</div>
                            <div class="text-sm font-medium text-gray-400">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1 px-2">
                        <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-3hcig-blue hover:text-white">Votre Profil</a>
                        @if(Auth::check() && Auth::user()->role === 'Superviseur')
                            <a href="{{ route('role.switch') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-3hcig-blue hover:text-white">Changer de rôle</a>
                        @endif
                        <a href="{{ route('logouts') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-3hcig-blue hover:text-white">Déconnexion</a>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-3hcig-blue-dark">@yield('header')</h1>
            </div>
        </header>

        <main>
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                @if (session('success'))
                <div class="mb-6 rounded-md bg-3hcig-green-light/20 p-4 text-3hcig-green-dark">
                    {{ session('success') }}
                </div>
                @endif

                @if (session('error'))
                <div class="mb-6 rounded-md bg-red-50 p-4 text-red-600">
                    {{ session('error') }}
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle user dropdown menu
        const profileButton = document.querySelector('.profile-button');
        const profileDropdown = document.querySelector('.profile-dropdown');

        if (profileButton && profileDropdown) {
            profileButton.addEventListener('click', function() {
                profileDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!profileButton.contains(event.target) && !profileDropdown.contains(event.target)) {
                    profileDropdown.classList.add('hidden');
                }
            });
        }

        // Mobile menu toggle
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenu = document.querySelector('.mobile-menu');
        const menuIconClosed = document.querySelector('.menu-icon-closed');
        const menuIconOpen = document.querySelector('.menu-icon-open');

        if (mobileMenuButton && mobileMenu && menuIconClosed && menuIconOpen) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                menuIconClosed.classList.toggle('hidden');
                menuIconOpen.classList.toggle('hidden');
            });
        }
    });
    </script>

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
