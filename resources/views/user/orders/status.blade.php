@extends('layouts.app')

@section('title', 'Status Pesanan')

@section('content')
<section class="py-10 md:py-16" aria-labelledby="status-title">
    <div class="page-heading flex flex-wrap items-end justify-between gap-6 pb-8">
        <div>
            <p class="eyebrow mb-3">Pembayaran & konfirmasi</p>
            <h1 id="status-title" class="display text-5xl md:text-7xl">Satu langkah lagi.</h1>
        </div>
        <a href="{{ route('user.dashboard') }}#products" class="text-link inline-flex items-center gap-2">Kembali ke toko <x-icon name="arrow-right" class="h-4 w-4" /></a>
    </div>

    <nav class="flex gap-7 border-b border-[#ddd5c8] text-sm" aria-label="Halaman pesanan">
        <a href="{{ route('orders.history') }}" class="pb-4 text-[#75675c] transition-colors hover:text-[#85543b]">Semua pesanan</a>
        <a href="{{ route('orders.status') }}" aria-current="page" class="border-b-2 border-[#85543b] pb-4 font-medium text-[#85543b]">Status pembayaran <span class="ml-1 text-xs">({{ $pendingOrders->count() }})</span></a>
    </nav>

    @if (session('success'))
        <div class="notice mt-6 flex items-start gap-3" role="status"><x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0" /><p>{{ session('success') }}</p></div>
    @endif
    @if (session('error'))
        <div class="notice mt-6 border-[#ac6958] text-[#8a3f30]" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->has('payment_proof'))
        <div class="notice mt-6 border-[#ac6958] text-[#8a3f30]" role="alert">{{ $errors->first('payment_proof') }}</div>
    @endif

    @if ($pendingOrders->isEmpty())
        <div class="empty-state py-20 text-center md:py-28">
            <x-icon name="check" class="mx-auto mb-6 h-10 w-10 text-[#85543b]" />
            <h2 class="display mb-4 text-4xl md:text-5xl">Semua sudah tercatat.</h2>
            <p class="mx-auto mb-8 max-w-md text-[#75675c]">Tidak ada pembayaran tertunda. Lihat riwayat untuk memeriksa seluruh pesanan Anda.</p>
            <a href="{{ route('orders.history') }}" class="button-primary inline-flex items-center gap-3">Lihat riwayat pesanan <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
    @else
        @php
            $paymentMethods = ['bank_transfer' => 'Transfer bank', 'credit_card' => 'Kartu kredit', 'e_wallet' => 'E-wallet', 'cod' => 'Bayar di tempat', 'cash' => 'Tunai'];
        @endphp
        <p class="mt-7 max-w-2xl text-sm leading-relaxed text-[#75675c]">Pesanan dengan pembayaran tertunda tampil di sini. Jika sudah membayar, unggah bukti agar tim kami dapat memeriksanya.</p>
        <div class="mt-8 space-y-8">
            @foreach ($pendingOrders as $order)
                <article class="panel border border-[#ddd5c8]" aria-labelledby="pending-order-{{ $order->id_order }}">
                    <header class="flex flex-wrap items-center justify-between gap-5 border-b border-[#ddd5c8] px-6 py-6 md:px-8">
                        <div>
                            <p class="eyebrow mb-2">{{ $order->created_at->tz('Asia/Jakarta')->format('d M Y · H:i') }} WIB</p>
                            <h2 id="pending-order-{{ $order->id_order }}" class="display text-3xl">Pesanan #{{ $order->id_order }}</h2>
                        </div>
                        <span class="inline-flex items-center gap-2 border border-[#d6c3a0] bg-[#eee4ce] px-3 py-1.5 text-xs text-[#795d2d]"><x-icon name="clock" class="h-3.5 w-3.5" /> {{ $order->payment_proof ? 'Menunggu pemeriksaan' : 'Menunggu pembayaran' }}</span>
                    </header>
                    <div class="grid lg:grid-cols-[minmax(0,1fr)_350px]">
                        <div class="p-6 md:p-8">
                            <p class="eyebrow mb-5">Rincian pesanan</p>
                            <ul role="list" class="divide-y divide-[#ddd5c8]">
                                @foreach ($order->products as $product)
                                    <li class="flex items-start justify-between gap-5 py-4 first:pt-0">
                                        <div><h3 class="text-sm font-medium">{{ $product->name }}</h3><p class="mt-1 text-xs text-[#75675c]">{{ $product->pivot->quantity }} × Rp {{ number_format($product->pivot->price_at_order, 0, ',', '.') }}</p></div>
                                        <p class="shrink-0 text-sm tabular-nums">Rp {{ number_format($product->pivot->price_at_order * $product->pivot->quantity, 0, ',', '.') }}</p>
                                    </li>
                                @endforeach
                            </ul>
                            <dl class="mt-3 space-y-4 border-t border-[#ddd5c8] pt-5 text-sm">
                                <div class="flex justify-between gap-4"><dt class="text-[#75675c]">Metode pembayaran</dt><dd class="text-right">{{ $paymentMethods[$order->payment_method] ?? ucfirst(str_replace('_', ' ', $order->payment_method)) }}</dd></div>
                                <div class="flex items-end justify-between gap-4"><dt class="font-medium">Total pesanan</dt><dd class="text-2xl font-medium tabular-nums">Rp {{ number_format($order->total_price, 0, ',', '.') }}</dd></div>
                            </dl>
                            @if ($order->address)
                                <div class="mt-6 border-t border-[#ddd5c8] pt-5"><p class="eyebrow mb-2">Dikirim ke</p><p class="text-sm leading-relaxed text-[#75675c]">{{ $order->address }}</p></div>
                            @endif
                        </div>
                        <div class="border-t border-[#ddd5c8] bg-[#efe9de] p-6 md:p-8 lg:border-t-0 lg:border-l">
                            <x-icon name="upload" class="mb-5 h-6 w-6 text-[#85543b]" />
                            <h3 class="display mb-3 text-3xl">{{ $order->payment_proof ? 'Bukti sudah dikirim.' : 'Sudah membayar?' }}</h3>
                            <p class="mb-6 text-sm leading-relaxed text-[#75675c]">{{ $order->payment_proof ? 'Tim kami akan memeriksa pembayaran Anda. Gunakan formulir ini jika perlu mengganti bukti.' : 'Unggah bukti pembayaran untuk diperiksa oleh tim kami.' }}</p>
                            @if ($order->payment_proof)
                                <a href="{{ asset('storage/proofs/' . $order->payment_proof) }}" target="_blank" rel="noopener noreferrer" class="text-link mb-6 inline-flex items-center gap-2 text-sm">Lihat bukti yang dikirim <x-icon name="arrow-right" class="h-4 w-4" /></a>
                            @endif
                            <form action="{{ route('orders.uploadProof', ['orderId' => $order->id_order]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <label for="payment-proof-{{ $order->id_order }}" class="field-label">{{ $order->payment_proof ? 'Ganti bukti pembayaran' : 'Bukti pembayaran' }}</label>
                                <input type="file" id="payment-proof-{{ $order->id_order }}" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required aria-describedby="proof-help-{{ $order->id_order }}" class="field-input w-full min-w-0 text-xs file:mr-3 file:border-0 file:bg-[#e4d9c9] file:px-3 file:py-2 file:text-xs file:font-medium file:text-[#30251f]">
                                <p id="proof-help-{{ $order->id_order }}" class="mt-3 text-xs leading-relaxed text-[#75675c]">Format JPG, PNG, atau PDF. Maksimal 2 MB.</p>
                                <button type="submit" class="button-primary mt-6 flex w-full items-center justify-between gap-3">{{ $order->payment_proof ? 'Kirim bukti pengganti' : 'Kirim bukti pembayaran' }} <x-icon name="arrow-right" class="h-4 w-4 shrink-0" /></button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
@endsection
