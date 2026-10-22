@extends('layouts.guest')

@php($isRegister = request()->routeIs('register'))
@section('title', $isRegister ? 'Buat akun' : 'Masuk')

@section('content')
    <div class="mb-9">
        <p class="eyebrow mb-4">Akun Abfis</p>
        <h1 class="display text-[44px] leading-[1.08] tracking-[-0.045em] sm:text-[52px]">{{ $isRegister ? 'Senang berkenalan.' : 'Selamat datang kembali.' }}</h1>
        <p class="mt-5 max-w-sm text-sm leading-7 text-[#786b5e]">{{ $isRegister ? 'Buat akun untuk memesan roti favorit dan melihat riwayat pesanan Anda.' : 'Masuk untuk melanjutkan pesanan dan melihat kabar dari dapur kami.' }}</p>
    </div>

    <nav class="mb-8 grid grid-cols-2 border-b border-[#ddd5c8]" aria-label="Pilihan akun">
        <a href="{{ route('login') }}" @if (!$isRegister) aria-current="page" @endif class="-mb-px border-b-2 py-3 text-center text-sm font-semibold transition-colors {{ !$isRegister ? 'border-[#85543b] text-[#85543b]' : 'border-transparent text-[#8c7e70] hover:text-[#30251f]' }}">Masuk</a>
        <a href="{{ route('register') }}" @if ($isRegister) aria-current="page" @endif class="-mb-px border-b-2 py-3 text-center text-sm font-semibold transition-colors {{ $isRegister ? 'border-[#85543b] text-[#85543b]' : 'border-transparent text-[#8c7e70] hover:text-[#30251f]' }}">Buat akun</a>
    </nav>

    @if (session('success'))
        <div class="notice mb-6 flex items-start gap-3" role="status">
            <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="notice mb-6 border-[#c88f76] text-[#85543b]" role="alert">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ $isRegister ? route('register.submit') : route('login.submit') }}" class="space-y-5">
        @csrf
        @if ($isRegister)
            <div>
                <label for="auth-name" class="field-label">Nama lengkap</label>
                <input id="auth-name" type="text" name="name" value="{{ old('name') }}" class="field-input @error('name') border-[#ac583c] @enderror" placeholder="Nama Anda" autocomplete="name" maxlength="255" required @error('name') aria-invalid="true" aria-describedby="auth-name-error" @enderror>
                @error('name')
                    <p id="auth-name-error" class="mt-2 text-xs leading-5 text-[#a34830]" role="alert">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div>
            <label for="auth-email" class="field-label">Alamat email</label>
            <input id="auth-email" type="email" name="email" value="{{ old('email') }}" class="field-input @error('email') border-[#ac583c] @enderror" placeholder="nama@email.com" autocomplete="email" maxlength="255" required @error('email') aria-invalid="true" aria-describedby="auth-email-error" @enderror>
            @error('email')
                <p id="auth-email-error" class="mt-2 text-xs leading-5 text-[#a34830]" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="auth-password" class="field-label">Kata sandi</label>
            <div class="relative">
                <input id="auth-password" type="password" name="password" class="field-input pr-20 @error('password') border-[#ac583c] @enderror" placeholder="{{ $isRegister ? 'Minimal 8 karakter' : 'Masukkan kata sandi' }}" autocomplete="{{ $isRegister ? 'new-password' : 'current-password' }}" @if ($isRegister) minlength="8" @endif required @error('password') aria-invalid="true" aria-describedby="auth-password-error" @enderror>
                <button type="button" data-password-toggle="auth-password" aria-controls="auth-password" aria-pressed="false" aria-label="Tampilkan kata sandi" class="absolute inset-y-0 right-0 px-4 text-[11px] font-semibold text-[#85543b] transition-colors hover:text-[#30251f]">Lihat</button>
            </div>
            @error('password')
                <p id="auth-password-error" class="mt-2 text-xs leading-5 text-[#a34830]" role="alert">{{ $message }}</p>
            @enderror
        </div>

        @if ($isRegister)
            <div>
                <label for="auth-password-confirmation" class="field-label">Ulangi kata sandi</label>
                <div class="relative">
                    <input id="auth-password-confirmation" type="password" name="password_confirmation" class="field-input pr-20" placeholder="Ketik kata sandi sekali lagi" autocomplete="new-password" minlength="8" required>
                    <button type="button" data-password-toggle="auth-password-confirmation" aria-controls="auth-password-confirmation" aria-pressed="false" aria-label="Tampilkan konfirmasi kata sandi" class="absolute inset-y-0 right-0 px-4 text-[11px] font-semibold text-[#85543b] transition-colors hover:text-[#30251f]">Lihat</button>
                </div>
            </div>
        @endif

        <div class="pt-2">
            <button type="submit" class="button-primary flex w-full items-center justify-between">
                <span>{{ $isRegister ? 'Buat akun saya' : 'Masuk ke akun' }}</span>
                <x-icon name="arrow-right" class="h-4 w-4" />
            </button>
        </div>
    </form>

    <p class="mt-7 text-center text-xs leading-6 text-[#786b5e]">
        {{ $isRegister ? 'Sudah punya akun?' : 'Baru di Abfis?' }}
        <a href="{{ $isRegister ? route('login') : route('register') }}" class="text-link ml-1">{{ $isRegister ? 'Masuk di sini' : 'Buat akun dulu' }}</a>
    </p>
@endsection
