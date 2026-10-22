<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#30251f">
    <title>@yield('page-title', 'Ringkasan') — Admin Abfis Bakery</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
    @stack('styles')
</head>
<body class="admin-body">
    <a href="#admin-content" class="skip-link">Lewati ke konten</a>
    <div class="admin-shell">
        <aside id="admin-sidebar" class="admin-sidebar" aria-label="Navigasi admin">
            <div class="admin-brand-row">
                <a href="{{ route('admin.dashboard') }}" class="brand" aria-label="Abfis Bakery, ringkasan admin">
                    <x-icon name="wheat" class="brand-symbol" />
                    <span class="brand-name">abfis<span class="brand-caption">RUANG KELOLA BAKERY</span></span>
                </a>
                <button type="button" class="admin-sidebar-close" data-sidebar-close aria-label="Tutup navigasi"><x-icon name="x" class="size-5" /></button>
            </div>
            <p class="eyebrow admin-nav-label">OPERASIONAL</p>
            <nav class="admin-nav" aria-label="Menu admin">
                @foreach (['dashboard' => ['Ringkasan', 'grid'], 'orders.index' => ['Pesanan', 'bag'], 'products.index' => ['Produk', 'package'], 'activities' => ['Aktivitas', 'clock']] as $route => [$label, $icon])
                    @php($active = request()->routeIs('admin.' . (str_contains($route, '.') ? explode('.', $route)[0] . '.*' : $route)))
                    <a href="{{ route('admin.' . $route) }}" @class(['admin-nav-link', 'is-active' => $active]) @if($active) aria-current="page" @endif>
                        <x-icon :name="$icon" class="size-[18px]" /><span>{{ $label }}</span><x-icon name="arrow-right" class="admin-nav-arrow size-3.5" />
                    </a>
                @endforeach
            </nav>
            <div class="admin-sidebar-bottom">
                <a href="{{ route('user.dashboard') }}" target="_blank" rel="noopener noreferrer" class="admin-store-link"><x-icon name="store" class="size-[18px]" /> Lihat toko <x-icon name="arrow-up-right" class="ml-auto size-4" /></a>
                <div class="admin-profile">
                    <span class="admin-avatar">{{ mb_strtoupper(mb_substr(Auth::guard('admin')->user()?->name ?? 'A', 0, 1)) }}</span>
                    <div class="min-w-0 flex-1"><p class="truncate text-xs">{{ Auth::guard('admin')->user()?->name ?? 'Administrator' }}</p><p class="mt-1 text-[10px] text-[#b6a895]">Administrator</p></div>
                    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="p-2 hover:text-white" aria-label="Keluar dari admin"><x-icon name="logout" class="size-4" /></button></form>
                </div>
                <p class="admin-sidebar-caption">Dari dapur, untuk setiap hari.</p>
            </div>
        </aside>
        <button type="button" class="admin-sidebar-overlay" data-sidebar-close aria-label="Tutup navigasi" tabindex="-1" hidden></button>
        <div class="admin-workspace">
            <header class="admin-topbar">
                <div class="flex min-w-0 items-center gap-4">
                    <button type="button" class="admin-sidebar-toggle" data-sidebar-toggle aria-expanded="false" aria-controls="admin-sidebar" aria-label="Buka navigasi admin"><x-icon name="menu" class="size-5" /></button>
                    <p class="admin-breadcrumb"><span>Ruang kelola</span><span class="mx-3 text-[#b7ac9c]">/</span>@yield('page-title', 'Ringkasan')</p>
                </div>
                <div class="admin-topbar-date"><span class="status-dot"></span>{{ now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}</div>
            </header>
            <main id="admin-content" class="admin-content" tabindex="-1">
                @yield('content')
            </main>
            <footer class="admin-footer"><span>© {{ date('Y') }} Abfis Bakery</span><span>Dibuat dengan hati. Dikelola dengan rapi.</span></footer>
        </div>
    </div>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
