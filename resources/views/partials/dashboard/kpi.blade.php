{{--
    Carte KPI commune aux dashboards.
    Variables : $label, $value, $meta (optionnel), $tone (default|gold|danger|success|warning|info),
                $href (optionnel), $onclick (optionnel).
--}}
@php
    $kpiTag = !empty($href) ? 'a' : (!empty($onclick) ? 'button' : 'div');
    $kpiTone = match ($tone ?? 'default') {
        'gold' => 'text-pharaoh-gold',
        'danger' => 'text-red-600',
        'success' => 'text-green-700',
        'warning' => 'text-orange-600',
        'info' => 'text-blue-700',
        default => 'text-[#080808]',
    };
@endphp

<{{ $kpiTag }}
    @if ($kpiTag === 'a') href="{{ $href }}" @endif
    @if ($kpiTag === 'button') type="button" onclick="{{ $onclick }}" @endif
    class="console-kpi {{ $kpiTag === 'button' ? 'w-full text-left' : '' }}"
>
    <span class="console-kpi-accent" aria-hidden="true"></span>
    <span class="console-kpi-label">{{ $label }}</span>
    <span class="console-kpi-value {{ $kpiTone }}">{{ $value }}</span>
    @if (!empty($meta))
        <span class="console-kpi-meta">{{ $meta }}</span>
    @endif
</{{ $kpiTag }}>
