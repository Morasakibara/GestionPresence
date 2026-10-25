{{--
    Élément du menu latéral de la console.
    Variables : $icon (clé d'icône), $label, $href, $active (bool),
                $badge (int, optionnel), $title (optionnel).
--}}
@php
    $active = !empty($active);
    $badge = isset($badge) ? (int) $badge : null;
@endphp

<a href="{{ $href }}"
   class="console-nav-link {{ $active ? 'is-active' : '' }}"
   @if ($active) aria-current="page" @endif
   @if (!empty($title)) title="{{ $title }}" @endif>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        @switch($icon)
            @case('dashboard')
                <rect x="3.5" y="3.5" width="6.5" height="6.5" rx="1.5" />
                <rect x="14" y="3.5" width="6.5" height="6.5" rx="1.5" />
                <rect x="3.5" y="14" width="6.5" height="6.5" rx="1.5" />
                <rect x="14" y="14" width="6.5" height="6.5" rx="1.5" />
                @break
            @case('payments')
                <rect x="2.75" y="6.5" width="18.5" height="11" rx="2" />
                <circle cx="12" cy="12" r="2.4" />
                <path d="M6 10v4M18 10v4" />
                @break
            @case('wallet')
                <path d="M3.5 8.5A2.5 2.5 0 0 1 6 6h11.5A2.5 2.5 0 0 1 20 8.5v8A2.5 2.5 0 0 1 17.5 19H6a2.5 2.5 0 0 1-2.5-2.5z" />
                <path d="M3.5 8.5V7A2 2 0 0 1 5.5 5h10" />
                <circle cx="16" cy="12.5" r="1.2" />
                @break
            @case('stock')
                <path d="M3.5 7.5h17v3h-17z" />
                <path d="M5 10.5v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8" />
                <path d="M10 14.5h4" />
                @break
            @case('userPlus')
                <circle cx="10" cy="8.25" r="3.25" />
                <path d="M4 20c0-3.1 2.7-5.25 6-5.25 1.4 0 2.7.4 3.7 1.1" />
                <path d="M18 14.5v5M15.5 17h5" />
                @break
            @case('users')
                <circle cx="9.25" cy="8.5" r="3" />
                <path d="M3.75 20c0-3 2.45-5 5.5-5s5.5 2 5.5 5" />
                <path d="M16 5.6a3 3 0 0 1 0 5.8M17.5 15.2c2 .6 3.5 2.4 3.5 4.8" />
                @break
            @case('report')
                <path d="M6.5 3.5h7l4.5 4.5v12a1 1 0 0 1-1 1h-10.5a1 1 0 0 1-1-1v-15.5a1 1 0 0 1 1-1z" />
                <path d="M13.5 3.5V8H18" />
                <path d="M9 17v-3M12 17v-5M15 17v-2" />
                @break
            @case('pin')
                <path d="M12 21s6.5-5.5 6.5-10.5A6.5 6.5 0 0 0 5.5 10.5C5.5 15.5 12 21 12 21z" />
                <circle cx="12" cy="10.5" r="2.25" />
                @break
            @case('calendar')
                <rect x="3.5" y="5.5" width="17" height="15" rx="2" />
                <path d="M3.5 10h17M8.5 3.5v4M15.5 3.5v4" />
                @break
            @case('audit')
                <path d="M9 4.5H7.5A1.5 1.5 0 0 0 6 6v13.5A1.5 1.5 0 0 0 7.5 21h9a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H15" />
                <rect x="9" y="3" width="6" height="3" rx="1" />
                <path d="M9.5 13.5l1.75 1.75L15 11.5" />
                @break
            @case('bell')
                <path d="M18 15.5V11a6 6 0 1 0-12 0v4.5L4.5 18h15z" />
                <path d="M9.75 18a2.25 2.25 0 0 0 4.5 0" />
                @break
            @case('metrics')
                <path d="M3.5 17.5l5-6 3.5 3 4-5.5 4 4" />
                <path d="M3.5 20.5h17" />
                @break
            @case('clock')
                <circle cx="12" cy="12" r="8.25" />
                <path d="M12 7.5V12l3 2" />
                @break
            @case('chartLine')
                <path d="M4 19V5M4 19h16" />
                <path d="M7 15l3.5-4 3 2.5L20 7.5" />
                @break
            @case('bag')
                <path d="M5 8h14l-1 12H6z" />
                <path d="M9 8V6.5a3 3 0 0 1 6 0V8" />
                @break
            @case('printer')
                <rect x="5.5" y="9" width="13" height="7" rx="1.5" />
                <path d="M8 9V4.5h8V9M8 16v3.5h8V16" />
                @break
            @case('withdraw')
                <path d="M12 4v9M8.5 9.5L12 13l3.5-3.5" />
                <path d="M4.5 16.5v1.75A1.75 1.75 0 0 0 6.25 20h11.5a1.75 1.75 0 0 0 1.75-1.75V16.5" />
                @break
            @case('pie')
                <path d="M12 3.75A8.25 8.25 0 1 0 20.25 12H12z" />
                <path d="M14.75 3.9A8.25 8.25 0 0 1 20.1 9.5h-5.35z" />
                @break
            @case('shirt')
                <path d="M9 4.5 5 6.5l-1.5 4 3 1.2V20h11v-8.3l3-1.2-1.5-4L15 4.5a3 3 0 0 1-6 0z" />
                @break
            @case('paper')
                <path d="M7 3.5h7l4 4V20a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1z" />
                <path d="M13.5 3.5V8H18M9 12.5h6M9 16h4" />
                @break
            @default
                <circle cx="12" cy="12" r="7.5" />
        @endswitch
    </svg>

    <span class="truncate">{{ $label }}</span>

    @if ($badge !== null)
        <span class="notif-badge-count ml-auto flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1.5 text-[0.6875rem] font-bold text-white"
              data-notif-count="{{ $badge }}"
              style="display: {{ $badge > 0 ? 'flex' : 'none' }}">{{ $badge > 9 ? '9+' : $badge }}</span>
    @endif
</a>
