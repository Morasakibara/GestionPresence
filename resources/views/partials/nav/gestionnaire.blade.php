@php
    $navClass = fn (bool $active) => 'rounded-md px-3 py-2 text-sm font-medium ' .
        ($active ? 'bg-pharaoh-gold text-white' : 'text-gray-300 hover:bg-pharaoh-gold hover:text-white');
@endphp

<a href="{{ route('gestionnaire.dashboard') }}" class="{{ $navClass(request()->routeIs('gestionnaire.dashboard')) }}" @if(request()->routeIs('gestionnaire.dashboard')) aria-current="page" @endif>Dashboard</a>
<a href="{{ route('gestionnaire.tshirts') }}" class="{{ $navClass(request()->routeIs('gestionnaire.tshirts*')) }}" @if(request()->routeIs('gestionnaire.tshirts*')) aria-current="page" @endif>T-Shirts</a>
<a href="{{ route('gestionnaire.papier') }}" class="{{ $navClass(request()->routeIs('gestionnaire.papier*')) }}" @if(request()->routeIs('gestionnaire.papier*')) aria-current="page" @endif>Papier</a>
<a href="{{ route('notifications.index') }}" class="{{ $navClass(request()->routeIs('notifications*')) }} relative" @if(request()->routeIs('notifications*')) aria-current="page" @endif>
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
