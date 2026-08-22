@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Mon Profil</span></div>
@endsection

@section('navigation')
<a href="{{ route('user.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Dashboard</a>
<a href="{{ route('user.profile') }}" class="rounded-md bg-pharaoh-gold px-3 py-2 text-sm font-medium text-white" aria-current="page">Profil</a>
<a href="{{ route('presence.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Présence</a>
<a href="{{ route('user.rendement') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rendement</a>
@endsection

@section('content')

@if(session('success'))<div class="alert alert-success"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('success') }}</span></div>@endif
@if(session('error'))<div class="alert alert-danger"><svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ═══ PROFIL ═══ -->
    <div class="lg:col-span-2">
        <div class="form-card">
            <div class="form-card-header">
                <h2 class="form-card-title">Mon profil</h2>
                <p class="form-card-subtitle">Gérez vos informations personnelles</p>
            </div>

            @if ($errors->any())
            <div class="bg-red-600/10 border-2 border-red-600 text-red-600 px-4 py-3 rounded-md mb-6">
                <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
            @endif

            <form action="{{ route('user.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name" class="input-label">Nom complet</label>
                    <input type="text" id="name" name="name" value="{{ $user->nom }}" required class="input-field">
                </div>

                <div class="form-group">
                    <label for="email" class="input-label">Adresse email</label>
                    <input type="email" id="email" name="email" value="{{ $user->email }}" required class="input-field">
                </div>

                <div class="form-group">
                    <label for="password" class="input-label">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer" class="input-field">
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="input-label">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="input-field">
                </div>

                <div class="form-group">
                    <label for="avatar" class="input-label">Photo de profil</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*" class="input-field">
                </div>

                <button type="submit" class="btn-primary">💾 Enregistrer les modifications</button>
            </form>
        </div>
    </div>

    <!-- ═══ AVATAR + INFO ═══ -->
    <div class="lg:col-span-1">
        <div class="form-card text-center">
            <div class="flex flex-col items-center">
                @if($user->avatar)
                    <div class="mb-4 h-32 w-32 overflow-hidden rounded-full border-4 border-[#E9B533]/60 shadow-gold">
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="h-full w-full object-cover">
                    </div>
                @else
                    <div class="mb-4 flex h-32 w-32 items-center justify-center rounded-full bg-[#FBF3E6] text-pharaoh-bronze">
                        <svg class="h-20 w-20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                @endif
                <h3 class="text-lg font-bold text-[#080808]">{{ $user->nom }}</h3>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <span class="mt-2 inline-flex items-center rounded-full bg-[#FBF3E6] px-3 py-1 text-xs font-semibold text-[#885910]">
                    {{ $user->role }}
                </span>
            </div>

            <div class="mt-6 border-t pt-4 space-y-3 text-sm text-left">
                <div class="flex justify-between">
                    <span class="text-gray-500">Membre depuis</span>
                    <span class="font-medium text-[#080808]">{{ $user->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Dernière connexion</span>
                    <span class="font-medium text-[#080808]">{{ $user->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('mobile-navigation')
<a href="{{ route('user.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium bg-pharaoh-gold text-white">Dashboard</a>
<a href="{{ route('user.profile') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Profil</a>
<a href="{{ route('presence.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Index</a>
<a href="{{ route('user.rendement') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">Rendement</a>
@endsection\n
