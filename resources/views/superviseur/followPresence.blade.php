@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Présences</title>
    <link rel="stylesheet" href="{{ asset('css/employee-list-styles.css') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Styles pour la modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <style>
        /* General styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f3f4f6;
    color: #1f2937;
    line-height: 1.5;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

h1 {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
}

/* Search styles */
.search-container {
    margin-bottom: 20px;
}

.search-input {
    width: 100%;
    padding: 10px 15px;
    font-size: 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background-color: #ffffff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.search-input:focus {
    outline: none;
    border-color: #a78bfa;
    box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.2);
}

/* Table styles */
.employee-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background-color: #1f2937;
    border-radius: 8px;
    overflow: hidden;
}

.employee-table th,
.employee-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #374151;
}

.employee-table th {
    background-color: #111827;
    color: #ffffff;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.05em;
}

.employee-table tbody tr {
    transition: background-color 0.2s;
}

.employee-table tbody tr:hover {
    background-color: #374151;
}

.employee-table td {
    color: #e5e7eb;
}

/* Button styles */
.button {
    padding: 6px 12px;
    background-color: #4f46e5;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s;
}

.button:hover {
    background-color: #4338ca;
}

/* Role styles */
.role {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 500;
}

.role-member {
    background-color: #10b981;
    color: #064e3b;
}

.role-admin {
    background-color: #f59e0b;
    color: #78350f;
}

.role-owner {
    background-color: #ef4444;
    color: #7f1d1d;
}

/* Checkbox styles */
.checkbox-group {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.checkbox-input {
    margin-right: 5px;
}
    </style>
</head>
<body>
    <div class="container">
        <h1>Suivi des Présences</h1>

        <table class="employee-table">
            <thead>
                <tr>
                    <th>Photo de profil</th>
                    <th>Nom</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($utilisateurs as $utilisateur)
                <tr>
                    <td>
                        @if($utilisateur->avatar)
                            <img src="{{ asset('storage/avatars/'.$utilisateur->avatar) }}" alt="{{ $utilisateur->nom }}" class="avatar">
                        @else
                            <img src="{{ asset('storage/avatars/default.png') }}" alt="Default Avatar" class="avatar">
                        @endif
                    </td>
                    <td>{{ $utilisateur->nom }}</td>
                    <td>
                        <button class="button" onclick="openPopup('{{ $utilisateur->id }}')">Suivre</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Informations de l'utilisateur</h2>
            <div id="userDetails"></div>
            <a id="viewMoreLink" class="button" href="#">Voir plus</a>
        </div>
    </div>

    <script>
    function openPopup(userId) {
        $.ajax({
            url: '/getUserDetails/' + userId,
            type: 'GET',
            success: function(data) {
                $('#userDetails').html(data.detailsHtml);
                $('#viewMoreLink').attr('href', '/viewUser/' + userId);
                $('#userModal').css('display', 'block');
            },
            error: function(err) {
                console.log('Erreur lors de la récupération des détails de l\'utilisateur:', err);
            }
        });
    }

    function closeModal() {
        $('#userModal').css('display', 'none');
    }

    // Fermer la modal si on clique en dehors
    window.onclick = function(event) {
        var modal = document.getElementById('userModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
</body>
</html>
@endsection