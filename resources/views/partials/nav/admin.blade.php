@php
    $navClass = fn (bool $active) => 'rounded-md px-3 py-2 text-sm font-medium ' .
        ($active ? 'bg-pharaoh-gold text-white' : 'text-gray-300 hover:bg-pharaoh-gold hover:text-white');
@endphp

<a href="{{ route('admin.dashboard') }}" class="{{ $navClass(request()->routeIs('admin.dashboard')) }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Tableau de bord</a>
<a href="{{ route('admin.paiements') }}" class="{{ $navClass(request()->routeIs('admin.paiements')) }}" @if(request()->routeIs('admin.paiements')) aria-current="page" @endif>Paiements</a>
<a href="{{ route('admin.caisse') }}" class="{{ $navClass(request()->routeIs('admin.caisse')) }}" @if(request()->routeIs('admin.caisse')) aria-current="page" @endif>État de caisse</a>
<a href="{{ route('admin.stock') }}" class="{{ $navClass(request()->routeIs('admin.stock')) }}" @if(request()->routeIs('admin.stock')) aria-current="page" @endif>État du stock</a>
<a href="{{ route('admin.addEmployee') }}" class="{{ $navClass(request()->routeIs('admin.addEmployee')) }}" @if(request()->routeIs('admin.addEmployee')) aria-current="page" @endif>Ajouter employé</a>
<a href="{{ route('admin.showEmployeeList') }}" class="{{ $navClass(request()->routeIs('admin.showEmployeeList')) }}" @if(request()->routeIs('admin.showEmployeeList')) aria-current="page" @endif>Liste des employés</a>
<a href="{{ route('admin.generateReport') }}" class="{{ $navClass(request()->routeIs('admin.generateReport', 'admin.storeEvaluation', 'admin.evaluations*', 'admin.evaluation.*')) }}" @if(request()->routeIs('admin.generateReport', 'admin.storeEvaluation', 'admin.evaluations*', 'admin.evaluation.*')) aria-current="page" @endif>Générer Bilan</a>
<a href="{{ route('admin.workplace-locations.index') }}" class="{{ $navClass(request()->routeIs('admin.workplace-locations*')) }}" @if(request()->routeIs('admin.workplace-locations*')) aria-current="page" @endif>Lieux de travail</a>
<a href="{{ route('admin.conges') }}" class="{{ $navClass(request()->routeIs('admin.conges*')) }}" @if(request()->routeIs('admin.conges*')) aria-current="page" @endif>Congés</a>
<a href="{{ route('admin.auditLogs') }}" class="{{ $navClass(request()->routeIs('admin.auditLogs')) }}" @if(request()->routeIs('admin.auditLogs')) aria-current="page" @endif>Audit</a>
<a href="{{ route('notifications.index') }}" class="{{ $navClass(request()->routeIs('notifications*')) }} relative" @if(request()->routeIs('notifications*')) aria-current="page" @endif>
    Notifications
    @if(Auth::user()->unreadNotifications->count() > 0)
        <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">
            {{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}
        </span>
    @endif
</a>
@if(Auth::user()->isSuperAdmin())
<a href="{{ route('admin.metrics') }}" class="{{ $navClass(request()->routeIs('admin.metrics')) }}" @if(request()->routeIs('admin.metrics')) aria-current="page" @endif title="Réservé au super administrateur">Métriques</a>
@endif
