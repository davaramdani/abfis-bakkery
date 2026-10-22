@extends('layouts.app')

@section('title', 'Riwayat Pesanan')

@section('content')
<section class="py-10 md:py-16" aria-labelledby="history-title">
    <div class="page-heading flex flex-wrap items-end justify-between gap-6 pb-8">
        <div>
            <p class="eyebrow mb-3">Catatan belanja</p>
            <h1 id="history-title" class="display text-5xl md:text-7xl">Pesanan Anda.</h1>
        </div>
        <a href="{{ route('user.dashboard') }}#products" class="text-link inline-flex items-center gap-2">Pesan roti lagi <x-icon name="arrow-right" class="h-4 w-4" /></a>
    </div>

    <nav class="flex gap-7 border-b border-[#ddd5c8] text-sm" aria-label="Halaman pesanan">
        <a href="{{ route('orders.history') }}" aria-current="page" class="border-b-2 border-[#85543b] pb-4 font-medium text-[#85543b]">Semua pesanan <span class="ml-1 text-xs">({{ $orders->count() }})</span></a>
        <a href="{{ route('orders.status') }}" class="pb-4 text-[#75675c] transition-colors hover:text-[#85543b]">Status pembayaran</a>
    </nav>

    @if ($orders->isEmpty())
        <div class="empty-state py-20 text-center md:py-28">
            <x-icon name="package" class="mx-auto mb-6 h-10 w-10 text-[#85543b]" />
            <h2 class="display mb-4 text-4xl md:text-5xl">Cerita pertama dimulai dari roti.</h2>
            <p class="mx-auto mb-8 max-w-sm text-[#75675c]">Anda belum memiliki pesanan. Semua catatan belanja akan tersimpan di sini.</p>
            <a href="{{ route('user.dashboard') }}#products" class="button-primary inline-flex items-center gap-3">Lihat pilihan roti <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
    @else
        @php
            $statusMapping = [
                'approved' => ['label' => 'Disetujui', 'class' => 'border-[#b0b99e] bg-[#e9eddf] text-[#4c5e38]', 'icon' => 'check'],
                'completed' => ['label' => 'Selesai', 'class' => 'border-[#b0b99e] bg-[#e9eddf] text-[#4c5e38]', 'icon' => 'check'],
                'rejected' => ['label' => 'Ditolak', 'class' => 'border-[#d9b7a9] bg-[#f2e3db] text-[#8a3f30]', 'icon' => 'x'],
                'pending' => ['label' => 'Menunggu pembayaran', 'class' => 'border-[#d6c3a0] bg-[#eee4ce] text-[#795d2d]', 'icon' => 'clock'],
                'pending_verification' => ['label' => 'Menunggu verifikasi', 'class' => 'border-[#d6c3a0] bg-[#eee4ce] text-[#795d2d]', 'icon' => 'clock'],
            ];
            $paymentMethods = ['bank_transfer' => 'Transfer bank', 'credit_card' => 'Kartu kredit', 'e_wallet' => 'E-wallet', 'cod' => 'Bayar di tempat', 'cash' => 'Tunai'];
        @endphp
        <div class="mt-8 space-y-6">
            @foreach ($orders as $order)
                @php
                    $currentStatus = $statusMapping[$order->payment_status] ?? ['label' => ucfirst($order->payment_status), 'class' => 'border-[#ddd5c8] bg-[#efe9de] text-[#75675c]', 'icon' => 'clock'];
                    if ($order->payment_status === 'pending' && $order->payment_proof) {
                        $currentStatus['label'] = 'Menunggu pemeriksaan';
                    }
                    $quantity = $order->products->sum(fn ($product) => $product->pivot->quantity);
                @endphp
                <article class="panel border border-[#ddd5c8]" aria-labelledby="order-{{ $order->id_order }}">
                    <header class="flex flex-wrap items-start justify-between gap-5 p-6 md:p-8">
                        <div>
                            <p class="eyebrow mb-2">{{ $order->created_at->tz('Asia/Jakarta')->format('d M Y · H:i') }} WIB</p>
                            <h2 id="order-{{ $order->id_order }}" class="display text-3xl">Pesanan #{{ $order->id_order }}</h2>
                            <p class="mt-2 text-sm text-[#75675c]">{{ $quantity }} buah · {{ $paymentMethods[$order->payment_method] ?? ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                        </div>
                        <div class="flex flex-col items-start gap-4 sm:items-end">
                            <span class="inline-flex items-center gap-2 border px-3 py-1.5 text-xs {{ $currentStatus['class'] }}"><x-icon :name="$currentStatus['icon']" class="h-3.5 w-3.5" /> {{ $currentStatus['label'] }}</span>
                            <p class="text-lg font-medium tabular-nums">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                        </div>
                    </header>
                    <details class="group border-t border-[#ddd5c8]">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-4 text-sm font-medium transition-colors hover:bg-[#efe9de] md:px-8 [&::-webkit-details-marker]:hidden">
                            Rincian pesanan <x-icon name="chevron-down" class="h-4 w-4 transition-transform group-open:rotate-180" />
                        </summary>
                        <div class="border-t border-[#ddd5c8] px-6 pb-6 md:px-8 md:pb-8">
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[440px] text-left text-sm">
                                    <caption class="sr-only">Produk dalam pesanan #{{ $order->id_order }}</caption>
                                    <thead><tr class="border-b border-[#ddd5c8] text-xs uppercase tracking-[0.12em] text-[#75675c]"><th scope="col" class="py-4 font-medium">Produk</th><th scope="col" class="px-4 py-4 text-right font-medium">Harga</th><th scope="col" class="px-4 py-4 text-right font-medium">Jumlah</th><th scope="col" class="py-4 text-right font-medium">Subtotal</th></tr></thead>
                                    <tbody class="divide-y divide-[#ddd5c8]">
                                        @foreach ($order->products as $product)
                                            <tr><th scope="row" class="py-4 font-medium">{{ $product->name }}</th><td class="whitespace-nowrap px-4 py-4 text-right tabular-nums">Rp {{ number_format($product->pivot->price_at_order, 0, ',', '.') }}</td><td class="px-4 py-4 text-right tabular-nums">{{ $product->pivot->quantity }}</td><td class="whitespace-nowrap py-4 text-right tabular-nums">Rp {{ number_format($product->pivot->price_at_order * $product->pivot->quantity, 0, ',', '.') }}</td></tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot><tr class="border-t border-[#ddd5c8]"><th scope="row" colspan="3" class="py-5 font-medium">Total pesanan</th><td class="whitespace-nowrap py-5 text-right font-medium tabular-nums">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td></tr></tfoot>
                                </table>
                            </div>
                            @if ($order->address)
                                <dl class="grid gap-5 border-t border-[#ddd5c8] pt-5 text-sm sm:grid-cols-[minmax(0,1fr)_auto]">
                                    <div><dt class="mb-2 text-xs uppercase tracking-[0.12em] text-[#75675c]">Alamat pengiriman</dt><dd class="max-w-xl leading-relaxed">{{ $order->address }}</dd></div>
                                    <div><dt class="mb-2 text-xs uppercase tracking-[0.12em] text-[#75675c]">Telepon</dt><dd>{{ $order->phone }}</dd></div>
                                </dl>
                            @endif
                            @if ($order->payment_status === 'pending')
                                <a href="{{ route('orders.status') }}" class="text-link mt-6 inline-flex items-center gap-2 text-sm">Lihat status pembayaran <x-icon name="arrow-right" class="h-4 w-4" /></a>
                            @endif
                        </div>
                    </details>
                </article>
            @endforeach
        </div>
    @endif
</section>
@endsection
