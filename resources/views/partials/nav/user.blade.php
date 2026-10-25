@php
    $navClass = fn (bool $active) => 'rounded-md px-3 py-2 text-sm font-medium ' .
        ($active ? 'bg-pharaoh-gold text-white' : 'text-gray-300 hover:bg-pharaoh-gold hover:text-white');
@endphp

<a href="{{ route('user.dashboard') }}" class="{{ $navClass(request()->routeIs('user.dashboard')) }}" @if(request()->routeIs('user.dashboard')) aria-current="page" @endif>Tableau de bord</a>
<a href="{{ route('presence.index') }}" class="{{ $navClass(request()->routeIs('presence.*')) }}" @if(request()->routeIs('presence.*')) aria-current="page" @endif>Présence</a>
<a href="{{ route('user.presence.report') }}" class="{{ $navClass(request()->routeIs('user.presence.report')) }}" @if(request()->routeIs('user.presence.report')) aria-current="page" @endif>Bilan de présence</a>
<a href="{{ route('user.rendement') }}" class="{{ $navClass(request()->routeIs('user.rendement')) }}" @if(request()->routeIs('user.rendement')) aria-current="page" @endif>Mes rendements</a>
<a href="{{ route('user.conges') }}" class="{{ $navClass(request()->routeIs('user.conges*')) }}" @if(request()->routeIs('user.conges*')) aria-current="page" @endif>Congés</a>
<a href="{{ route('notifications.index') }}" class="{{ $navClass(request()->routeIs('notifications*')) }} relative" @if(request()->routeIs('notifications*')) aria-current="page" @endif>
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
