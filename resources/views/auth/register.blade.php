@extends('layouts.app')

@section('content')
<style>
    /* Styles généraux */
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

/* Styles du formulaire */
form {
    background-color: white;
    padding: 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

h2 {
    text-align: center;
    color: #1f2937;
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 2rem;
}

div {
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
select {
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
select:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='currentColor'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd' /%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.5rem center;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
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
}

button[type="submit"]:hover {
    background-color: #4338ca;
}

/* Styles pour les messages d'erreur (si nécessaire) */
.error-message {
    color: #dc2626;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}
</style>
<div class="container">
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label for="name">Nom</label>
            <input id="name" type="text" name="name" required autofocus value="nom">
        </div>

        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" required>
        </div>

        <div>
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" required>
        </div>

        <div>
            <label for="password_confirmation">Confirmez le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
        </div>

        <div>
            <label for="poste">Poste</label>
            <select name="poste" id="poste" required>
                <option value="rien">Selectionne ton poste</option>
                <option value="administrateur">administrateur</option>
            </select>
        </div>

        <button type="submit">
            S'enregistrer
        </button>
    </form>
</div>
@endsection
