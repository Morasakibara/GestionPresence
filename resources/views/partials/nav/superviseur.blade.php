@php
    $unread = Auth::user()->unreadNotifications->count();
@endphp

<p class="console-nav-group">Pilotage</p>
@include('partials.nav.item', ['icon' => 'dashboard', 'label' => 'Tableau de bord', 'href' => route('superviseur.supdashboard'), 'active' => request()->routeIs('superviseur.supdashboard')])
@include('partials.nav.item', ['icon' => 'users', 'label' => 'Suivre les présences', 'href' => route('superviseur.showFollowPresence'), 'active' => request()->routeIs('superviseur.showFollowPresence', 'viewUser', 'superviseur.getUserDetails')])
@include('partials.nav.item', ['icon' => 'clock', 'label' => 'Présence', 'href' => route('presence.index'), 'active' => request()->routeIs('presence.*')])

<p class="console-nav-group">Évaluation</p>
@include('partials.nav.item', ['icon' => 'report', 'label' => 'Générer Rapports', 'href' => route('superviseur.generateReport2'), 'active' => request()->routeIs('superviseur.generateReport2', 'superviseur.storeEvaluation', 'superviseur.evaluations*', 'superviseur.evaluation.*', 'superviseur.comparatif.export', 'export.pdf')])
@include('partials.nav.item', ['icon' => 'chartLine', 'label' => 'Rendements', 'href' => route('superviseur.rendements'), 'active' => request()->routeIs('superviseur.rendements*')])

<p class="console-nav-group">Équipe</p>
@include('partials.nav.item', ['icon' => 'userPlus', 'label' => 'Ajout de membre', 'href' => route('superviseur.showAddMember'), 'active' => request()->routeIs('superviseur.showAddMember', 'superviseur.showAddMemberForm', 'superviseur.addMemberToTeam', 'superviseur.removeMemberFromTeam')])
@include('partials.nav.item', ['icon' => 'calendar', 'label' => 'Congés', 'href' => route('superviseur.conges.index'), 'active' => request()->routeIs('superviseur.conges*')])

<p class="console-nav-group">Système</p>
@include('partials.nav.item', ['icon' => 'bell', 'label' => 'Notifications', 'href' => route('notifications.index'), 'active' => request()->routeIs('notifications*'), 'badge' => $unread])
