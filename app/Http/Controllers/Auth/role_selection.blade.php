

@section('content')
    <div id="roleModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h2 class="modal-title">Bienvenue <span id="userName"></span></h2>
            <p class="modal-text">Quelle fonctionnalité souhaitez-vous utiliser ?</p>
            <div class="modal-buttons">
                <button onclick="selectRole('utilisateur')" class="btn-cancel">Utilisateur</button>
                <button onclick="selectRole('superviseur')" class="btn-confirm">Superviseur</button>
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
            background-color: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.5rem;
            width: 90%;
            max-width: 28rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
        }
        .modal-icon {
            background-color: #d1fae5;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .modal-icon svg {
            color: #059669;
        }
        .modal-title {
            color: #111827;
            font-size: 1.25rem;
            font-weight: 600;
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
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
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
            background-color: #4f46e5;
            color: #ffffff;
            border: none;
        }
        .btn-confirm:hover {
            background-color: #4338ca;
        }
    </style>

    <script>
        function showModal(userName) {
            document.getElementById('userName').textContent = userName;
            document.getElementById('roleModal').style.display = 'flex';
        }

        function selectRole(role) {
            document.getElementById('roleModal').style.display = 'none';
            
            if (role === 'superviseur') {
                window.location.href = '/superviseur/supdashboard';
            } else if (role === 'superviseur') {
                window.location.href = '/user/dashboard';
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showModal("{{ $user->nom }}");
        });
    </script>
@endsection