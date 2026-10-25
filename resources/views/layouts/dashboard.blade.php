<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Le Pharaon'))</title>

    @vite('resources/css/app.css')

    {{-- Applique le thème enregistré avant le rendu pour éviter tout clignotement. --}}
    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
        })();
    </script>
</head>
<body class="relative min-h-full bg-[#F4F4F2] antialiased">
@php
    $account = auth()->user();
    $prefix = explode('/', trim(request()->path(), '/'))[0] ?? '';

    // Menu unique par rôle : toutes les fonctionnalités, état actif selon la route.
    $navRole = match (true) {
        $account->role === 'Administrateur' => 'admin',
        $account->role === 'Superviseur' && in_array($prefix, ['directrice', 'secretaire', 'gestionnaire'], true) => $prefix,
        $account->role === 'Superviseur' && session('current_role') === 'Employer' => 'user',
        $account->role === 'Superviseur' => 'superviseur',
        default => 'user',
    };

    $roleMeta = [
        'admin' => ['label' => 'Administrateur', 'home' => 'admin.dashboard'],
        'superviseur' => ['label' => 'Superviseur', 'home' => 'superviseur.supdashboard'],
        'directrice' => ['label' => 'Directrice', 'home' => 'directrice.dashboard'],
        'secretaire' => ['label' => 'Secrétaire', 'home' => 'secretaire.dashboard'],
        'gestionnaire' => ['label' => 'Gestionnaire de stock', 'home' => 'gestionnaire.dashboard'],
        'user' => ['label' => 'Employé', 'home' => 'user.dashboard'],
    ];
    $currentRole = $roleMeta[$navRole];
    $isHomeRoute = request()->routeIs($currentRole['home']);
    $homeRoute = route($currentRole['home']);
    $unreadCount = $account->unreadNotifications->count();
    $hideSound = false;
    $contentShell = trim($__env->yieldContent('content_shell'));
    $avatarPath = $account->avatar
        ? asset('storage/avatars/' . $account->avatar)
        : asset('storage/avatars/default.png');
@endphp

<!-- Arrière-plan commun à toutes les consoles -->
<div class="fixed inset-0 z-[-10]">
    <img src="{{ asset('storage/avatars/userBackground.jpg') }}" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full object-cover">
    <div class="console-veil absolute inset-0"></div>
</div>

<div class="relative z-[1] min-h-screen">
    <!-- Voile de fermeture du menu mobile -->
    <div id="consoleBackdrop" class="fixed inset-0 z-40 hidden bg-[#080808]/60 backdrop-blur-sm lg:hidden"></div>

    <!-- ══════════════ MENU LATÉRAL ══════════════ -->
    <aside id="consoleSidebar"
           class="console-sidebar -translate-x-full transition-transform duration-200 ease-out lg:translate-x-0"
           aria-label="Navigation principale">
        <div class="flex items-start justify-between gap-2 px-4 pb-3 pt-4 sm:pt-5">
            <a href="{{ $homeRoute }}" class="console-brand-plate" aria-label="Le Pharaon — Accueil">
                <img src="{{ asset('storage/avatars/logo-pharaon.png') }}" alt="Le Pharaon" width="606" height="610" class="h-auto w-full" decoding="async">
            </a>
            <button type="button" id="consoleSidebarClose"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/50 transition-colors hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-pharaoh-gold/60 lg:hidden"
                    aria-label="Fermer le menu">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="px-4 pb-1">
            <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.18em] text-pharaoh-gold">{{ $currentRole['label'] }}</p>
            <p class="mt-1 truncate text-sm font-semibold text-white">{{ $account->nom }}</p>
            <p class="truncate text-xs text-white/40">{{ $account->email }}</p>
        </div>

        <nav class="console-nav-scroll" aria-label="Menu {{ $currentRole['label'] }}">
            {{-- Menu de rôle unique (toutes les fonctionnalités, état actif) --}}
            @include('partials.nav.' . $navRole)
        </nav>

        <div class="border-t border-white/10 p-3">
            <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                <img src="{{ $avatarPath }}" data-nav-avatar alt="{{ $account->nom }}"
                     class="h-9 w-9 flex-shrink-0 rounded-full object-cover ring-2 ring-pharaoh-gold/40">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ $account->nom }}</p>
                    <p class="truncate text-xs text-white/40">{{ $currentRole['label'] }}</p>
                </div>
                <a href="{{ route('logouts') }}" title="Déconnexion" aria-label="Déconnexion"
                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/50 transition-colors hover:bg-white/10 hover:text-pharaoh-gold-light focus:outline-none focus-visible:ring-2 focus-visible:ring-pharaoh-gold/60">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 8.5V6a1.5 1.5 0 0 0-1.5-1.5h-7A1.5 1.5 0 0 0 5 6v12a1.5 1.5 0 0 0 1.5 1.5h7A1.5 1.5 0 0 0 15 18v-2.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 12H20M17.5 9.5 20 12l-2.5 2.5" />
                    </svg>
                </a>
            </div>

            @if($account->role === 'Superviseur')
                <a href="{{ route('role.switch') }}"
                   class="mt-1 flex items-center gap-2 rounded-lg px-2 py-2 text-xs font-medium text-white/45 transition-colors hover:bg-white/5 hover:text-pharaoh-gold-light focus:outline-none focus-visible:ring-2 focus-visible:ring-pharaoh-gold/60">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 4.5h9M7.5 19.5h9M12 4.5v15M4.5 7.5l-1.5 3h3zM19.5 7.5l-1.5 3h3z" />
                    </svg>
                    Changer de rôle
                </a>
            @endif
        </div>
    </aside>

    <!-- ══════════════ ZONE PRINCIPALE ══════════════ -->
    <div class="console-main flex min-h-screen flex-col">
        <!-- Barre supérieure -->
        <header class="console-topbar">
            <div class="flex h-14 items-center justify-between gap-2 px-3 sm:h-16 sm:gap-3 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                    <button type="button" id="consoleSidebarToggle"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 transition-colors hover:border-pharaoh-gold/50 hover:text-[#080808] focus:outline-none focus-visible:ring-2 focus-visible:ring-pharaoh-gold/60 lg:hidden"
                            aria-controls="consoleSidebar" aria-expanded="false" aria-label="Ouvrir le menu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    @unless($isHomeRoute)
                        <button type="button"
                                onclick="if (history.length > 1) { history.back(); } else { window.location.href = '{{ $homeRoute }}'; }"
                                class="hidden h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition-colors hover:bg-gray-100 hover:text-[#080808] focus:outline-none focus-visible:ring-2 focus-visible:ring-pharaoh-gold/60 md:inline-flex"
                                title="Revenir en arrière" aria-label="Revenir en arrière">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                        </button>
                    @endunless

                    <nav class="console-eyebrow min-w-0" aria-label="Fil d'Ariane">
                        <a href="{{ $homeRoute }}" class="transition-colors hover:text-[#080808]">Le Pharaon</a>
                        <svg class="h-3 w-3 flex-shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="truncate text-[#080808]">@yield('breadcrumb', $currentRole['label'])</span>
                    </nav>
                </div>

                <div class="flex items-center gap-1 sm:gap-2">
                    <!-- Son des alertes (masqué pour la caisse) -->
                    @if(!$hideSound)
                        <button type="button" id="soundToggle" onclick="AlertSound.toggle()"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition-colors hover:bg-gray-100 hover:text-[#080808] focus:outline-none focus-visible:ring-2 focus-visible:ring-pharaoh-gold/60"
                                title="Activer / désactiver le son" aria-label="Activer ou désactiver le son">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            </svg>
                        </button>
                    @endif

                    <!-- Thème clair / sombre -->
                    <button type="button" id="darkModeToggle" onclick="toggleDarkMode()"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition-colors hover:bg-gray-100 hover:text-[#080808] focus:outline-none focus-visible:ring-2 focus-visible:ring-pharaoh-gold/60"
                            title="Mode sombre" aria-label="Basculer le thème">
                        <svg id="sunIcon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg id="moonIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Notifications -->
                    <a href="{{ route('notifications.index') }}" title="Notifications" aria-label="Notifications"
                       class="relative inline-flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition-colors hover:bg-gray-100 hover:text-[#080808] focus:outline-none focus-visible:ring-2 focus-visible:ring-pharaoh-gold/60">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 15.5V11a6 6 0 10-12 0v4.5L4.5 18h15z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 18a2.25 2.25 0 004.5 0" />
                        </svg>
                        <span class="notif-badge-count absolute right-0.5 top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-600 px-1 text-[0.625rem] font-bold text-white"
                              data-notif-count="{{ $unreadCount }}"
                              style="display: {{ $unreadCount > 0 ? 'flex' : 'none' }}">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    </a>

                    <!-- Menu du compte -->
                    <div class="relative ml-1">
                        <button type="button" id="user-menu-button" aria-expanded="false" aria-haspopup="true"
                                class="profile-button flex items-center gap-2 rounded-xl py-1 pl-1 pr-1 transition-colors hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-pharaoh-gold/60 sm:pr-2">
                            <span class="sr-only">Ouvrir le menu utilisateur</span>
                            <img src="{{ $avatarPath }}" data-nav-avatar alt="{{ $account->nom }}" class="h-8 w-8 rounded-full object-cover ring-2 ring-pharaoh-gold/30">
                            <span class="hidden text-sm font-semibold text-[#080808] sm:block">{{ $account->nom }}</span>
                            <svg class="hidden h-4 w-4 text-gray-400 sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div id="profile-dropdown"
                             class="profile-dropdown absolute right-0 z-50 mt-2 hidden w-56 origin-top-right rounded-xl border border-gray-100 bg-white py-1 shadow-xl focus:outline-none"
                             role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                            <div class="border-b border-gray-100 px-4 py-3">
                                <p class="truncate text-sm font-semibold text-[#080808]">{{ $account->nom }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $account->email }}</p>
                            </div>

                            @if($account->role === 'Administrateur')
                                <a href="#" onclick="openProfileModal()" id="user-profile-button"
                                   class="block px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-[#FBF3E6] hover:text-[#080808]" role="menuitem" tabindex="-1">
                                    Votre Profil
                                </a>
                            @endif

                            <a href="{{ route('pin.settings') }}" role="menuitem" tabindex="-1"
                               class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-[#FBF3E6] hover:text-[#080808]">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Code PIN
                            </a>

                            @if($account->role === 'Superviseur')
                                <a href="{{ route('role.switch') }}" role="menuitem" tabindex="-1"
                                   class="block px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-[#FBF3E6] hover:text-[#080808]">
                                    Changer de rôle
                                </a>
                            @endif

                            <a href="{{ route('logouts') }}" role="menuitem" tabindex="-1"
                               class="mt-1 flex items-center gap-2 border-t border-gray-100 px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 8.5V6a1.5 1.5 0 0 0-1.5-1.5h-7A1.5 1.5 0 0 0 5 6v12a1.5 1.5 0 0 0 1.5 1.5h7A1.5 1.5 0 0 0 15 18v-2.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 12H20M17.5 9.5 20 12l-2.5 2.5" />
                                </svg>
                                Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        @if($account->role === 'Administrateur')
            @include('partials.dashboard.profile-modal')
        @endif

        <!-- Titre de page + onglets -->
        <div class="px-3 pt-4 sm:px-6 sm:pt-6 lg:px-8">
            @hasSection('header')
                <div class="console-heading flex w-full flex-wrap items-center justify-between gap-3">
                    @yield('header')
                </div>
            @else
                <div class="console-heading">
                    <div class="flex w-full flex-wrap items-center justify-between gap-3">
                        <span>{{ $currentRole['label'] }}</span>
                    </div>
                </div>
            @endif

            @hasSection('tabs')
                <div class="console-tabs mt-4 sm:mt-5" data-console-tabs>
                    @yield('tabs')
                </div>
            @endif
        </div>

        <!-- Contenu -->
        <main class="flex-1 px-3 pb-12 pt-4 sm:px-6 sm:pt-5 lg:px-8">
            @if (session('success'))
                <div class="mb-5 rounded-xl bg-pharaoh-green-light/20 px-4 py-3 text-sm text-pharaoh-green-dark">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">
                    {{ session('error') }}
                </div>
            @endif

            @if ($contentShell === 'flush')
                @yield('content')
            @else
                <div class="rounded-3xl border border-white/70 bg-white/90 p-4 shadow-card backdrop-blur-sm sm:p-6">
                    @yield('content')
                </div>
            @endif
        </main>
    </div>
</div>

<!-- Notifications temps réel -->
<script>window.__notifSoundArmed = @json((bool) session()->pull('notif_sound_armed', false));</script>
<script src="{{ asset('js/notifications.js') }}"></script>

<script>
    // Menu latéral mobile
    (function () {
        const sidebar = document.getElementById('consoleSidebar');
        const backdrop = document.getElementById('consoleBackdrop');
        const toggle = document.getElementById('consoleSidebarToggle');
        const close = document.getElementById('consoleSidebarClose');

        if (!sidebar || !backdrop || !toggle) return;

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
            document.body.classList.add('overflow-hidden');
            sidebar.querySelector('a, button')?.focus();
        }

        function closeSidebar(restoreFocus) {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('overflow-hidden');
            if (restoreFocus) toggle.focus();
        }

        toggle.addEventListener('click', openSidebar);
        backdrop.addEventListener('click', () => closeSidebar(true));
        close?.addEventListener('click', () => closeSidebar(true));

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !backdrop.classList.contains('hidden')) {
                closeSidebar(true);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024 && !backdrop.classList.contains('hidden')) {
                closeSidebar(false);
            }
        });
    })();

    // Menu du compte
    document.addEventListener('DOMContentLoaded', function () {
        const profileButton = document.querySelector('.profile-button');
        const profileDropdown = document.querySelector('.profile-dropdown');

        if (profileButton && profileDropdown) {
            profileButton.addEventListener('click', function () {
                profileDropdown.classList.toggle('hidden');
                profileButton.setAttribute('aria-expanded', profileDropdown.classList.contains('hidden') ? 'false' : 'true');
            });

            document.addEventListener('click', function (event) {
                if (!profileButton.contains(event.target) && !profileDropdown.contains(event.target)) {
                    profileDropdown.classList.add('hidden');
                    profileButton.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !profileDropdown.classList.contains('hidden')) {
                    profileDropdown.classList.add('hidden');
                    profileButton.setAttribute('aria-expanded', 'false');
                    profileButton.focus();
                }
            });
        }

        // Onglets de la console : l'onglet actif est porté par `.is-active`.
        const tabs = document.querySelector('[data-console-tabs]');
        if (tabs) {
            tabs.addEventListener('click', function (event) {
                const tab = event.target.closest('.console-tab');
                if (!tab || !tabs.contains(tab)) return;
                tabs.querySelectorAll('.console-tab').forEach(el => {
                    el.classList.remove('is-active');
                    el.removeAttribute('aria-current');
                });
                tab.classList.add('is-active');
                tab.setAttribute('aria-current', 'page');
            });
        }
    });
</script>

<!-- Thème clair / sombre -->
<script>
    function toggleDarkMode() {
        const html = document.documentElement;
        const isDark = html.getAttribute('data-theme') === 'dark';
        html.setAttribute('data-theme', isDark ? 'light' : 'dark');
        localStorage.setItem('theme', isDark ? 'light' : 'dark');
        document.getElementById('sunIcon').classList.toggle('hidden', !isDark);
        document.getElementById('moonIcon').classList.toggle('hidden', isDark);
    }

    (function () {
        if (document.documentElement.getAttribute('data-theme') === 'dark') {
            document.getElementById('sunIcon')?.classList.remove('hidden');
            document.getElementById('moonIcon')?.classList.add('hidden');
        }
    })();
</script>

<!-- Alertes de présence temps réel -->
<link rel="stylesheet" href="{{ asset('css/presence-toasts.css') }}">
<script src="{{ asset('js/alert-sound.js') }}"></script>
<script src="{{ asset('js/presence-alerts.js') }}"></script>

<!-- Additional Scripts -->
@stack('scripts')

<!-- ══════════════ MODAL RÉCUPÉRATION PIN ══════════════ -->
<div id="pinModal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="relative mx-4 w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl" onclick="event.stopPropagation()">
        <div class="bg-[#080808] px-6 py-8 text-center">
            <img class="mx-auto mb-4 h-16 w-auto drop-shadow-[0_2px_8px_rgba(211,155,35,0.35)]" src="{{ asset('storage/avatars/logo-pharaon.png') }}" alt="Le Pharaon">
            <h2 class="text-xl font-bold text-white">Session expirée</h2>
            <p class="mt-1 text-sm text-gray-400">Entrez votre code PIN pour récupérer votre session</p>
        </div>

        <div class="px-6 py-6">
            <div id="pinError" class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600"></div>
            <div id="pinSuccess" class="mb-4 hidden rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-600"></div>

            <form id="pinForm" class="space-y-5">
                <div>
                    <label for="pinInput" class="mb-2 block text-sm font-medium text-gray-700">Code PIN (4 chiffres)</label>
                    <div class="flex justify-center gap-3">
                        <input type="password" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off" aria-label="Chiffre 1"
                               class="pin-digit h-14 w-14 rounded-xl border-2 border-gray-300 text-center text-2xl font-bold transition-colors focus:border-pharaoh-gold focus:outline-none focus:ring-2 focus:ring-pharaoh-gold/30"
                               data-index="0">
                        <input type="password" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off" aria-label="Chiffre 2"
                               class="pin-digit h-14 w-14 rounded-xl border-2 border-gray-300 text-center text-2xl font-bold transition-colors focus:border-pharaoh-gold focus:outline-none focus:ring-2 focus:ring-pharaoh-gold/30"
                               data-index="1">
                        <input type="password" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off" aria-label="Chiffre 3"
                               class="pin-digit h-14 w-14 rounded-xl border-2 border-gray-300 text-center text-2xl font-bold transition-colors focus:border-pharaoh-gold focus:outline-none focus:ring-2 focus:ring-pharaoh-gold/30"
                               data-index="2">
                        <input type="password" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off" aria-label="Chiffre 4"
                               class="pin-digit h-14 w-14 rounded-xl border-2 border-gray-300 text-center text-2xl font-bold transition-colors focus:border-pharaoh-gold focus:outline-none focus:ring-2 focus:ring-pharaoh-gold/30"
                               data-index="3">
                    </div>
                </div>

                <button type="submit" id="pinSubmit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-pharaoh-gold px-4 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-200 hover:bg-pharaoh-gold-light hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-pharaoh-gold focus:ring-offset-2">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Déverrouiller
                </button>
            </form>

            <div class="relative mt-5">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                <div class="relative flex justify-center text-sm"><span class="bg-white px-2 text-gray-400">ou</span></div>
            </div>

            <div class="mt-5 text-center">
                <a href="{{ route('logouts') }}" id="pinLogoutLink"
                   class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition-colors hover:text-pharaoh-gold">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Se connecter avec email & mot de passe
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const pinModal = document.getElementById('pinModal');
        if (!pinModal) return;

        fetch('{{ route('pin.status') }}', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(data => {
                if (data.pin_required) {
                    pinModal.classList.remove('hidden');
                    pinModal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                    document.querySelector('.pin-digit[data-index="0"]')?.focus();
                }
            })
            .catch(() => {});

        const digits = document.querySelectorAll('.pin-digit');
        digits.forEach((input, idx) => {
            input.addEventListener('input', function () {
                const val = this.value.replace(/[^0-9]/g, '');
                this.value = val;
                if (val && idx < digits.length - 1) {
                    digits[idx + 1].focus();
                }
                checkAllFilled();
            });
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && idx > 0) {
                    digits[idx - 1].focus();
                    digits[idx - 1].value = '';
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('pinForm').dispatchEvent(new Event('submit'));
                }
            });
            input.addEventListener('click', function () { this.select(); });
        });

        function checkAllFilled() {
            let code = '';
            digits.forEach(d => code += d.value);
            if (code.length === 4) {
                submitPin(code);
            }
        }

        function submitPin(code) {
            const errDiv = document.getElementById('pinError');
            const succDiv = document.getElementById('pinSuccess');
            const btn = document.getElementById('pinSubmit');
            errDiv.classList.add('hidden');
            succDiv.classList.add('hidden');
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Vérification...';

            fetch('{{ route('pin.verify') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ pin: code })
            })
            .then(r => r.json().then(j => ({ ok: r.ok, data: j })))
            .then(({ ok, data }) => {
                if (ok && data.success) {
                    succDiv.textContent = '✅ Session restaurée ! Redirection...';
                    succDiv.classList.remove('hidden');
                    setTimeout(() => {
                        window.location.href = data.redirect || window.location.href;
                    }, 800);
                } else {
                    errDiv.textContent = data.error || 'Code PIN incorrect.';
                    errDiv.classList.remove('hidden');
                    digits.forEach(d => { d.value = ''; });
                    digits[0].focus();
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg> Déverrouiller';
                }
            })
            .catch(() => {
                errDiv.textContent = 'Erreur réseau. Vérifiez votre connexion.';
                errDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg> Déverrouiller';
            });
        }

        document.getElementById('pinForm').addEventListener('submit', function (e) {
            e.preventDefault();
            let code = '';
            digits.forEach(d => code += d.value);
            if (code.length === 4) submitPin(code);
        });
    })();
</script>

<script>
    // Désactiver molette et touches direction sur les inputs de prix/montant
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[name*="montant"], input[name*="prix"], input[type="number"].no-spin').forEach(function (input) {
            input.addEventListener('wheel', function (e) { e.preventDefault(); }, { passive: false });
            input.addEventListener('keydown', function (e) {
                if (['ArrowUp', 'ArrowDown'].includes(e.key)) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
</body>
</html>
