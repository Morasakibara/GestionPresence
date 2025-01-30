@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générer le bilan de présence</title>
    <link rel="stylesheet" href="{{ asset('css/form-styles.css') }}">
</head>
<body>
    <style>
        /* Reset and base styles */
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
  background-color: white;
  font-family: Arial, sans-serif;
}

.container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 3rem 1.5rem;
  max-width: 24rem;
  margin: 0 auto;
}

.logo {
  height: 2.5rem;
  width: auto;
  margin: 0 auto;
}

h1, h2 {
  text-align: center;
  font-size: 1.5rem;
  font-weight: bold;
  margin-top: 2.5rem;
  margin-bottom: 1.5rem;
  color: #111827;
}

form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #111827;
  margin-bottom: 0.5rem;
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="date"],
select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  border: 1px solid #D1D5DB;
  font-size: 0.875rem;
  color: #111827;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus,
input[type="date"]:focus,
select:focus {
  outline: none;
  border-color: #4F46E5;
  box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
}

.checkbox-group {
  display: flex;
  gap: 1rem;
  align-items: center;
}

button[type="submit"] {
  width: 100%;
  padding: 0.75rem 1rem;
  background-color: #4F46E5;
  color: white;
  font-size: 0.875rem;
  font-weight: 600;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

button[type="submit"]:hover {
  background-color: #4338CA;
}

.error-list {
  color: #DC2626;
  margin-top: 1rem;
  padding-left: 1.5rem;
}

.success-message {
  color: #10B981;
  text-align: center;
  margin-top: 1rem;
}

.link {
  color: #4F46E5;
  text-decoration: none;
  font-weight: 600;
}

.link:hover {
  color: #4338CA;
}
    </style>
    <div class="container">
        <h1>Générer le bilan de présence</h1>

        <form action="{{ route('admin.generateReport') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="start_date">Date de début</label>
                <input type="date" name="start_date" id="start_date" required>
            </div>

            <div class="form-group">
                <label for="end_date">Date de fin</label>
                <input type="date" name="end_date" id="end_date" required>
            </div>

            <div class="form-group">
                <label for="export_format">Format d'exportation</label>
                <select name="export_format" id="export_format" required>
                    <option value="rien">Choisir le format d'exportation</option>
                    <option value="pdf">PDF</option>
                </select>
            </div>

            <button type="submit">Générer</button>
        </form>
    </div>
</body>
</html>
@endsection