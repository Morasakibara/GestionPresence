@if (Auth::check())
@extends('layouts.app')
@endif


@section('content')
<style>
    /* Styles généraux */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f3f4f6;
    margin: 0;
    padding: 0;
}

.login-container {
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

input[type="email"]
 {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    color: #1f2937;
    transition: border-color 0.15s ease-in-out;
}

input[type="password"]
{
    width: 93%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    color: #1f2937;
    transition: border-color 0.15s ease-in-out;
}

input[type="email"]:focus,
input[type="password"]:focus {
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
}

button[type="submit"]:hover {
    background-color: #4338ca;
}

.forgot-password {
    display: block;
    text-align: right;
    font-size: 0.75rem;
    color: #4f46e5;
    text-decoration: none;
    margin-top: 0.5rem;
}

.forgot-password:hover {
    text-decoration: underline;
}


.password-input-wrapper {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
}

.eye-icon {
    display: inline-block;
    width: 20px;
    height: 20px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'%3E%3C/path%3E%3Ccircle cx='12' cy='12' r='3'%3E%3C/circle%3E%3C/svg%3E");
    background-size: contain;
    background-repeat: no-repeat;
    opacity: 0.5;
    transition: opacity 0.3s ease;
}

.toggle-password:hover .eye-icon {
    opacity: 1;
}

.toggle-password.show-password .eye-icon {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24'%3E%3C/path%3E%3Cline x1='1' y1='1' x2='23' y2='23'%3E%3C/line%3E%3C/svg%3E");
}

/* Ajustez le padding de l'input password pour faire de la place pour l'icône */
input[type="password"] {
    padding-right: 40px;
}
</style>
<div class="login-container">
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <h2>Connexion à votre compte</h2>

        <div class="form-group">
            <label for="email">Adresse email</label>
            <input id="email" type="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <div class="password-input-wrapper">
                <input id="password" type="password" name="password" required>
                <button type="button" id="togglePassword" class="toggle-password">
                    <i class="eye-icon"></i>
                </button>
            </div>
            <a href="#" class="forgot-password">Mot de passe oublié ?</a>
        </div>

        <button type="submit">Se connecter</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('show-password');
    });
});
</script>
@endsection