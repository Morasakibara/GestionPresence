@php
    $unread = Auth::user()->unreadNotifications->count();
@endphp

<p class="console-nav-group">Pilotage</p>
@include('partials.nav.item', ['icon' => 'dashboard', 'label' => 'Tableau de bord', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')])
@include('partials.nav.item', ['icon' => 'payments', 'label' => 'Paiements', 'href' => route('admin.paiements'), 'active' => request()->routeIs('admin.paiements')])
@include('partials.nav.item', ['icon' => 'wallet', 'label' => 'État de caisse', 'href' => route('admin.caisse'), 'active' => request()->routeIs('admin.caisse')])
@include('partials.nav.item', ['icon' => 'stock', 'label' => 'État du stock', 'href' => route('admin.stock'), 'active' => request()->routeIs('admin.stock')])

<p class="console-nav-group">Personnel</p>
@include('partials.nav.item', ['icon' => 'userPlus', 'label' => 'Ajouter employé', 'href' => route('admin.addEmployee'), 'active' => request()->routeIs('admin.addEmployee')])
@include('partials.nav.item', ['icon' => 'users', 'label' => 'Liste des employés', 'href' => route('admin.showEmployeeList'), 'active' => request()->routeIs('admin.showEmployeeList')])
@include('partials.nav.item', ['icon' => 'report', 'label' => 'Générer Bilan', 'href' => route('admin.generateReport'), 'active' => request()->routeIs('admin.generateReport', 'admin.storeEvaluation', 'admin.evaluations*', 'admin.evaluation.*')])
@include('partials.nav.item', ['icon' => 'pin', 'label' => 'Lieux de travail', 'href' => route('admin.workplace-locations.index'), 'active' => request()->routeIs('admin.workplace-locations*')])

<p class="console-nav-group">Administration</p>
@include('partials.nav.item', ['icon' => 'calendar', 'label' => 'Congés', 'href' => route('admin.conges'), 'active' => request()->routeIs('admin.conges*')])
@include('partials.nav.item', ['icon' => 'audit', 'label' => 'Audit', 'href' => route('admin.auditLogs'), 'active' => request()->routeIs('admin.auditLogs')])
@include('partials.nav.item', ['icon' => 'bell', 'label' => 'Notifications', 'href' => route('notifications.index'), 'active' => request()->routeIs('notifications*'), 'badge' => $unread])

@if(Auth::user()->isSuperAdmin())
    <p class="console-nav-group">Système</p>
    @include('partials.nav.item', ['icon' => 'metrics', 'label' => 'Métriques', 'href' => route('admin.metrics'), 'active' => request()->routeIs('admin.metrics'), 'title' => 'Réservé au super administrateur'])
@endif
