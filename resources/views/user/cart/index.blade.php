@extends('layouts.app')

@section('title', 'Keranjang Belanja')

@section('content')
<section class="py-10 md:py-16" aria-labelledby="cart-title">
    <div class="page-heading flex flex-wrap items-end justify-between gap-6 border-b border-[#ddd5c8] pb-8">
        <div>
            <p class="eyebrow mb-3">01 / Keranjang belanja</p>
            <h1 id="cart-title" class="display text-5xl md:text-7xl">Pilihan Anda.</h1>
        </div>
        <a href="{{ route('user.dashboard') }}#products" class="text-link inline-flex items-center gap-2">
            <x-icon name="arrow-left" class="h-4 w-4" /> Lanjut belanja
        </a>
    </div>

    @if (session('success'))
        <div class="notice mt-6 flex items-start gap-3" role="status">
            <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0" />
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="notice mt-6 border-[#ac6958] text-[#8a3f30]" role="alert">{{ session('error') }}</div>
    @endif

    @if (empty($cart))
        <div class="empty-state py-20 text-center md:py-28">
            <x-icon name="bag" class="mx-auto mb-6 h-10 w-10 text-[#85543b]" />
            <h2 class="display mb-4 text-4xl md:text-5xl">Masih ada ruang untuk roti.</h2>
            <p class="mx-auto mb-8 max-w-sm text-[#75675c]">Keranjang Anda masih kosong. Pilih roti yang ingin dibawa pulang.</p>
            <a href="{{ route('user.dashboard') }}#products" class="button-primary inline-flex items-center gap-3">Lihat pilihan roti <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
    @else
        @php
            $subtotal = array_reduce($cart, fn ($total, $item) => $total + ($item['price'] * $item['quantity']), 0);
            $itemCount = array_sum(array_column($cart, 'quantity'));
        @endphp
        <div class="mt-9 grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-14">
            <div>
                <div class="hidden grid-cols-[minmax(0,1fr)_120px_125px] gap-5 border-b border-[#ddd5c8] pb-4 text-xs font-medium uppercase tracking-[0.14em] text-[#75675c] md:grid">
                    <span>Produk</span><span>Jumlah</span><span class="text-right">Subtotal</span>
                </div>
                <ul role="list" class="divide-y divide-[#ddd5c8] border-b border-[#ddd5c8]">
                    @foreach ($cart as $id => $item)
                        <li class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-5 gap-y-5 py-6 md:grid-cols-[minmax(0,1fr)_120px_125px] md:py-7">
                            <div class="col-span-2 flex min-w-0 items-center gap-5 md:col-span-1">
                                <a href="{{ route('products.show', $id) }}" class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden bg-[#eae4d8] sm:h-28 sm:w-28" aria-label="Lihat {{ $item['name'] }}">
                                    @if (!empty($item['image']))
                                        <img src="{{ asset('storage/products/' . $item['image']) }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover transition-transform duration-500 hover:scale-105">
                                    @else
                                        <x-icon name="package" class="h-8 w-8 text-[#85543b]" />
                                    @endif
                                </a>
                                <div class="min-w-0">
                                    <a href="{{ route('products.show', $id) }}" class="display text-2xl transition-colors hover:text-[#85543b]">{{ $item['name'] }}</a>
                                    <p class="mt-2 text-sm text-[#75675c]">Rp {{ number_format($item['price'], 0, ',', '.') }} / buah</p>
                                    <form action="{{ route('cart.remove', $id) }}" method="POST" class="mt-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs text-[#75675c] underline-offset-4 transition-colors hover:text-[#85543b] hover:underline" aria-label="Hapus {{ $item['name'] }} dari keranjang">
                                            <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="inline-flex w-fit items-center border border-[#ddd5c8]">
                                <form action="{{ route('cart.update', $id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" name="action" value="decrease" class="flex h-9 w-9 items-center justify-center transition-colors hover:bg-[#eae4d8] disabled:cursor-not-allowed disabled:opacity-30" aria-label="Kurangi jumlah {{ $item['name'] }}" @disabled($item['quantity'] <= 1)>
                                        <x-icon name="minus" class="h-3.5 w-3.5" />
                                    </button>
                                </form>
                                <span class="min-w-8 text-center text-sm tabular-nums" aria-label="Jumlah">{{ $item['quantity'] }}</span>
                                <form action="{{ route('cart.update', $id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" name="action" value="increase" class="flex h-9 w-9 items-center justify-center transition-colors hover:bg-[#eae4d8]" aria-label="Tambah jumlah {{ $item['name'] }}">
                                        <x-icon name="plus" class="h-3.5 w-3.5" />
                                    </button>
                                </form>
                            </div>
                            <p class="text-right text-sm font-medium tabular-nums">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</p>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-5 text-sm text-[#75675c]">{{ count($cart) }} jenis roti, {{ $itemCount }} buah dalam keranjang.</p>
            </div>

            <aside class="panel border border-[#ddd5c8] bg-[#efe9de] p-7 lg:sticky lg:top-28" aria-labelledby="summary-title">
                <p class="eyebrow mb-5">Ringkasan</p>
                <h2 id="summary-title" class="display mb-7 text-3xl">Siap dipesan?</h2>
                <dl class="space-y-4 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-[#75675c]">Jumlah produk</dt><dd>{{ $itemCount }} buah</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-[#75675c]">Subtotal</dt><dd class="tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                    <div class="flex items-end justify-between gap-4 border-t border-[#d4cbbb] pt-5"><dt class="font-medium">Total pesanan</dt><dd class="text-xl font-medium tabular-nums">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                </dl>
                <a href="{{ route('checkout.index') }}" class="button-primary mt-7 flex w-full items-center justify-between gap-3">Lanjut ke checkout <x-icon name="arrow-right" class="h-4 w-4" /></a>
                <p class="mt-4 text-xs leading-relaxed text-[#75675c]">Alamat dan metode pembayaran diisi pada langkah berikutnya.</p>
            </aside>
        </div>
    @endif
</section>
@endsection
