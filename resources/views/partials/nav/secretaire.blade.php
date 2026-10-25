@php
    $unread = Auth::user()->unreadNotifications->count();
@endphp

<p class="console-nav-group">Caisse</p>
@include('partials.nav.item', ['icon' => 'dashboard', 'label' => 'Tableau de bord', 'href' => route('secretaire.dashboard'), 'active' => request()->routeIs('secretaire.dashboard')])
@include('partials.nav.item', ['icon' => 'bag', 'label' => 'Commandes', 'href' => route('secretaire.commandes'), 'active' => request()->routeIs('secretaire.commandes', 'secretaire.commandes.*')])
@include('partials.nav.item', ['icon' => 'printer', 'label' => 'Services', 'href' => route('secretaire.services'), 'active' => request()->routeIs('secretaire.services', 'secretaire.services.*')])
@include('partials.nav.item', ['icon' => 'withdraw', 'label' => 'Retraits', 'href' => route('secretaire.retraits'), 'active' => request()->routeIs('secretaire.retraits', 'secretaire.retraits.*')])

<p class="console-nav-group">Analyse</p>
@include('partials.nav.item', ['icon' => 'pie', 'label' => 'Rapport', 'href' => route('secretaire.rapport'), 'active' => request()->routeIs('secretaire.rapport', 'secretaire.rapport.*')])

<p class="console-nav-group">Système</p>
@include('partials.nav.item', ['icon' => 'bell', 'label' => 'Notifications', 'href' => route('notifications.index'), 'active' => request()->routeIs('notifications*'), 'badge' => $unread])
