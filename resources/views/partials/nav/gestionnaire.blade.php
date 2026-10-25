@php
    $unread = Auth::user()->unreadNotifications->count();
@endphp

<p class="console-nav-group">Stock</p>
@include('partials.nav.item', ['icon' => 'dashboard', 'label' => 'Tableau de bord', 'href' => route('gestionnaire.dashboard'), 'active' => request()->routeIs('gestionnaire.dashboard')])
@include('partials.nav.item', ['icon' => 'shirt', 'label' => 'T-Shirts', 'href' => route('gestionnaire.tshirts'), 'active' => request()->routeIs('gestionnaire.tshirts*')])
@include('partials.nav.item', ['icon' => 'paper', 'label' => 'Papier', 'href' => route('gestionnaire.papier'), 'active' => request()->routeIs('gestionnaire.papier*')])

<p class="console-nav-group">Système</p>
@include('partials.nav.item', ['icon' => 'bell', 'label' => 'Notifications', 'href' => route('notifications.index'), 'active' => request()->routeIs('notifications*'), 'badge' => $unread])
