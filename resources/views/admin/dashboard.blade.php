@extends('layouts.admin')
@section('page-title', 'Ringkasan')
@section('content')
    <div class="admin-page-heading">
        <div><p class="eyebrow mb-3 text-rust">ABFIS BAKERY / RINGKASAN TOKO</p><h1 class="display">Kabar dari dapur.</h1><p class="mt-3 text-sm text-muted">Selamat datang, {{ Auth::guard('admin')->user()?->name }}. Ini yang terjadi di toko Anda.</p></div>
        <a href="{{ route('admin.orders.create') }}" class="button-primary"><x-icon name="plus" class="size-4" /> Buat pesanan</a>
    </div>
    @include('admin.partials.feedback')
    <dl class="admin-metrics">
        <div><dt>Total pesanan <x-icon name="bag" class="size-4" /></dt><dd>{{ number_format($totalOrders, 0, ',', '.') }}</dd><p>{{ $orderTrend >= 0 ? '+' : '' }}{{ $orderTrend }}% <span>30 hari vs periode sebelumnya</span></p></div>
        <div><dt>Produk di katalog <x-icon name="package" class="size-4" /></dt><dd>{{ number_format($totalProducts, 0, ',', '.') }}</dd><p>{{ $newProductsThisMonth }} baru <span>bulan ini</span></p></div>
        <div><dt>Pelanggan baru <x-icon name="user" class="size-4" /></dt><dd>{{ number_format($activeUsers, 0, ',', '.') }}</dd><p>30 hari terakhir <span>berdasarkan pendaftaran</span></p></div>
        <div><dt>Pendapatan tercatat <x-icon name="wallet" class="size-4" /></dt><dd class="admin-revenue"><span>Rp</span> {{ number_format($revenue, 0, ',', '.') }}</dd><p>{{ $revenueTrend >= 0 ? '+' : '' }}{{ $revenueTrend }}% <span>Disetujui & selesai · tren 30 hari</span></p></div>
    </dl>
    <div class="admin-dashboard-grid">
        <section class="admin-panel" aria-labelledby="recent-orders-title">
            <div class="admin-panel-heading"><div><p class="eyebrow mb-1 text-muted">DARI PELANGGAN</p><h2 id="recent-orders-title" class="display">Pesanan terbaru</h2></div><a href="{{ route('admin.orders.index') }}" class="text-link">Lihat semua <x-icon name="arrow-right" class="size-3.5" /></a></div>
            <div class="admin-table-scroll">
                <table class="admin-table"><thead><tr><th scope="col">Pesanan / Pelanggan</th><th scope="col">Tanggal</th><th scope="col">Total</th><th scope="col">Pembayaran</th><th scope="col"><span class="sr-only">Detail</span></th></tr></thead>
                    <tbody>@forelse($recentOrders as $order)<tr><td><a href="{{ route('admin.orders.show', $order) }}" class="font-medium hover:text-rust">#{{ str_pad($order->id_order, 4, '0', STR_PAD_LEFT) }}</a><p class="mt-1 text-xs text-muted">{{ $order->customer_name ?: $order->user?->name ?: 'Pelanggan' }}</p></td><td class="text-muted">{{ $order->created_at->format('d M Y') }}</td><td class="whitespace-nowrap tabular-nums">Rp {{ number_format($order->products->sum(fn($p) => $p->pivot->quantity * $p->pivot->price_at_order), 0, ',', '.') }}</td><td><x-payment-status :status="$order->payment_status" /></td><td><a href="{{ route('admin.orders.show', $order) }}" class="admin-icon-button" aria-label="Lihat pesanan {{ $order->id_order }}"><x-icon name="arrow-up-right" class="size-4" /></a></td></tr>@empty<tr><td colspan="5" class="admin-table-empty"><x-icon name="bag" class="mb-3 size-7" /><p>Pesanan pertama akan muncul di sini.</p></td></tr>@endforelse</tbody>
                </table>
            </div>
        </section>
        <aside class="admin-attention" aria-labelledby="attention-title">
            <p class="eyebrow mb-3">DAFTAR PERIKSA</p><h2 id="attention-title" class="display text-3xl">Perlu dilihat.</h2>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="admin-review-link"><span><strong>{{ $pendingOrders }}</strong><span>Pembayaran menunggu</span></span><x-icon name="arrow-up-right" class="size-5" /></a>
            <p class="mt-3 text-xs leading-6 text-muted">{{ $ordersAwaitingReview }} pesanan sudah mengirim bukti pembayaran untuk diperiksa.</p>
            <div class="mt-7 border-t border-[#d6ccba] pt-6"><div class="mb-4 flex items-center justify-between"><h3 class="text-xs font-semibold">Stok perlu perhatian</h3><a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="text-xs text-rust underline underline-offset-4">Lihat</a></div>
                <ul class="divide-y divide-[#d6ccba]">@forelse($lowStockProducts as $product)<li class="flex justify-between gap-3 py-3 text-xs"><a href="{{ route('admin.products.edit', $product) }}" class="min-w-0 truncate hover:text-rust">{{ $product->name }}</a><span @class(['shrink-0', 'text-[#984d36]' => $product->stock === 0])>{{ $product->stock }} tersisa</span></li>@empty<li class="py-3 text-xs text-muted">Semua stok di atas 5 buah.</li>@endforelse</ul>
            </div>
        </aside>
    </div>
    <section class="admin-panel mt-7" aria-labelledby="activity-title">
        <div class="admin-panel-heading"><div><p class="eyebrow mb-1 text-muted">JEJAK TOKO</p><h2 id="activity-title" class="display">Aktivitas terbaru</h2></div><a href="{{ route('admin.activities') }}" class="text-link">Semua aktivitas <x-icon name="arrow-right" class="size-3.5" /></a></div>
        @include('admin.partials.activities', ['activities' => $recentActivities])
    </section>
@endsection
