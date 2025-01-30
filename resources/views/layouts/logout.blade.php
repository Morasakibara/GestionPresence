<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Default Title')</title>
    <!-- Inclure vos fichiers CSS ici -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminDashboard.css') }}" >
</head>
<body>
    
   

    
    <div class="container">
        @yield('content')
    </div>
    <!-- Inclure vos fichiers JS ici -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
