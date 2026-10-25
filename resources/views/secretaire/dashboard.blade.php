@extends('layouts.dashboard')

@section('content_shell', 'flush')

@section('breadcrumb', 'Caisse photo')

@section('header')
<div class="flex w-full flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="console-title">Tableau de bord</h1>
        <p class="console-subtitle">Caisse photo — {{ now()->format('d/m/Y') }}</p>
    </div>
    <a href="{{ route('secretaire.commandes') }}" class="btn-gold inline-flex items-center gap-2 text-sm">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        Nouvelle commande
    </a>
</div>
@endsection

@section('tabs')
<a href="#caisse-flux" class="console-tab is-active" aria-current="page">Vue d'ensemble</a>
<a href="#caisse-cumul" class="console-tab">Encaissements</a>
<a href="#caisse-types" class="console-tab">Détail par type</a>
@if($commandesImpayees->isNotEmpty())
    <a href="#caisse-attente" class="console-tab">En attente</a>
@endif
@endsection

@section('content')
@include('partials.dashboard.caisse', ['module' => 'secretaire', 'types' => $typesPhoto])
@endsection
