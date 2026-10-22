<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f7f4ed">
    <title>@yield('title', 'Masuk') — Abfis Bakery</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-[#f7f4ed] text-[#30251f] antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-5 focus:top-5 focus:z-50 focus:bg-white focus:p-4">Langsung ke formulir</a>
    <div class="min-h-screen lg:grid lg:grid-cols-[1.05fr_1fr]">
        <aside class="relative hidden min-h-screen flex-col justify-between overflow-hidden bg-[#30251f] p-12 text-[#fffaf1] lg:flex xl:p-16" aria-label="Abfis Bakery">
            <img src="{{ asset('images/bakery-hero.jpg') }}" alt="Roti buatan Abfis Bakery yang siap dinikmati" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-b from-[#21180f]/35 via-[#21180f]/10 to-[#21180f]/80" aria-hidden="true"></div>
            <a href="{{ route('user.dashboard') }}" class="relative w-fit text-[#fffaf1] transition-opacity hover:opacity-75" aria-label="Abfis Bakery, kembali ke beranda">
                <span class="display block text-[37px] font-semibold leading-none tracking-[-0.055em]">abfis<span class="text-[#dcbda0]">.</span></span>
                <span class="mt-2 block text-[10px] font-medium uppercase tracking-[0.3em]">Bakery &amp; Homemade</span>
            </a>
            <div class="relative max-w-lg pt-32">
                <p class="mb-5 text-[10px] font-semibold uppercase tracking-[0.24em] text-[#e9d9c7]">@yield('aside-eyebrow', 'Sederhana. Hangat. Abfis.')</p>
                <h2 class="display text-6xl leading-[1.08] tracking-[-0.045em] xl:text-7xl">@yield('aside-title', 'Teman manis<br>di sela hari.')</h2>
                <p class="mt-6 max-w-[310px] text-sm leading-7 text-[#eee2d4]">@yield('aside-description', 'Pilih roti favorit, pesan dari rumah, lalu nikmati bersama orang tersayang.')</p>
                <div class="mt-10 flex items-center justify-between border-t border-white/30 pt-5 text-[10px] uppercase tracking-[0.18em] text-[#e9d9c7]">
                    <span>Abfis Bakery</span>
                    <span>Dari dapur, dengan hati</span>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen flex-col px-6 py-7 sm:px-12 lg:px-12 lg:py-10 xl:px-20">
            <header class="flex items-center justify-between gap-6">
                <a href="{{ route('user.dashboard') }}" class="display text-[32px] font-semibold leading-none tracking-[-0.055em] lg:hidden" aria-label="Abfis Bakery, kembali ke beranda">abfis<span class="text-[#85543b]">.</span></a>
                <a href="{{ route('user.dashboard') }}" class="ml-auto inline-flex items-center gap-2 text-xs text-[#706459] transition-colors hover:text-[#85543b]">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali ke toko
                </a>
            </header>
            <main id="main-content" class="mx-auto flex w-full max-w-[440px] flex-1 flex-col justify-center py-14 sm:py-16 lg:py-12" tabindex="-1">
                @yield('content')
            </main>
            <footer class="mx-auto w-full max-w-[440px] border-t border-[#ddd5c8] pt-5 text-[11px] text-[#87796b]">
                &copy; {{ date('Y') }} Abfis Bakery. Dibuat dengan hati.
            </footer>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
