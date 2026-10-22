@extends('layouts.admin')
@section('page-title', 'Edit pesanan')
@section('content')
    <div class="admin-page-heading"><div><p class="eyebrow mb-3 text-rust">PESANAN / #{{ str_pad($order->id_order, 4, '0', STR_PAD_LEFT) }}</p><h1 class="display">Perbarui pesanan.</h1><p class="mt-3 text-sm text-muted">Periksa data pelanggan, jumlah produk, dan pembayaran.</p></div><a href="{{ route('admin.orders.show', $order) }}" class="text-link"><x-icon name="arrow-left" class="size-4" /> Detail pesanan</a></div>
    @include('admin.partials.feedback')
    <form method="POST" action="{{ route('admin.orders.update', $order) }}" enctype="multipart/form-data" data-order-editor="edit">
        @csrf
        @method('PUT')
        @include('admin.orders.form')
    </form>
@endsection
