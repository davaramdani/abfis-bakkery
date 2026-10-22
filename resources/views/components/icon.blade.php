@props(['name' => 'arrow-right'])

<svg
    {{ $attributes->class(['icon'])->merge([
        'viewBox' => '0 0 24 24',
        'fill' => 'none',
        'stroke' => 'currentColor',
        'stroke-width' => '1.5',
        'stroke-linecap' => 'round',
        'stroke-linejoin' => 'round',
        'aria-hidden' => 'true'
    ]) }}
>
    @switch($name)
        @case('arrow-right')
            <path d="M4 12h16m-6-6 6 6-6 6" />
            @break

        @case('arrow-left')
            <path d="M20 12H4m6-6-6 6 6 6" />
            @break

        @case('arrow-up-right')
            <path d="M6 18 18 6M6 6h12v12" />
            @break

        @case('bag')
            <path d="M5 7h14l1 14H4L5 7Z" />
            <path d="M8 8V6a4 4 0 0 1 8 0v2" />
            @break

        @case('user')
            <circle cx="12" cy="8" r="3.5" />
            <path d="M5 21v-2a7 7 0 0 1 14 0v2" />
            @break

        @case('search')
            <circle cx="10.5" cy="10.5" r="6.5" />
            <path d="m16 16 5 5" />
            @break

        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break

        @case('minus')
            <path d="M5 12h14" />
            @break

        @case('x')
            <path d="m6 6 12 12M6 18 18 6" />
            @break

        @case('check')
            <path d="m5 12 4 4L19 6" />
            @break

        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break

        @case('chevron-down')
            <path d="m6 9 6 6 6-6" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
            @break

        @case('upload')
            <path d="M12 16V3m-4 4 4-4 4 4M4 15v6h16v-6" />
            @break

        @case('trash')
            <path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7" />
            @break

        @case('package')
            <path d="m3 7 9-5 9 5v10l-9 5-9-5V7Zm0 0 9 5 9-5M12 12v10M7 4.8l10 5.5v4" />
            @break

        @case('heart')
            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z" />
            @break

        @case('coffee')
            <path d="M3 8h13v7a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5V8Zm13 1h2a3 3 0 1 1 0 6h-2M6 2v3m4-3v3m4-3v3M2 23h17" />
            @break

        @case('grid')
            <rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /><rect x="14" y="14" width="7" height="7" />
            @break
        @case('store')
            <path d="M3 10h18l-2-7H5l-2 7Zm1 0v11h16V10M9 21v-7h6v7M8 3l-1 7m9-7 1 7" />
            @break
        @case('lock')
            <rect x="5" y="10" width="14" height="11" rx="1" /><path d="M8 10V6a4 4 0 0 1 8 0v4m-4 5v2" />
            @break
        @case('logout')
            <path d="M10 4H4v16h6m5-13 5 5-5 5M9 12h11" />
            @break
        @case('wallet')
            <path d="M20 8V4H5a3 3 0 0 0 0 6h16v10H5a3 3 0 0 1-3-3V7m19 6h-5v4h5" /><path d="M17 15h.01" />
            @break
        @case('edit')
            <path d="m15 4 5 5M4 20l5-1L21 7a2.1 2.1 0 0 0-5-5L4 14l-1 7Z" />
            @break
        @case('image')
            <rect x="3" y="3" width="18" height="18" /><circle cx="8" cy="8" r="1.5" /><path d="m3 17 6-5 4 4 3-3 5 4" />
            @break
        @case('wheat')
            <path d="M12 22V3m0 8C6 11 5 7 5 4c5 0 7 3 7 7Zm0 6c-6 0-8-3-8-6 5 0 8 3 8 6Zm0-6c6 0 7-4 7-7-5 0-7 3-7 7Zm0 6c6 0 8-3 8-6-5 0-8 3-8 6ZM12 4V1" />
            @break
    @endswitch
</svg>
