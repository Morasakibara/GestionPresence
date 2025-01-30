@extends('layouts.app')

@section('content')
<style>
    /* profile.css */

body {
    font-family: 'Arial', sans-serif;
    background-color: #f3f4f6;
    margin: 0;
    padding: 0;
}

.container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 2rem;
}

h1 {
    text-align: center;
    color: #1f2937;
    font-size: 1.75rem;
    font-weight: bold;
    margin-bottom: 2rem;
}

form {
    background-color: white;
    padding: 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 500px;
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="file"] {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    color: #1f2937;
    transition: border-color 0.15s ease-in-out;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus,
input[type="file"]:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

button[type="submit"] {
    width: 100%;
    padding: 0.75rem;
    background-color: #4f46e5;
    color: white;
    border: none;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.15s ease-in-out;
    margin-top: 1rem;
}

button[type="submit"]:hover {
    background-color: #4338ca;
}

.img-thumbnail {
    max-width: 200px;
    height: auto;
    border-radius: 0.375rem;
    margin-top: 1rem;
}

/* Responsive design */
@media (max-width: 640px) {
    form {
        padding: 1.5rem;
    }

    h1 {
        font-size: 1.5rem;
    }
}
</style>
<div class="container">
    <form action="{{ route('user.update') }}" method="POST" enctype="multipart/form-data">
        <h1>Mon profil</h1>
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" id="name" name="name" value="{{ $user->nom }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ $user->email }}" required>
        </div>

        <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
            <input type="password" id="password_confirmation" name="password_confirmation">
        </div>

        <div class="form-group">
            <label for="avatar">Photo de profil</label>
            <input type="file" id="avatar" name="avatar">
        </div>

        @if($user->avatar)
            <div class="avatar-preview">
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="img-thumbnail">
            </div>
        @endif

        <button type="submit">Mettre à jour le profil</button>
    </form>
</div>
@endsection