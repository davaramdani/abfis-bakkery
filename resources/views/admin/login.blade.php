@extends('layouts.guest')
@section('title', 'Masuk admin')
@section('aside-eyebrow', 'DARI BALIK DAPUR ABFIS')
@section('aside-title')Rapi di balik<br>hangatnya roti.@endsection
@section('aside-description', 'Produk tercatat. Pesanan terpantau. Beri ruang untuk bagian terbaiknya: membuat roti.')
@section('content')
    <p class="eyebrow mb-5 flex items-center gap-2 text-rust"><x-icon name="lock" class="size-4" /> RUANG KELOLA ABFIS</p>
    <h1 class="display text-[48px] leading-[1.1] sm:text-[56px]">Selamat datang<br>di balik dapur.</h1>
    <p class="mb-9 mt-5 text-sm leading-7 text-muted">Masuk untuk mengelola produk, memeriksa pesanan, dan mencatat kabar dari toko.</p>
    @include('admin.partials.feedback')
    <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-6">
        @csrf
        <div>
            <label for="admin-email" class="field-label">Email admin</label>
            <input id="admin-email" type="email" name="email" value="{{ old('email') }}" class="field-input" placeholder="Email akun admin Anda" autocomplete="username" required @error('email') aria-invalid="true" @enderror>
        </div>
        <div>
            <label for="admin-password" class="field-label">Kata sandi</label>
            <div class="relative">
                <input id="admin-password" type="password" name="password" class="field-input pr-20" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                <button type="button" data-password-toggle="admin-password" aria-controls="admin-password" aria-pressed="false" aria-label="Tampilkan kata sandi" class="absolute inset-y-0 right-0 px-4 text-xs font-medium text-rust">Lihat</button>
            </div>
        </div>
        <button type="submit" class="button-primary w-full justify-between">Masuk ke ruang kelola <x-icon name="arrow-right" class="size-4" /></button>
    </form>
    <p class="mt-7 border-t border-line pt-5 text-xs leading-6 text-muted">Halaman ini untuk pengelola Abfis Bakery.</p>
@endsection
