@php
    $navClass = fn (bool $active) => 'rounded-md px-3 py-2 text-sm font-medium ' .
        ($active ? 'bg-pharaoh-gold text-white' : 'text-gray-300 hover:bg-pharaoh-gold hover:text-white');
@endphp

<a href="{{ route('secretaire.dashboard') }}" class="{{ $navClass(request()->routeIs('secretaire.dashboard')) }}" @if(request()->routeIs('secretaire.dashboard')) aria-current="page" @endif>Dashboard</a>
<a href="{{ route('secretaire.commandes') }}" class="{{ $navClass(request()->routeIs('secretaire.commandes', 'secretaire.commandes.*')) }}" @if(request()->routeIs('secretaire.commandes', 'secretaire.commandes.*')) aria-current="page" @endif>Commandes</a>
<a href="{{ route('secretaire.services') }}" class="{{ $navClass(request()->routeIs('secretaire.services', 'secretaire.services.*')) }}" @if(request()->routeIs('secretaire.services', 'secretaire.services.*')) aria-current="page" @endif>Services</a>
<a href="{{ route('secretaire.retraits') }}" class="{{ $navClass(request()->routeIs('secretaire.retraits', 'secretaire.retraits.*')) }}" @if(request()->routeIs('secretaire.retraits', 'secretaire.retraits.*')) aria-current="page" @endif>Retraits</a>
<a href="{{ route('secretaire.rapport') }}" class="{{ $navClass(request()->routeIs('secretaire.rapport', 'secretaire.rapport.*')) }}" @if(request()->routeIs('secretaire.rapport', 'secretaire.rapport.*')) aria-current="page" @endif>Rapport</a>
<a href="{{ route('notifications.index') }}" class="{{ $navClass(request()->routeIs('notifications*')) }} relative" @if(request()->routeIs('notifications*')) aria-current="page" @endif>
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
