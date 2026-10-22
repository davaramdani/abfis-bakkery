@extends('layouts.admin')
@section('page-title', 'Pesanan')
@section('content')
    <div class="admin-page-heading"><div><p class="eyebrow mb-3 text-rust">CATATAN PENJUALAN</p><h1 class="display">Setiap pesanan berarti.</h1><p class="mt-3 text-sm text-muted">Periksa detail, konfirmasi pembayaran, dan kelola pesanan pelanggan.</p></div><a href="{{ route('admin.orders.create') }}" class="button-primary"><x-icon name="plus" class="size-4" /> Buat pesanan</a></div>
    @include('admin.partials.feedback')
    <section class="admin-panel" aria-labelledby="orders-title">
        <div class="admin-panel-heading"><h2 id="orders-title" class="display">Daftar pesanan <span class="admin-count">{{ $allOrdersCount }}</span></h2><span class="hidden text-xs text-muted sm:block">Terbaru ditampilkan lebih dulu</span></div>
        <nav class="admin-status-tabs" aria-label="Filter status pesanan">
            @foreach(['' => 'Semua pesanan', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'completed' => 'Selesai'] as $status => $label)
                <a href="{{ route('admin.orders.index', array_filter(['search' => request('search'), 'status' => $status])) }}" @class(['is-active' => request('status', '') === $status]) @if(request('status', '') === $status) aria-current="page" @endif>{{ $label }}<span>{{ $status === '' ? $allOrdersCount : ($statusCounts[$status] ?? 0) }}</span></a>
            @endforeach
        </nav>
        <form method="GET" action="{{ route('admin.orders.index') }}" class="admin-filter-bar" role="search">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <div class="admin-search"><x-icon name="search" class="size-4" /><label class="sr-only" for="order-search">Cari pesanan</label><input type="search" id="order-search" name="search" value="{{ request('search') }}" placeholder="Cari nomor pesanan, nama, atau telepon..." maxlength="100"></div>
            <div class="flex items-center gap-3"><button type="submit" class="admin-small-button">Cari pesanan</button>@if(request()->filled('search'))<a href="{{ route('admin.orders.index', ['status' => request('status')]) }}" class="p-2 text-xs text-muted underline underline-offset-4">Reset</a>@endif</div>
        </form>
        <div class="admin-table-scroll">
            <table class="admin-table admin-orders-table">
                <thead><tr><th scope="col">Pesanan</th><th scope="col">Pelanggan</th><th scope="col">Tanggal</th><th scope="col">Produk</th><th scope="col">Total</th><th scope="col">Pembayaran</th><th scope="col">Bukti</th><th scope="col" class="text-right">Tindakan</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="font-medium hover:text-rust">#{{ str_pad($order->id_order, 4, '0', STR_PAD_LEFT) }}</a><p class="mt-1 text-[10px] capitalize text-muted">{{ str_replace('_', ' ', $order->payment_method) }}</p></td>
                            <td><p class="max-w-[180px] truncate font-medium">{{ $order->customer_name ?: $order->user?->name ?: 'Pelanggan' }}</p><p class="mt-1 text-xs text-muted">{{ $order->phone ?: '—' }}</p></td>
                            <td class="whitespace-nowrap text-xs text-muted">{{ ($order->order_date ?? $order->created_at)->format('d M Y') }}</td>
                            <td><span class="font-medium">{{ $order->products->sum('pivot.quantity') }}</span><span class="ml-1 text-xs text-muted">buah</span></td>
                            <td class="whitespace-nowrap tabular-nums">Rp {{ number_format($order->products->sum(fn($p) => $p->pivot->quantity * $p->pivot->price_at_order), 0, ',', '.') }}</td>
                            <td><x-payment-status :status="$order->payment_status" /></td>
                            <td>@if($order->payment_proof)<a href="{{ asset('storage/proofs/' . $order->payment_proof) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-xs text-rust underline underline-offset-4">Lihat <x-icon name="arrow-up-right" class="size-3" /></a>@else<span class="text-xs text-muted">Belum ada</span>@endif</td>
                            <td><div class="flex justify-end gap-2"><a href="{{ route('admin.orders.show', $order) }}" class="admin-icon-button" aria-label="Detail pesanan {{ $order->id_order }}"><x-icon name="arrow-up-right" class="size-4" /></a><a href="{{ route('admin.orders.edit', $order) }}" class="admin-icon-button" aria-label="Edit pesanan {{ $order->id_order }}"><x-icon name="edit" class="size-4" /></a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="admin-table-empty"><x-icon name="bag" class="mb-3 size-8" /><p>{{ request()->filled('search') || request()->filled('status') ? 'Tidak ada pesanan yang sesuai filter.' : 'Belum ada pesanan yang masuk.' }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-table-footer"><span>{{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }} pesanan</span>@if($orders->hasPages()){{ $orders->onEachSide(1)->links('admin.partials.pagination') }}@endif</div>
    </section>
@endsection
