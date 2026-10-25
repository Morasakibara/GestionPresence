@extends('layouts.dashboard')

@section('title', 'Mon profil')
@section('breadcrumb', 'Mon profil')

@section('header')
    <div>
        <span>Mon profil</span>
        <p class="console-subtitle">Gérez vos informations personnelles et votre photo.</p>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <aside class="space-y-5">
            <div class="console-card">
                <div class="console-card-body flex flex-col items-center text-center">
                    @if($user->avatar)
                        <div class="mb-3 h-28 w-28 overflow-hidden rounded-full border-4 border-[#E9B533]/60 shadow-gold">
                            <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="Avatar de {{ $user->nom }}" class="h-full w-full object-cover">
                        </div>
                    @else
                        <div class="mb-3 flex h-28 w-28 items-center justify-center rounded-full bg-[#FBF3E6] text-pharaoh-bronze">
                            <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    @endif
                    <p class="text-sm font-semibold text-[#080808]">{{ $user->nom }}</p>
                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    <span class="badge badge-gold mt-3">{{ $user->role }}</span>
                </div>
            </div>
        </aside>

        <div class="console-card xl:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Informations personnelles</h2>
                    <p class="console-card-sub">L'email sert également d'identifiant de connexion.</p>
                </div>
            </div>

            <div class="console-card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-semibold">Il y a des erreurs dans votre formulaire</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.updateProfile') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('POST')

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="input-label">Nom</label>
                            <div class="mt-1">
                                <input type="text" id="name" name="nom" value="{{ $user->nom }}" required class="input-field">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="input-label">Email</label>
                            <div class="mt-1">
                                <input type="email" id="email" name="email" value="{{ $user->email }}" required class="input-field">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="input-label">Nouveau mot de passe</label>
                            <div class="mt-1">
                                <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer" autocomplete="new-password" class="input-field">
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="input-label">Confirmer le nouveau mot de passe</label>
                            <div class="mt-1">
                                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="input-field">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="avatar" class="input-label">Photo de profil</label>
                            <div class="mt-1">
                                <input type="file" id="avatar" name="avatar" accept="image/*" class="input-field">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-gray-100 pt-4">
                        <button type="submit" class="btn-gold">Mettre à jour le profil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
