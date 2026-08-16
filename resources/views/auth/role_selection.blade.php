@extends('layouts.app')

@section('content')
<div id="roleModal" class="modal" style="display:flex;">
    <div class="modal-content">
        <div class="modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <h2 class="modal-title">Bienvenue {{ $user->nom }}</h2>
        <p class="modal-text">Quelle fonctionnalité souhaitez-vous utiliser ?</p>
        <div class="modal-buttons">
            <button onclick="selectRole('Employer')" class="btn-cancel">Employé</button>
            <button onclick="selectRole('Superviseur')" class="btn-confirm">Superviseur</button>
        </div>
    </div>
</div>
<style>
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(8,8,8,0.55);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background-color: #ffffff;
        padding: 2rem;
        border-radius: 1rem;
        width: 90%;
        max-width: 28rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1), 0 8px 24px -8px rgba(211,155,35,0.2);
        text-align: center;
    }
    .modal-icon {
        background-color: #f6e2c0;
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    .modal-icon svg {
        color: #b77f1d;
    }
    .modal-title {
        color: #080808;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .modal-text {
        color: #6b7280;
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
    }
    .modal-buttons {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }
    .btn-cancel, .btn-confirm {
        padding: 0.5rem 1.25rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 150ms cubic-bezier(0.23, 1, 0.32, 1), background-color 0.2s;
    }
    .btn-cancel:active, .btn-confirm:active {
        transform: scale(0.97);
    }
    .btn-cancel {
        background-color: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
    }
    .btn-cancel:hover {
        background-color: #f3f4f6;
    }
    .btn-confirm {
        background-color: #D39B23;
        color: #ffffff;
        border: none;
        box-shadow: 0 8px 24px -8px rgba(211,155,35,0.45);
    }
    .btn-confirm:hover {
        background-color: #E9B533;
    }
</style>


<script>
    function showModal(userName) {
        document.getElementById('userName').textContent = userName;
        document.getElementById('roleModal').style.display = 'flex';
    }

    function selectRole(role) {
    // Au lieu de rediriger directement
    // window.location.href = '/superviseur/supdashboard';

    // Utilisez AJAX pour envoyer la sélection au serveur
    fetch('/select-role', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ role: role })
    })
    .then(response => response.json())
    .then(data => {
        if (data.redirect) {
            window.location.href = data.redirect;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

    document.addEventListener('DOMContentLoaded', function() {
        showModal("{{ $user->nom }}");
    });
</script>
@endsection
