@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter des membres à votre équipe</title>
    <link rel="stylesheet" href="{{ asset('css/employee-list-styles.css') }}">
</head>
<body>
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
    <div class="container">
        <h1>Ajouter des membres à votre équipe</h1>

        @if(session('success'))
        <script>
            alert("{{ session('success') }}");
        </script>
        @endif

        @if(session('error'))
        <script>
            alert("{{ session('error') }}");
        </script>
        @endif

        <div class="search-container">
            <form method="GET" action="{{ route('superviseur.showAddMemberForm') }}">
                @csrf
                <input type="text" class="search-input" name="search" placeholder="Rechercher par nom" value="{{ $search ?? '' }}">
            </form>
        </div>

        <table class="employee-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employers as $employer)
                    <tr>
                        <td>{{ $employer->nom }}</td>
                        <td>
                            <form action="{{ route('superviseur.addMemberToTeam', $employer->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="button">Ajouter à l'équipe</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">Aucun employé trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        // You can add any necessary JavaScript here
    </script>
</body>
</html>
@endsection