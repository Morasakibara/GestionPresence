@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-md">
        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex flex-col items-center">
                    <h1 class="mb-6 text-2xl font-bold text-3hcig-blue-dark">Mon profil</h1>

                    <!-- Photo de profil en haut -->
                    <div class="mb-6 flex flex-col items-center">
                        @if($user->avatar)
                            <div class="mb-3 h-32 w-32 overflow-hidden rounded-full border-4 border-3hcig-blue-light">
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="h-full w-full object-cover">
                            </div>
                        @else
                            <div class="mb-3 flex h-32 w-32 items-center justify-center rounded-full bg-3hcig-blue-light/10 text-3hcig-blue">
                                <svg class="h-20 w-20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        @endif
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>

                    <!-- Formulaire de profil -->
                    <form action="{{ route('user.update') }}" method="POST" enctype="multipart/form-data" class="w-full space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
                            <div class="mt-1">
                                <input type="text" id="name" name="name" value="{{ $user->nom }}" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <div class="mt-1">
                                <input type="email" id="email" name="email" value="{{ $user->email }}" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
                            <div class="mt-1">
                                <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le nouveau mot de passe</label>
                            <div class="mt-1">
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label for="avatar" class="block text-sm font-medium text-gray-700">Photo de profil</label>
                            <div class="mt-1">
                                <input type="file" id="avatar" name="avatar"
                                       class="block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-3hcig-blue focus:outline-none focus:ring-3hcig-blue sm:text-sm">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                    class="flex w-full justify-center rounded-md bg-3hcig-blue px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                                Mettre à jour le profil
                            </button>
                        </div>
                    </form>

                    @if(session('success'))
                    <div class="mt-6 rounded-md bg-3hcig-green-light/20 p-4 text-3hcig-green-dark">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-3hcig-green" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-3hcig-green-dark">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mt-6 rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Il y a des erreurs dans votre formulaire</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc space-y-1 pl-5">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
