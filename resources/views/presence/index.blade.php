@extends('layouts.app')

@section('content')
<style>
    /* Styles généraux */
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    background-color: #f3f4f6;
    color: #1a202c;
    line-height: 1.5;
}

.container {
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

h1 {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1f2937;
    margin-bottom: 1.5rem;
    text-align: center;
}

/* Styles des boutons */
.btn {
    display: block;
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    text-align: center;
    color: #ffffff;
    background-color: #3b82f6;
    border: none;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #2563eb;
}

.btn:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}

.btn-arrival {
    background-color: #3b82f6;
}

.btn-departure {
    background-color: #10b981;
}

.btn-arrival:hover {
    background-color: #2563eb;
}

.btn-departure:hover {
    background-color: #059669;
}

/* Styles des messages */
.message {
    padding: 0.75rem;
    margin-top: 1rem;
    border-radius: 0.375rem;
    text-align: center;
}

.message-success {
    background-color: #d1fae5;
    color: #065f46;
}

.message-error {
    background-color: #fee2e2;
    color: #991b1b;
}

/* Styles des formulaires */
.form-group {
    margin-bottom: 1rem;
}

/* Responsive design */
@media (max-width: 640px) {
    .container {
        padding: 1rem;
    }
}
</style>
<div class="container">
    <h1>Marquer la présence</h1>

    <!-- Bouton pour marquer l'heure d'arrivée -->
    <div class="form-group">
        @if(now()->hour >= 7 && now()->hour <= 10)
            <form method="POST" action="{{ route('presence.arrival') }}">
                @csrf
                <button type="submit" class="btn btn-arrival">
                    Marquer l'heure d'arrivée
                </button>
            </form>
        @else
            <p class="message message-error">Le bouton d'arrivée est actif uniquement entre 7h et 10h.</p>
        @endif
    </div>

    <!-- Bouton pour marquer l'heure de départ -->
    <div class="form-group">
        @if(now()->hour >= 17 && now()->hour <= 18.5)
            <form method="POST" action="{{ route('presence.departure') }}">
                @csrf
                <button type="submit" class="btn btn-departure">
                    Marquer l'heure de départ
                </button>
            </form>
        @else
            <p class="message message-error">Le bouton de départ est actif uniquement entre 17h et 18h30.</p>
        @endif
    </div>

    @if(session('success'))
        <div class="message message-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="message message-error">
            {{ $errors->first() }}
        </div>
    @endif
</div>
@endsection