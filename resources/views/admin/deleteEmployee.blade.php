@extends('layouts.app')

@section('title', 'Supprimer un employé')

@section('content')
<div class="mx-auto max-w-md px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-[#080808]">Supprimer un employé</h1>
        <p class="mt-2 text-sm text-gray-600">Saisissez l'email de l'employé à supprimer</p>
    </div>

    @if(session('success'))
    <div class="mb-6 rounded-md bg-3hcig-green-light/20 p-4 text-3hcig-green-dark">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 rounded-md bg-red-50 p-4 text-red-800">
        {{ session('error') }}
    </div>
    @endif

    <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
        <form action="{{ route('admin.deleteEmployee') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email de l'employé ou du superviseur</label>
                <div class="mt-1">
                    <input type="email" id="email" name="email" required
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-3hcig-blue focus:ring-3hcig-blue sm:text-sm">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="flex w-full justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Supprimer l'employé
                </button>
            </div>
        </form>

        <div class="mt-6 rounded-md bg-yellow-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Attention : Cette action est irréversible et supprimera définitivement l'employé et toutes ses données associées.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
