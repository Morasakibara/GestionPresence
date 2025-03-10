@extends('layouts.dashboard')

@section('header')
<div class="flex items-center justify-between">
    <span>Tableau de bord Employé</span>
    <!-- Indicateur de notifications -->
    <a href="{{ route('notifications.index') }}" class="relative inline-flex items-center px-2 py-1 text-sm font-medium text-3hcig-blue hover:bg-gray-100 rounded-md">
        <svg class="h-6 w-6 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        Notifications
        @if(Auth::user()->unreadNotifications->count() > 0)
            <span class="absolute -top-1 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
                {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
            </span>
        @endif
    </a>
</div>
@endsection

@section('navigation')
<!-- Current: "bg-3hcig-blue text-white", Default: "text-gray-300 hover:bg-3hcig-blue hover:text-white" -->
<a href="{{ route('user.dashboard') }}" class="rounded-md bg-3hcig-blue px-3 py-2 text-sm font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('presence.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Présence</a>
<a href="{{ route('user.presence.report') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Bilan de présence</a>
<a href="{{ route('notifications.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white relative">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
@endsection

@section('mobile-navigation')
<a href="{{ route('user.dashboard') }}" class="block rounded-md bg-3hcig-blue px-3 py-2 text-base font-medium text-white" aria-current="page">Tableau de bord</a>
<a href="{{ route('presence.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Présence</a>
<a href="{{ route('user.presence.report') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white">Bilan de présence</a>
<a href="{{ route('notifications.index') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-3hcig-blue hover:text-white relative">
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute top-2 right-2 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
    <div class="rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-xl font-semibold text-gray-900">Marquer la présence</h2>

        <div class="space-y-4">
            <form action="{{ route('presence.arrival') }}" method="POST">
                @csrf
                <button type="submit" id="arrivalButton"
                        class="w-full rounded-md bg-3hcig-blue px-4 py-2 font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ Carbon\Carbon::now()->hour >= 7 && Carbon\Carbon::now()->hour < 10 ? '' : 'disabled' }}>
                    Marquer l'arrivée
                </button>
            </form>

            <form action="{{ route('presence.departure') }}" method="POST">
                @csrf
                <button type="submit" id="departureButton"
                        class="w-full rounded-md bg-gray-600 px-4 py-2 font-medium text-white shadow-sm hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ Carbon\Carbon::now()->hour >= 17 && Carbon\Carbon::now()->hour < 18 && Carbon\Carbon::now()->minute <= 30 ? '' : 'disabled' }}>
                    Marquer le départ
                </button>
            </form>
        </div>

        <div class="mt-4 rounded-md bg-gray-50 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-3hcig-blue" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 text-sm text-gray-600">
                    <p>L'arrivée peut être marquée entre 7h et 10h.</p>
                    <p>Le départ peut être marqué entre 17h et 18h30.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-xl font-semibold text-gray-900">Résumé de présence</h2>

        <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <span class="text-sm font-medium text-gray-500">Présences ce mois-ci</span>
                <span class="text-lg font-semibold text-3hcig-blue">{{ isset($presenceCount) ? $presenceCount : '0' }}</span>
            </div>

            <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <span class="text-sm font-medium text-gray-500">Dernière arrivée</span>
                <span class="text-gray-700">{{ isset($lastArrival) ? $lastArrival->format('d/m/Y H:i') : 'Aucune donnée' }}</span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Dernier départ</span>
                <span class="text-gray-700">{{ isset($lastDeparture) ? $lastDeparture->format('d/m/Y H:i') : 'Aucune donnée' }}</span>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('user.presence.report') }}" class="inline-flex items-center text-sm font-medium text-3hcig-blue hover:text-3hcig-blue-light">
                Voir le bilan complet
                <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-xl font-semibold text-gray-900 flex items-center justify-between">
            <span>Notifications</span>
            @if(Auth::user()->unreadNotifications->count() > 0)
                <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-red-600 text-xs font-medium text-white">
                    {{ Auth::user()->unreadNotifications->count() }}
                </span>
            @endif
        </h2>

        <div class="space-y-3">
            @if(Auth::user()->notifications->count() > 0)
                @foreach(Auth::user()->notifications->take(3) as $notification)
                    <div class="border-l-4 {{ $notification->read_at ? 'border-gray-300 bg-gray-50' : 'border-3hcig-blue bg-blue-50' }} p-3">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-700 {{ $notification->read_at ? '' : 'font-medium' }}">
                                {{ isset($notification->data['message']) ? $notification->data['message'] : 'Notification' }}
                            </p>
                            <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="mt-4 text-center">
                    <a href="{{ route('notifications.index') }}" class="inline-flex items-center text-sm font-medium text-3hcig-blue hover:text-3hcig-blue-light">
                        Voir toutes les notifications
                        <svg class="ml-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            @else
                <div class="py-8 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="mt-2">Vous n'avez aucune notification</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateButtons() {
        var now = new Date();
        var hour = now.getHours();
        var minute = now.getMinutes();

        var arrivalButton = document.getElementById('arrivalButton');
        var departureButton = document.getElementById('departureButton');

        arrivalButton.disabled = !(hour >= 7 && hour < 10);
        departureButton.disabled = !(hour >= 17 && hour < 18 && minute <= 30);
    }

    // Mettre à jour toutes les minutes
    setInterval(updateButtons, 60000);
    // Mettre à jour immédiatement au chargement de la page
    updateButtons();
});
</script>
@endpush