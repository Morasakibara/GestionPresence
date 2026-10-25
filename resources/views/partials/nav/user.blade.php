@php
    $unread = Auth::user()->unreadNotifications->count();
@endphp

<p class="console-nav-group">Mon espace</p>
@include('partials.nav.item', ['icon' => 'dashboard', 'label' => 'Tableau de bord', 'href' => route('user.dashboard'), 'active' => request()->routeIs('user.dashboard')])
@include('partials.nav.item', ['icon' => 'clock', 'label' => 'Présence', 'href' => route('presence.index'), 'active' => request()->routeIs('presence.*')])
@include('partials.nav.item', ['icon' => 'report', 'label' => 'Bilan de présence', 'href' => route('user.presence.report'), 'active' => request()->routeIs('user.presence.report')])
@include('partials.nav.item', ['icon' => 'chartLine', 'label' => 'Mes rendements', 'href' => route('user.rendement'), 'active' => request()->routeIs('user.rendement')])

<p class="console-nav-group">Ressources humaines</p>
@include('partials.nav.item', ['icon' => 'calendar', 'label' => 'Congés', 'href' => route('user.conges'), 'active' => request()->routeIs('user.conges*')])
@include('partials.nav.item', ['icon' => 'bell', 'label' => 'Notifications', 'href' => route('notifications.index'), 'active' => request()->routeIs('notifications*'), 'badge' => $unread])
