@extends('layouts.logout')

@section('content')

<style>
  /* Styles de base et réinitialisation */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  background-color: #f3f4f6;
  color: #1a202c;
  line-height: 1.5;
}

.container {
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  padding: 1rem;
}

/* Navigation */
.nav {
  background-color: #1f2937;
  padding: 1rem 0;
}

.nav-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-logo img {
  height: 2rem;
  width: auto;
}

.nav-links {
  display: none;
}

.nav-links a {
  color: #d1d5db;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  transition: background-color 0.3s, color 0.3s;
}

.nav-links a:hover,
.nav-links a.active {
  background-color: #374151;
  color: #ffffff;
}

.nav-profile {
  position: relative;
}

.nav-profile-img {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  cursor: pointer;
}

.nav-profile-dropdown {
  display: none;
  position: absolute;
  right: 0;
  top: 100%;
  background-color: #ffffff;
  border-radius: 0.375rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.nav-profile-dropdown a {
  display: block;
  padding: 0.5rem 1rem;
  color: #4b5563;
  text-decoration: none;
  transition: background-color 0.3s;
}

.nav-profile-dropdown a:hover {
  background-color: #f3f4f6;
}

/* Contenu principal */
.main-content {
  padding-top: 2rem;
}

h1 {
  font-size: 2.25rem;
  font-weight: bold;
  color: #111827;
  margin-bottom: 1.5rem;
}

h2 {
  font-size: 1.5rem;
  font-weight: bold;
  color: #374151;
  margin-bottom: 1rem;
}

/* Grille */
.row {
  display: flex;
  flex-wrap: wrap;
  margin: -1rem;
}

.col-md-6 {
  width: 100%;
  padding: 1rem;
}

/* Boutons */
.btn {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  text-align: center;
  text-decoration: none;
  transition: background-color 0.3s, opacity 0.3s;
  border: none;
  cursor: pointer;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background-color: #3b82f6;
  color: #ffffff;
}

.btn-primary:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn-secondary {
  background-color: #6b7280;
  color: #ffffff;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #4b5563;
}

.mt-3 {
  margin-top: 1rem;
}



/* Responsive */
@media (min-width: 768px) {
  .container {
    padding: 2rem;
  }

  .nav-links {
    display: flex;
  }

  .nav-profile-img {
    width: 2rem;
    height: 2rem;
  }

  .col-md-6 {
    width: 50%;
  }
}
</style>
<nav class="nav">
    <div class="container nav-content">
        <div class="nav-logo">
          <img class="logo-image" src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=500" alt="Your Company">
        <div class="nav-links">
            <a href="#" class="active">Tableau de bord</a>
            <a href="{{ route('presence.index') }}">Présence</a>
            <a href="{{ route('user.presence.report') }}">Bilan de présence</a>
        </div>
        <div class="nav-profile">
          <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->nom }}" class="nav-profile-img">
            <div class="nav-profile-dropdown">
              <a href="{{ route('user.profile') }}" class="">Mon Profil</a>
                <a href="{{route('logouts')}}">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<main class="main-content">
    <div class="container">
        <h1>Tableau de bord</h1>
        <div class="row">
            <div class="col-md-6">
                <h2>Marquer la présence</h2>
                <form action="{{ route('presence.arrival') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary" id="arrivalButton" {{ Carbon\Carbon::now()->hour >= 7 && Carbon\Carbon::now()->hour < 10 ? '' : 'disabled' }}>
                        Marquer l'arrivée
                    </button>
                </form>
                <form action="{{ route('presence.departure') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-secondary" id="departureButton" {{ Carbon\Carbon::now()->hour >= 17 && Carbon\Carbon::now()->hour < 18 && Carbon\Carbon::now()->minute <= 30 ? '' : 'disabled' }}>
                        Marquer le départ
                    </button>
                </form>
            </div>
        
            </div>
        </div>
    </div>
</main>
<script>
  document.addEventListener('DOMContentLoaded', function() {
      const profileImg = document.querySelector('.nav-profile-img');
      const dropdown = document.querySelector('.nav-profile-dropdown');
  
      profileImg.addEventListener('click', function() {
          dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
      });
  
      // Fermer le menu déroulant si on clique en dehors
      document.addEventListener('click', function(event) {
          if (!profileImg.contains(event.target) && !dropdown.contains(event.target)) {
              dropdown.style.display = 'none';
          }
      });
  });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateButtons() {
            var now = new Date();
            var hour = now.getHours();
            var minute = now.getMinutes();
    
            var arrivalButton = document.getElementById('arrivalButton');
            var departureButton = document.getElementById('departureButton');
    
            arrivalButton.disabled = !(hour >= 7 && hour < 10);
            departureButton.disabled = !(hour >= 17 && hour < 18 && minute <= 30);
        }
    
        // Mettre à jour toutes les minutes
        setInterval(updateButtons, 60000);
        // Mettre à jour immédiatement au chargement de la page
        updateButtons();
    });
    </script>
@endsection