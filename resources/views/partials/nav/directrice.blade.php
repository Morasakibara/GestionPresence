@php
    $navClass = fn (bool $active) => 'rounded-md px-3 py-2 text-sm font-medium ' .
        ($active ? 'bg-pharaoh-gold text-white' : 'text-gray-300 hover:bg-pharaoh-gold hover:text-white');
@endphp

<a href="{{ route('directrice.dashboard') }}" class="{{ $navClass(request()->routeIs('directrice.dashboard')) }}" @if(request()->routeIs('directrice.dashboard')) aria-current="page" @endif>Dashboard</a>
<a href="{{ route('directrice.commandes') }}" class="{{ $navClass(request()->routeIs('directrice.commandes', 'directrice.commandes.*')) }}" @if(request()->routeIs('directrice.commandes', 'directrice.commandes.*')) aria-current="page" @endif>Commandes</a>
<a href="{{ route('directrice.services') }}" class="{{ $navClass(request()->routeIs('directrice.services', 'directrice.services.*')) }}" @if(request()->routeIs('directrice.services', 'directrice.services.*')) aria-current="page" @endif>Services</a>
<a href="{{ route('directrice.retraits') }}" class="{{ $navClass(request()->routeIs('directrice.retraits', 'directrice.retraits.*')) }}" @if(request()->routeIs('directrice.retraits', 'directrice.retraits.*')) aria-current="page" @endif>Retraits</a>
<a href="{{ route('directrice.rapport') }}" class="{{ $navClass(request()->routeIs('directrice.rapport', 'directrice.rapport.*')) }}" @if(request()->routeIs('directrice.rapport', 'directrice.rapport.*')) aria-current="page" @endif>Rapport</a>
<a href="{{ route('notifications.index') }}" class="{{ $navClass(request()->routeIs('notifications*')) }} relative" @if(request()->routeIs('notifications*')) aria-current="page" @endif>
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
