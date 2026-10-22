@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<section class="py-10 md:py-16" aria-labelledby="checkout-title">
    <div class="page-heading flex flex-wrap items-end justify-between gap-6 border-b border-[#ddd5c8] pb-8">
        <div>
            <p class="eyebrow mb-3">02 / Selesaikan pesanan</p>
            <h1 id="checkout-title" class="display text-5xl md:text-7xl">Sedikit lagi.</h1>
        </div>
        <a href="{{ route('cart.index') }}" class="text-link inline-flex items-center gap-2"><x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke keranjang</a>
    </div>

    @if (session('error'))
        <div class="notice mt-6 border-[#ac6958] text-[#8a3f30]" role="alert">{{ session('error') }}</div>
    @endif

    @if (!empty($cart))
        @php
            $subtotal = array_reduce($cart, fn ($total, $item) => $total + ($item['price'] * $item['quantity']), 0);
            $itemCount = array_sum(array_column($cart, 'quantity'));
        @endphp
        <div class="mt-10 grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_380px] lg:gap-20">
            <form id="checkout-form" action="{{ route('checkout.store') }}" method="POST" class="space-y-10">
                @csrf
                <fieldset>
                    <legend class="display mb-2 text-3xl md:text-4xl">Tujuan pengiriman</legend>
                    <p class="mb-7 text-sm text-[#75675c]">Tulis alamat lengkap dan nomor yang dapat dihubungi.</p>
                    <div class="space-y-6">
                        <div>
                            <label for="address" class="field-label">Alamat lengkap <span aria-hidden="true">*</span></label>
                            <textarea id="address" name="address" rows="4" maxlength="255" required autocomplete="street-address" class="field-input w-full resize-y @error('address') border-[#ac6958] @enderror" placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, dan kota" @error('address') aria-invalid="true" aria-describedby="address-error" @enderror>{{ old('address') }}</textarea>
                            @error('address')<p id="address-error" class="mt-2 text-sm text-[#8a3f30]">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="phone" class="field-label">Nomor telepon <span aria-hidden="true">*</span></label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" maxlength="20" required autocomplete="tel" inputmode="tel" class="field-input w-full @error('phone') border-[#ac6958] @enderror" placeholder="Contoh: 0812 3456 7890" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                            @error('phone')<p id="phone-error" class="mt-2 text-sm text-[#8a3f30]">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border-t border-[#ddd5c8] pt-9">
                    <legend class="display float-left mb-2 w-full text-3xl md:text-4xl">Cara pembayaran</legend>
                    <div class="clear-both">
                        <p class="mb-7 text-sm text-[#75675c]">Pilih metode pembayaran untuk pesanan Anda.</p>
                        <label for="payment_method" class="field-label">Metode pembayaran <span aria-hidden="true">*</span></label>
                        <select id="payment_method" name="payment_method" required class="field-input w-full @error('payment_method') border-[#ac6958] @enderror" @error('payment_method') aria-invalid="true" aria-describedby="payment-error" @enderror>
                            <option value="" disabled @selected(!old('payment_method'))>Pilih metode pembayaran</option>
                            <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Transfer bank</option>
                            <option value="e_wallet" @selected(old('payment_method') === 'e_wallet')>E-wallet</option>
                            <option value="cod" @selected(old('payment_method') === 'cod')>Bayar di tempat (COD)</option>
                        </select>
                        @error('payment_method')<p id="payment-error" class="mt-2 text-sm text-[#8a3f30]">{{ $message }}</p>@enderror
                        <p class="mt-4 text-sm leading-relaxed text-[#75675c]">Untuk pembayaran transfer, bukti pembayaran dapat diunggah melalui halaman Status Pesanan setelah pesanan dibuat.</p>
                    </div>
                </fieldset>
            </form>

            <aside class="panel border border-[#ddd5c8] bg-[#efe9de] p-7 md:p-8 lg:sticky lg:top-28" aria-labelledby="checkout-summary">
                <p class="eyebrow mb-3">{{ $itemCount }} buah / {{ count($cart) }} jenis</p>
                <h2 id="checkout-summary" class="display mb-7 text-3xl">Di dalam pesanan.</h2>
                <ul role="list" class="divide-y divide-[#d4cbbb] border-y border-[#d4cbbb]">
                    @foreach ($cart as $item)
                        <li class="flex items-start justify-between gap-5 py-5 text-sm">
                            <div><p class="font-medium">{{ $item['name'] }}</p><p class="mt-1 text-xs text-[#75675c]">{{ $item['quantity'] }} × Rp {{ number_format($item['price'], 0, ',', '.') }}</p></div>
                            <span class="shrink-0 tabular-nums">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-6">
                    <div class="flex items-end justify-between gap-4"><dt class="text-sm font-medium">Total pesanan</dt><dd class="text-2xl font-medium tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                </dl>
                <button type="submit" form="checkout-form" class="button-primary mt-7 flex w-full items-center justify-between gap-3">Buat pesanan <x-icon name="arrow-right" class="h-4 w-4" /></button>
                <a href="{{ route('cart.index') }}" class="text-link mt-5 block text-center text-sm">Ubah isi keranjang</a>
            </aside>
        </div>
    @else
        <div class="empty-state py-20 text-center">
            <x-icon name="bag" class="mx-auto mb-6 h-10 w-10 text-[#85543b]" />
            <h2 class="display mb-4 text-4xl">Belum ada yang dipesan.</h2>
            <p class="mb-8 text-[#75675c]">Tambahkan roti ke keranjang sebelum checkout.</p>
            <a href="{{ route('user.dashboard') }}#products" class="button-primary inline-flex items-center gap-3">Pilih roti <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
    @endif
</section>
@endsection
