@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between"><span>Paramètres — Code PIN</span></div>
@endsection

@section('navigation')
<a href="{{ url()->previous() }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-pharaoh-gold hover:text-white">← Retour</a>
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success mb-4">
    <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger mb-4">
    <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span>{{ session('error') }}</span>
</div>
@endif

<div class="max-w-lg mx-auto">
    <!-- Info -->
    <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-sm flex items-start gap-3">
        <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-medium">Récupération rapide de session</p>
            <p class="mt-1">En définissant un code PIN, vous pourrez récupérer votre session après expiration sans avoir à saisir à nouveau votre email et mot de passe. Le PIN doit faire exactement 4 chiffres.</p>
        </div>
    </div>

    <!-- État actuel -->
    <div class="pharaoh-card p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="rounded-full {{ $user->pin_required ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }} p-3">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-[#080808]">{{ $user->pin_required ? 'PIN actif' : 'PIN non configuré' }}</div>
                    <div class="text-sm text-gray-500">{{ $user->pin_required ? 'Votre code PIN est actif et sera demandé à chaque session expirée.' : 'Configurez un PIN pour une récupération rapide de session.' }}</div>
                </div>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $user->pin_required ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $user->pin_required ? 'ACTIF' : 'INACTIF' }}
            </span>
        </div>
    </div>

    <!-- Formulaire set/change PIN -->
    <div class="pharaoh-card p-6 mb-6">
        <h3 class="text-lg font-semibold text-[#080808] mb-4">{{ $user->pin_required ? 'Modifier le code PIN' : 'Définir un code PIN' }}</h3>
        <form method="POST" action="{{ route('pin.set') }}" class="space-y-4">
            @csrf
            <div>
                <label for="current_password_set" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                <input type="password" id="current_password_set" name="current_password" required
                       class="input-field w-full" placeholder="Saisissez votre mot de passe actuel">
                @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="pin_set" class="block text-sm font-medium text-gray-700 mb-1">Nouveau code PIN (4 chiffres)</label>
                <input type="password" id="pin_set" name="pin" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" autocomplete="off" required
                       class="input-field w-full" placeholder="Ex: 1234" style="letter-spacing: 0.5em; text-align: center; font-size: 1.5rem;">
                @error('pin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="pin_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le code PIN</label>
                <input type="password" id="pin_confirmation" name="pin_confirmation" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" autocomplete="off" required
                       class="input-field w-full" placeholder="Saisissez à nouveau" style="letter-spacing: 0.5em; text-align: center; font-size: 1.5rem;">
            </div>
            <button type="submit" class="btn-gold w-full justify-center">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ $user->pin_required ? 'Mettre à jour le PIN' : 'Enregistrer le PIN' }}
            </button>
        </form>
    </div>

    <!-- Supprimer le PIN -->
    @if($user->pin_required)
    <div class="pharaoh-card p-6 border border-red-200">
        <h3 class="text-lg font-semibold text-red-700 mb-4">Supprimer le code PIN</h3>
        <p class="text-sm text-gray-600 mb-4">La récupération de session par PIN sera désactivée. Vous devrez utiliser email + mot de passe à chaque fois.</p>
        <form method="POST" action="{{ route('pin.remove') }}" class="space-y-4" onsubmit="return confirm('Désactiver le code PIN ?')">
            @csrf
            <div>
                <label for="current_password_remove" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                <input type="password" id="current_password_remove" name="current_password" required
                       class="input-field w-full" placeholder="Confirmez votre mot de passe">
            </div>
            <button type="submit" class="w-full flex justify-center items-center gap-2 rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Supprimer le PIN
            </button>
        </form>
    </div>
    @endif
</div>

@endsection
