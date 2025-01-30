@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Mon profil</h1>
    <form action="{{ route('employee.update-profile') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $user->nom }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
        </div>

        <div class="form-group">
            <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
        </div>

        <div class="form-group">
            <label for="avatar">Photo de profil</label>
            <input type="file" class="form-control-file" id="avatar" name="avatar">
        </div>

        @if($user->avatar)
            <div class="mt-3">
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="img-thumbnail" style="max-width: 200px;">
            </div>
        @endif

        <button type="submit" class="mt-3 btn btn-primary">Mettre à jour le profil</button>
    </form>
</div>
@endsection