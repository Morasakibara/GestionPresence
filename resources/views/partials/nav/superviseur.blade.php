@php
    $navClass = fn (bool $active) => 'rounded-md px-3 py-2 text-sm font-medium ' .
        ($active ? 'bg-pharaoh-gold text-white' : 'text-gray-300 hover:bg-pharaoh-gold hover:text-white');
@endphp

<a href="{{ route('superviseur.supdashboard') }}" class="{{ $navClass(request()->routeIs('superviseur.supdashboard')) }}" @if(request()->routeIs('superviseur.supdashboard')) aria-current="page" @endif>Tableau de bord</a>
<a href="{{ route('superviseur.showAddMember') }}" class="{{ $navClass(request()->routeIs('superviseur.showAddMember', 'superviseur.showAddMemberForm', 'superviseur.addMemberToTeam', 'superviseur.removeMemberFromTeam')) }}" @if(request()->routeIs('superviseur.showAddMember', 'superviseur.showAddMemberForm', 'superviseur.addMemberToTeam', 'superviseur.removeMemberFromTeam')) aria-current="page" @endif>Ajout de membre</a>
<a href="{{ route('superviseur.showFollowPresence') }}" class="{{ $navClass(request()->routeIs('superviseur.showFollowPresence', 'viewUser', 'superviseur.getUserDetails')) }}" @if(request()->routeIs('superviseur.showFollowPresence', 'viewUser', 'superviseur.getUserDetails')) aria-current="page" @endif>Suivre les présences</a>
<a href="{{ route('superviseur.generateReport2') }}" class="{{ $navClass(request()->routeIs('superviseur.generateReport2', 'superviseur.storeEvaluation', 'superviseur.evaluations*', 'superviseur.evaluation.*', 'superviseur.comparatif.export', 'export.pdf')) }}" @if(request()->routeIs('superviseur.generateReport2', 'superviseur.storeEvaluation', 'superviseur.evaluations*', 'superviseur.evaluation.*', 'superviseur.comparatif.export', 'export.pdf')) aria-current="page" @endif>Générer Rapports</a>
<a href="{{ route('superviseur.rendements') }}" class="{{ $navClass(request()->routeIs('superviseur.rendements*')) }}" @if(request()->routeIs('superviseur.rendements*')) aria-current="page" @endif>Rendements</a>
<a href="{{ route('superviseur.conges.index') }}" class="{{ $navClass(request()->routeIs('superviseur.conges*')) }}" @if(request()->routeIs('superviseur.conges*')) aria-current="page" @endif>Congés</a>
<a href="{{ route('presence.index') }}" class="{{ $navClass(request()->routeIs('presence.*')) }}" @if(request()->routeIs('presence.*')) aria-current="page" @endif>Présence</a>
<a href="{{ route('notifications.index') }}" class="{{ $navClass(request()->routeIs('notifications*')) }} relative" @if(request()->routeIs('notifications*')) aria-current="page" @endif>
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
