@extends('layouts.user')

@section('content')
<div class="container p-4 mx-auto">
    <h1 class="mb-4 text-xl font-bold">Mon profil</h1>

    <!-- Formulaire de mise à jour du profil -->
    <form action="{{ route('user.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block mb-2">Changer la photo de profil</label>
            <input type="file" name="avatar" class="w-full p-2 border">
        </div>

        <div class="mb-4">
            <label class="block mb-2">Modifier le mot de passe</label>
            <input type="password" name="password" class="w-full p-2 border" placeholder="Nouveau mot de passe">
        </div>

        <div class="mb-4">
            <label class="block mb-2">Confirmer le nouveau mot de passe</label>
            <input type="password" name="password_confirmation" class="w-full p-2 border" placeholder="Confirmer le mot de passe">
        </div>

        <div>
            <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-700">
                Mettre à jour
            </button>
        </div>

        @if(session('success'))
            <div class="mt-4 text-green-500">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mt-4 text-red-500">
                {{ $errors->first() }}
            </div>
        @endif
    </form>
</div>
@endsection
