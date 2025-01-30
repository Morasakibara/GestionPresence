@extends('layouts.user')

@section('content')
<div class="container">
    <h1>Tableau de bord</h1>
    <div class="row">
        <div class="col-md-6">
            <h2>Marquer la présence</h2>
            <form action="{{ route('employee.mark-arrival') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary" id="arrivalButton" {{ Carbon\Carbon::now()->hour >= 7 && Carbon\Carbon::now()->hour < 10 ? '' : 'disabled' }}>
                    Marquer l'arrivée
                </button>
            </form>
            <form action="{{ route('employee.mark-departure') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-secondary" id="departureButton" {{ Carbon\Carbon::now()->hour >= 17 && Carbon\Carbon::now()->hour < 18 && Carbon\Carbon::now()->minute <= 30 ? '' : 'disabled' }}>
                    Marquer le départ
                </button>
            </form>
        </div>
        <div class="col-md-6">
            <h2>Liens rapides</h2>
            <ul>
                <li><a href="{{ route('employee.profile') }}">Mon profil</a></li>
                <li><a href="{{ route('employee.presence-report') }}">Bilan de présence</a></li>
            </ul>
        </div>
    </div>
</div>

<script>
    function updateButtonStatus() {
        const now = new Date();
        const arrivalButton = document.getElementById('arrivalButton');
        const departureButton = document.getElementById('departureButton');

        arrivalButton.disabled = now.getHours() < 7 || now.getHours() >= 10;
        departureButton.disabled = now.getHours() < 17 || (now.getHours() === 18 && now.getMinutes() > 30) || now.getHours() > 18;
    }

    setInterval(updateButtonStatus, 60000); // Mise à jour toutes les minutes
    updateButtonStatus(); // Appel initial
</script>
@endsection