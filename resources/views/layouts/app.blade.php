<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f4ed">
    <meta name="description" content="Roti, kue, dan teman minum kopi dari Abfis Bakery, Gunung Sindur, Bogor. Pilih favorit Anda dan pesan langsung di sini.">
    <title>@yield('title', 'Roti untuk hari yang baik') — Abfis Bakery</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;450;500;550;600;650;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <a href="#main-content" class="skip-link">Lewati ke konten</a>

    <div class="announcement">
        <span class="status-dot"></span>
        Sedikit tepung. Banyak cerita. Selamat datang di Abfis Bakery.
    </div>

    <header class="site-header">
        <div class="header-inner">
            <a href="{{ route('user.dashboard') }}" class="brand" aria-label="Abfis Bakery, beranda">
                <x-icon name="wheat" class="brand-symbol" />
                <span class="brand-name">abfis<span class="brand-caption">BAKERY & GOOD THINGS</span></span>
            </a>

            <nav class="desktop-nav" aria-label="Navigasi utama">
                <a
                    href="{{ route('user.dashboard') }}#products"
                    @class(['nav-link', 'is-active' => request()->routeIs('user.dashboard', 'dashboard', 'products.show')])
                >Pilihan kami</a>
                <a href="{{ route('user.dashboard') }}#cerita" class="nav-link">Cerita Abfis</a>
                <a href="#kunjungi" class="nav-link">Kunjungi kami</a>
            </nav>

            <div class="header-actions">
                @auth
                    <details class="account-menu">
                        <summary class="account-link">
                            <x-icon name="user" class="size-4" />
                            <span class="account-name">{{ Str::limit(Auth::user()->name, 15) }}</span>
                            <x-icon name="chevron-down" class="size-3" />
                        </summary>
                        <div class="account-dropdown">
                            <a href="{{ route('orders.history') }}">Riwayat pesanan</a>
                            <a href="{{ route('orders.status') }}">Status & pembayaran</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">Keluar</button>
                            </form>
                        </div>
                    </details>
                @else
                    <a href="{{ route('login') }}" class="account-link">
                        <x-icon name="user" class="size-4" />
                        <span class="account-name">Masuk</span>
                    </a>
                @endauth

                <a
                    href="{{ route('cart.index') }}"
                    class="cart-link"
                    aria-label="Keranjang, {{ collect(session('cart', []))->sum('quantity') }} produk"
                >
                    <x-icon name="bag" class="size-5" />
                    <span class="cart-count">{{ collect(session('cart', []))->sum('quantity') }}</span>
                </a>
                <button
                    type="button"
                    class="mobile-menu-toggle"
                    aria-expanded="false"
                    aria-controls="mobile-nav"
                    aria-label="Buka menu"
                    data-menu-toggle
                >
                    <x-icon name="menu" class="size-5" />
                </button>
            </div>
        </div>

        <nav id="mobile-nav" class="mobile-nav" aria-label="Navigasi ponsel" hidden>
            <a href="{{ route('user.dashboard') }}#products">
                Pilihan kami <x-icon name="arrow-right" class="size-4" />
            </a>
            <a href="{{ route('user.dashboard') }}#cerita">
                Cerita Abfis <x-icon name="arrow-right" class="size-4" />
            </a>
            <a href="#kunjungi">
                Kunjungi kami <x-icon name="arrow-right" class="size-4" />
            </a>
            @auth
                <a href="{{ route('orders.history') }}">Riwayat pesanan</a>
                <a href="{{ route('orders.status') }}">Status & pembayaran</a>
            @else
                <a href="{{ route('register') }}">Buat akun</a>
            @endauth
        </nav>
    </header>

    <main id="main-content" class="site-main" tabindex="-1">
        @yield('content')
    </main>

    <footer id="kunjungi" class="site-footer">
        <div class="footer-inner">
            <div class="footer-top">
                <div>
                    <p class="eyebrow mb-5">ADA YANG LEBIH ENAK DARI SEKADAR MENYAPA.</p>
                    <h2 class="display footer-heading">Mampir, <em>yuk.</em></h2>
                    <a href="tel:082125964315" class="text-link mt-6 inline-flex">
                        0821-2596-4315 <x-icon name="arrow-up-right" class="size-4" />
                    </a>
                </div>

                <div class="footer-address">
                    <p class="eyebrow mb-5">TEMUKAN DAPUR KAMI</p>
                    <address class="not-italic leading-7">
                        Jl. Perum Taman Sari Bukit Damai<br>
                        No. 55, Blok B3, Padurenan<br>
                        Gunung Sindur, Bogor, Jawa Barat 16340
                    </address>
                    <a
                        href="https://www.google.com/maps/search/?api=1&query=Abfis+Bakery+Taman+Sari+Bukit+Damai+Bogor"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-link mt-5 inline-flex"
                    >
                        Buka di Google Maps <x-icon name="arrow-up-right" class="size-4" />
                    </a>
                </div>

                <div class="footer-links">
                    <p class="eyebrow mb-5">DARI ABFIS</p>
                    <a href="{{ route('user.dashboard') }}#products">Pilihan roti & kue</a>
                    <a href="{{ route('user.dashboard') }}#cerita">Cerita kami</a>
                    <a href="{{ route('orders.history') }}">Pesanan Anda</a>
                    <a href="mailto:support@abfis.com">support@abfis.com</a>
                </div>
            </div>

            <div class="footer-bottom">
                <a href="{{ route('user.dashboard') }}" class="footer-wordmark display">abfis<span>bakery.</span></a>
                <p>Roti yang baik. Hari yang lebih baik.</p>
                <p>© {{ date('Y') }} Abfis Bakery</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
    @yield('scripts')
</body>
</html>
