@extends('layouts.dashboard')

@section('title', 'Mes Congés & Absences')
@section('breadcrumb', 'Mes congés')

@section('header')
    <div>
        <span>Mes Congés & Absences</span>
        <p class="console-subtitle">Déposez une demande et suivez son traitement par votre superviseur.</p>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
@php
    $userConges = \App\Models\Conge::where('employer_id', auth()->id())->orderByDesc('created_at')->get();
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        @include('partials.dashboard.kpi', ['label' => 'Total demandes', 'value' => $userConges->count(), 'meta' => 'Toutes périodes', 'tone' => 'gold'])
        @include('partials.dashboard.kpi', ['label' => 'Approuvées', 'value' => $userConges->where('statut', 'approuve')->count(), 'meta' => 'Validées', 'tone' => 'success'])
        @include('partials.dashboard.kpi', ['label' => 'En attente', 'value' => $userConges->where('statut', 'en_attente')->count(), 'meta' => 'À traiter', 'tone' => 'warning'])
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

        <div class="console-card xl:col-span-2">
            <div class="console-card-head">
                <div>
                    <h2 class="console-card-title">Nouvelle demande</h2>
                    <p class="console-card-sub">Congé ou absence : précisez la période et le motif.</p>
                </div>
                <span class="badge badge-gold">RH</span>
            </div>

            <div class="console-card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <form action="{{ route('user.conges.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="date_debut" class="input-label mb-1">Date de début <span class="text-red-600">*</span></label>
                            <input type="date" name="date_debut" id="date_debut" required min="{{ now()->format('Y-m-d') }}" value="{{ old('date_debut') }}" class="input-field">
                        </div>
                        <div>
                            <label for="date_fin" class="input-label mb-1">Date de fin <span class="text-red-600">*</span></label>
                            <input type="date" name="date_fin" id="date_fin" required value="{{ old('date_fin') }}" class="input-field">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="type" class="input-label mb-1">Type <span class="text-red-600">*</span></label>
                            <select name="type" id="type" required class="select-field">
                                <option value="conge" {{ old('type') === 'conge' ? 'selected' : '' }}>Congé</option>
                                <option value="absence_justifiee" {{ old('type') === 'absence_justifiee' ? 'selected' : '' }}>Absence justifiée</option>
                                <option value="absence_injustifiee" {{ old('type') === 'absence_injustifiee' ? 'selected' : '' }}>Absence injustifiée</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="motif" class="input-label mb-1">Motif <span class="text-red-600">*</span></label>
                        <textarea name="motif" id="motif" rows="3" required class="input-field" placeholder="Décrivez le motif de votre demande...">{{ old('motif') }}</textarea>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-100 pt-4">
                        <button type="submit" class="btn-gold px-5 py-2.5 text-sm" onclick="return confirm('Envoyer cette demande ?')">
                            Soumettre la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="space-y-5">
            <div class="console-card">
                <div class="console-card-body">
                    <h2 class="console-card-title">Bon à savoir</h2>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600">
                        <li class="console-panel-row">
                            <span>Validation</span>
                            <span class="badge badge-info">Superviseur</span>
                        </li>
                        <li class="console-panel-row">
                            <span>Délai de réponse</span>
                            <span class="text-xs text-gray-500">Dès traitement</span>
                        </li>
                        <li class="console-panel-row">
                            <span>Annulation</span>
                            <span class="text-xs text-gray-500">Sur demande</span>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>

    <div class="console-card">
        <div class="console-card-head">
            <div>
                <h2 class="console-card-title">Historique de mes demandes</h2>
                <p class="console-card-sub">Suivi du statut de chaque demande envoyée.</p>
            </div>
            <span class="badge badge-gold">{{ $userConges->count() }} demande{{ $userConges->count() > 1 ? 's' : '' }}</span>
        </div>

        @if($userConges->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg class="h-7 w-7 text-[#B77F1D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-semibold text-gray-900">Aucune demande</h3>
                <p class="mt-1 text-sm text-gray-500">Aucune demande pour le moment.</p>
            </div>
        @else
            <div class="table-scroll">
                <table class="min-w-full text-sm">
                    <thead class="table-head">
                        <tr>
                            <th scope="col">Type</th>
                            <th scope="col">Période</th>
                            <th scope="col">Jours</th>
                            <th scope="col">Motif</th>
                            <th scope="col">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="table-body divide-y divide-gray-100 bg-white">
                        @foreach($userConges as $c)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900">{{ $c->typeLabel() }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $c->date_debut->format('d/m/Y') }} → {{ $c->date_fin->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-center font-bold text-pharaoh-gold">{{ $c->dureeJours() }}</td>
                            <td class="max-w-xs px-4 py-3">
                                <p class="truncate text-gray-600" title="{{ $c->motif }}">{{ $c->motif }}</p>
                            </td>
                            <td class="px-4 py-3">{!! $c->statutBadge() !!}</td>
                        </tr>
                        @if($c->commentaire_admin)
                        <tr class="bg-gray-50">
                            <td colspan="5" class="px-4 py-2">
                                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                    <svg class="h-3.5 w-3.5 flex-shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span>Réponse du superviseur : {{ $c->commentaire_admin }}</span>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
