@php
    $unread = Auth::user()->unreadNotifications->count();
@endphp

<p class="console-nav-group">Caisse</p>
@include('partials.nav.item', ['icon' => 'dashboard', 'label' => 'Tableau de bord', 'href' => route('directrice.dashboard'), 'active' => request()->routeIs('directrice.dashboard')])
@include('partials.nav.item', ['icon' => 'bag', 'label' => 'Commandes', 'href' => route('directrice.commandes'), 'active' => request()->routeIs('directrice.commandes', 'directrice.commandes.*')])
@include('partials.nav.item', ['icon' => 'printer', 'label' => 'Services', 'href' => route('directrice.services'), 'active' => request()->routeIs('directrice.services', 'directrice.services.*')])
@include('partials.nav.item', ['icon' => 'withdraw', 'label' => 'Retraits', 'href' => route('directrice.retraits'), 'active' => request()->routeIs('directrice.retraits', 'directrice.retraits.*')])

<p class="console-nav-group">Analyse</p>
@include('partials.nav.item', ['icon' => 'pie', 'label' => 'Rapport', 'href' => route('directrice.rapport'), 'active' => request()->routeIs('directrice.rapport', 'directrice.rapport.*')])

<p class="console-nav-group">Système</p>
@include('partials.nav.item', ['icon' => 'bell', 'label' => 'Notifications', 'href' => route('notifications.index'), 'active' => request()->routeIs('notifications*'), 'badge' => $unread])
