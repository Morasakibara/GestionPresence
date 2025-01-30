<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestion de Présence')</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <nav class="p-4 bg-gray-800">
        <div class="container mx-auto">
            <div class="flex items-center justify-between">
                <div class="text-xl text-white">
                    <a href="{{ url('/user/dashboard') }}">Gestion de Présence</a>
                </div>
                <div>
                    @auth
                        <a href="{{ route('user.profile') }}" class="mr-4 text-white">Mon Profil</a>
                        <a href="{{ route('user.presence.report') }}" class="mr-4 text-white">Bilan de Présence</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-white">Déconnexion</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-6 mx-auto">
        @yield('content')
    </div>
</body>
</html>
