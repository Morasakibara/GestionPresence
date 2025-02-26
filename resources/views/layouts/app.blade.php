<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Default Title')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navigation.css') }}">
    @yield('additional_css')
</head>
<body>
    <style>
        .nav-main {
    background-color: #1f2937;
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .nav-content {
            display: flex;
            height: 4rem;
            align-items: center;
            justify-content: space-between;
        }

        .mobile-menu-button {
            display: flex;
            align-items: center;
        }

        .mobile-menu-button button {
            padding: 0.5rem;
            color: #9ca3af;
            background-color: transparent;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
        }

        .mobile-menu-button button:hover {
            background-color: #374151;
            color: #ffffff;
        }

        .menu-icon, .close-icon {
            width: 1.5rem;
            height: 1.5rem;
        }

        .close-icon {
            display: none;
        }

        .nav-logo-menu {
            display: flex;
            align-items: center;
        }

        .nav-logo {
            flex-shrink: 0;
        }

        .logo-image {
            height: 2rem;
            width: auto;
        }

        .desktop-menu {
            display: none;
        }

        .menu-items {
            display: flex;
            space-x: 1rem;
        }

        .menu-item {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #d1d5db;
            text-decoration: none;
            border-radius: 0.375rem;
        }

        .menu-item:hover {
            background-color: #374151;
            color: #ffffff;
        }

        .menu-item.active {
            background-color: #111827;
            color: #ffffff;
        }

        .nav-right {
            display: flex;
            align-items: center;
        }


        .profile-dropdown {
            position: relative;
            margin-left: 0.75rem;
        }

        .profile-button {
            display: flex;
            background-color: #1f2937;
            border: none;
            border-radius: 9999px;
            cursor: pointer;
        }

        .profile-image {
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            z-index: 10;
            margin-top: 0.5rem;
            width: 12rem;
            background-color: #ffffff;
            border-radius: 0.375rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .dropdown-item {
            display: block;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            color: #1f2937;
            text-decoration: none;
        }

        .dropdown-item:hover {
            background-color: #f3f4f6;
        }

        .mobile-menu {
            display: none;
        }

        .mobile-menu-items {
            padding: 0.5rem 1rem;
            space-y: 0.25rem;
        }

        .mobile-menu-item {
            display: block;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            color: #d1d5db;
            text-decoration: none;
            border-radius: 0.375rem;
        }

        .mobile-menu-item:hover {
            background-color: #374151;
            color: #ffffff;
        }

        .mobile-menu-item.active {
            background-color: #111827;
            color: #ffffff;
        }

        @media (min-width: 640px) {
            .mobile-menu-button {
                display: none;
            }

            .desktop-menu {
                display: block;
                margin-left: 1.5rem;
            }

            .mobile-menu {
                display: none !important;
            }
        }
        /* Reset de base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .site-footer {
            background-color: #f8f9fa;
            color: #6c757d;
            text-align: center;
            padding: 1rem 0;
            margin-top: auto;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .site-footer p {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Media queries pour la responsivité */
        @media screen and (max-width: 600px) {
            .site-footer {
                padding: 0.75rem 0;
            }

            .site-footer p {
                font-size: 0.8rem;
            }
        }

        @media screen and (max-width: 400px) {
            .site-footer {
                padding: 0.5rem 0;
            }

            .site-footer p {
                font-size: 0.7rem;
            }
        }
    </style>
    @php
    $currentRole = session('current_role', null);
    $user = Auth::user();
    @endphp
    @if (Auth::check())
    <header>
        <nav class="nav-main">
            <div class="nav-container">
                <div class="nav-content">
                    <div class="mobile-menu-button">
                        <button type="button" aria-controls="mobile-menu" aria-expanded="false">
                            <svg class="menu-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <svg class="close-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="nav-logo-menu">
                        <div class="nav-logo">
                            <img class="logo-image" src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=500" alt="Your Company">
                        </div>
                        <div class="desktop-menu">
                            <div class="menu-items">
                                @if ($user->role === 'Superviseur')
                                    @if ($currentRole === 'Employer')
                                        <a href="{{ route('user.dashboard') }}" class="menu-item active">Tableau de bord</a>
                                        <a href="{{ route('user.profile') }}" class="menu-item">Profil</a>
                                        <a href="{{ route('presence.index') }}" class="menu-item">Présence</a>
                                        <a href="{{ route('user.presence.report') }}" class="menu-item">Bilan de présence</a>
                                    @elseif ($currentRole === 'Superviseur')
                                        <a href="{{ route('superviseur.supdashboard') }}" class="menu-item active">Tableau de bord</a>
                                        <a href="{{ route('superviseur.showFollowPresence') }}" class="menu-item">Suivre les présences</a>
                                        <a href="{{ route('superviseur.generateReport2') }}" class="menu-item">Générer un rapport</a>
                                        <a href="{{ route('superviseur.showAddMember') }}" class="menu-item">Ajouter membre à l'équipe</a>
                                    @endif
                                @elseif ($user->role === 'Employer')
                                    <a href="{{ route('user.dashboard') }}" class="menu-item active">Tableau de bord</a>
                                    <a href="{{ route('user.profile') }}" class="menu-item">Profil</a>
                                    <a href="{{ route('presence.index') }}" class="menu-item">Présence</a>
                                    <a href="{{ route('user.presence.report') }}" class="menu-item">Bilan de présence</a>
                                @elseif ($user->role === 'administrateur')
                                <a href="{{ route('admin.dashboard') }}" class="menu-item active">Tableau de bord</a>
                                <a href="{{ route('admin.addEmployee') }}" class="menu-item">Ajouter employé</a>
                                <a href="{{ route('admin.deleteEmployee') }}" class="menu-item">Supprimer employé</a>
                                <a href="{{ route('admin.generateReport') }}" class="menu-item">Générer Bilan</a>
                                <a href="{{ route('admin.showEmployeeList') }}" class="menu-item">Liste des Employés</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="nav-right">
                        <div class="profile-dropdown">
                            <button type="button" class="profile-button" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                <img class="profile-image" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                            </button>
                            <div class="dropdown-menu" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1">
                                <a href="{{ route('user.profile') }}" class="dropdown-item" role="menuitem" tabindex="-1" id="user-menu-item-0">Mon Profile</a>
                                <a href="{{ route('logouts') }}" class="dropdown-item" role="menuitem" tabindex="-1" id="user-menu-item-2">Deconnexion</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mobile-menu" id="mobile-menu">
                <div class="mobile-menu-items">
                    @if ($user->role === 'Superviseur')
                        @if ($currentRole === 'Employer')
                            <a href="{{ route('user.dashboard') }}" class="mobile-menu-item active">Tableau de bord</a>
                            <a href="{{ route('user.profile') }}" class="mobile-menu-item">Profil</a>
                            <a href="{{ route('presence.index') }}" class="mobile-menu-item">Présence</a>
                            <a href="{{ route('user.presence.report') }}" class="mobile-menu-item">Bilan de présence</a>
                        @elseif ($currentRole === 'Superviseur')
                            <a href="{{ route('superviseur.supdashboard') }}" class="mobile-menu-item active">Tableau de bord</a>
                            <a href="{{ route('superviseur.showFollowPresence') }}" class="mobile-menu-item">Suivre les présences</a>
                            <a href="{{ route('superviseur.generateReport2') }}" class="mobile-menu-item">Générer un rapport</a>
                            <a href="{{ route('superviseur.showAddMember') }}" class="mobile-menu-item">Ajouter membre à l'équipe</a>
                        @endif
                    @elseif ($user->role === 'Employer')
                        <a href="{{ route('user.dashboard') }}" class="mobile-menu-item active">Tableau de bord</a>
                        <a href="{{ route('user.profile') }}" class="mobile-menu-item">Profil</a>
                        <a href="{{ route('presence.index') }}" class="mobile-menu-item">Présence</a>
                        <a href="{{ route('user.presence.report') }}" class="mobile-menu-item">Bilan de présence</a>
                    @elseif ($user->role === 'administrateur')
                    <a href="{{ route('admin.dashboard') }}" class="mobile-menu-item active">Tableau de bord</a>
                    <a href="{{ route('admin.addEmployee') }}" class="mobile-menu-item">Ajouter employé</a>
                    <a href="{{ route('admin.deleteEmployee') }}" class="mobile-menu-item">Supprimer employé</a>
                    <a href="{{ route('admin.generateReport') }}" class="mobile-menu-item">Générer Bilan</a>
                    <a href="{{ route('admin.showEmployeeList') }}" class="mobile-menu-item">Liste des Employés</a>
                    @endif
                </div>
        </nav>
    </header>
    @endif

    <div class="container">
        @yield('content')
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
                document.addEventListener('DOMContentLoaded', function() {
            const userMenuButton = document.getElementById('user-menu-button');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            const mobileMenuButton = document.querySelector('.mobile-menu-button button');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.querySelector('.menu-icon');
            const closeIcon = document.querySelector('.close-icon');

            // Fonction pour basculer l'affichage du menu utilisateur
            function toggleUserMenu() {
                const expanded = userMenuButton.getAttribute('aria-expanded') === 'true';
                userMenuButton.setAttribute('aria-expanded', !expanded);
                dropdownMenu.style.display = expanded ? 'none' : 'block';
            }

            // Fonction pour basculer l'affichage du menu mobile
            function toggleMobileMenu() {
                const expanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
                mobileMenuButton.setAttribute('aria-expanded', !expanded);
                mobileMenu.style.display = expanded ? 'none' : 'block';
                menuIcon.style.display = expanded ? 'block' : 'none';
                closeIcon.style.display = expanded ? 'none' : 'block';
            }

            // Gestionnaire d'événements pour le clic sur le bouton du menu utilisateur
            userMenuButton.addEventListener('click', function(event) {
                event.stopPropagation();
                toggleUserMenu();
            });

            // Gestionnaire d'événements pour le clic sur le bouton du menu mobile
            mobileMenuButton.addEventListener('click', function(event) {
                event.stopPropagation();
                toggleMobileMenu();
            });

            // Fermer les menus si on clique en dehors
            document.addEventListener('click', function(event) {
                if (!userMenuButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.style.display = 'none';
                    userMenuButton.setAttribute('aria-expanded', 'false');
                }
                if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.style.display = 'none';
                    mobileMenuButton.setAttribute('aria-expanded', 'false');
                    menuIcon.style.display = 'block';
                    closeIcon.style.display = 'none';
                }
            });
        });

        </script>
    <footer class="site-footer">
        <div class="footer-content">
            <p>&copy; 2024 Timcone, Inc. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
