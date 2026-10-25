@extends('layouts.dashboard')

@section('title', 'Supprimer un employé')
@section('breadcrumb', 'Supprimer un employé')

@section('header')
    <div>
        <span>Supprimer un employé</span>
        <p class="console-subtitle">Saisissez l'email de l'employé à supprimer.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.showEmployeeList') }}" class="btn-secondary">Retour à la liste</a>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
    <div class="mx-auto max-w-xl">
        <div class="console-card">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Compte à supprimer</h2>
                    <p class="console-card-sub">La suppression est définitive.</p>
                </div>
                <span class="badge badge-danger">Irréversible</span>
            </div>

            <div class="console-card-body">
                <form action="{{ route('admin.deleteEmployee') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="input-label">Email de l'employé ou du superviseur</label>
                        <div class="mt-1">
                            <input type="email" id="email" name="email" required class="input-field">
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end">
                        <a href="{{ route('admin.showEmployeeList') }}" class="btn-secondary order-2 sm:order-1">Annuler</a>
                        <button type="submit" class="btn-danger order-1 sm:order-2">Supprimer l'employé</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="alert alert-warning mt-5">
            <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <p>
                Attention : cette action est irréversible et supprimera définitivement l'employé et toutes ses données associées.
            </p>
        </div>
    </div>
@endsection
