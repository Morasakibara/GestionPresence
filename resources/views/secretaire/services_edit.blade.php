@extends('layouts.dashboard')

@section('title', 'Modifier le service')
@section('breadcrumb', 'Services / Modifier')

@section('header')
    <div>
        <span>Modifier le service photo</span>
        <p class="console-subtitle">Tarifs et libellés appliqués à la caisse.</p>
    </div>
@endsection

@section('content_shell', 'flush')

@section('content')
<div class="space-y-5">
    <div class="console-card p-6">
        <form action="{{ route('secretaire.services.update', $service->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="input-label">Type</label>
                <select name="type" class="input-field mt-1" required>
                    @foreach($typesPhoto as $key => $label)
                        <option value="{{ $key }}" {{ $service->type === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="input-label">Montant (FCFA)</label>
                <input type="number" name="montant" class="input-field mt-1" min="0.01" step="0.01" value="{{ old('montant', $service->montant) }}" required>
            </div>
            <div>
                <label class="input-label">Détails (facultatif)</label>
                <input type="text" name="details" class="input-field mt-1" value="{{ old('details', $service->details) }}">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-gold">Mettre à jour</button>
                <a href="{{ route('secretaire.services') }}" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
