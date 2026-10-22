@extends('layouts.admin')
@section('page-title', 'Buat pesanan')
@section('content')
    <div class="admin-page-heading"><div><p class="eyebrow mb-3 text-rust">PESANAN / CATATAN BARU</p><h1 class="display">Catat pesanan baru.</h1><p class="mt-3 text-sm text-muted">Pilih pelanggan, isi pengiriman, lalu tambahkan produk.</p></div><a href="{{ route('admin.orders.index') }}" class="text-link"><x-icon name="arrow-left" class="size-4" /> Daftar pesanan</a></div>
    @include('admin.partials.feedback')
    <form method="POST" action="{{ route('admin.orders.store') }}" enctype="multipart/form-data" data-order-editor="create">
        @csrf
        @include('admin.orders.form', ['order' => null])
    </form>
@endsection
