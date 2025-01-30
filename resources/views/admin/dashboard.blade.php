@extends('layouts.logout')

@section('content')

<style>
  /* Réinitialisation et styles de base */
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
  
  /* Header */
  .header {
    background-color: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1.5rem 0;
  }
  
  .header h1 {
    font-size: 1.875rem;
    font-weight: bold;
    color: #111827;
  }
  
  /* Main content */
  .main-content {
    padding: 2rem 0;
  }
  
  /* Tableau de bord */
  .dashboard h1 {
    font-size: 2rem;
    margin-bottom: 1.5rem;
  }
  
  .alert {
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1.5rem;
  }
  
  .alert-success {
    background-color: #d1fae5;
    color: #065f46;
  }
  
  .buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 1.5rem;
  }
  
  .btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s;
  }
  
  .btn-primary {
    background-color: #2563eb;
    color: #ffffff;
  }
  
  .btn-primary:hover {
    background-color: #1d4ed8;
  }
  
  .btn-danger {
    background-color: #2563eb;
    color: #ffffff;
  }
  
  .btn-danger:hover {
    background-color: #1d4ed8;
  }
  
  .btn-success {
    background-color: #2563eb;
    color: #ffffff;
  }
  
  .btn-success:hover {
    background-color: #1d4ed8;
  }
  
  .btn-info {
    background-color: #3b82f6;
    color: #ffffff;
  }
  
  .btn-info:hover {
    background-color: #1d4ed8;
  }
  
  /* Responsive design */
  @media (min-width: 768px) {
    .nav-links {
      display: flex;
    }
  
    .nav-profile-img {
      width: 2rem;
      height: 2rem;
    }
  
    .buttons {
      flex-wrap: nowrap;
    }
  }
  
  @media (min-width: 1024px) {
    .container {
      padding: 2rem;
    }
  
    .header h1 {
      font-size: 2.25rem;
    }
  
    .dashboard h1 {
      font-size: 2.5rem;
    }
  }
</style>
<nav class="nav">
    <div class="container nav-content">
        <div class="nav-logo">
            <img src="{{ asset('/storage/avatars/default.png') }}" alt="Logo">
        </div>
        <div class="nav-links">
            <a href="#" class="active">Tableau de bord</a>
            <a href="{{ route('admin.addEmployee') }}" class="">Ajouter employé</a>
            <a href="{{ route('admin.deleteEmployee') }}" class="">Supprimer employé</a>
            <a href="{{ route('admin.generateReport') }}" class="">Générer Bilan</a>
            <a href="{{ route('admin.showEmployeeList') }}" class="">Consulter la liste des employés</a>
        </div>
        <div class="nav-profile">
          <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->nom }}" class="nav-profile-img">
            <div class="nav-profile-dropdown">
                <a href="#">Votre Profil</a>
                <a href="#">Paramètres</a>
                <a href="{{route('logouts')}}">Déconnexion</a>
            </div>
        </div>
    </div>
</nav>

<header class="header">
    <div class="container">
        <h1>Tableau de bord de l'administrateur</h1>
    </div>
</header>

<main class="main-content">
    <div class="container dashboard">
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif
        <div class="buttons">
            <a href="{{ route('admin.addEmployee') }}" class="btn btn-primary">Ajouter employé</a>
            <a href="{{ route('admin.deleteEmployee') }}" class="btn btn-danger">Supprimer employé</a>
            <a href="{{ route('admin.generateReport') }}" class="btn btn-success">Générer Bilan</a>
            <a href="{{ route('admin.showEmployeeList') }}" class="btn btn-info">Consulter la liste des employés</a>
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
});
</script>
@endsection