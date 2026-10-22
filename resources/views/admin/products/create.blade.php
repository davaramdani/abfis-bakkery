@extends('layouts.admin')
@section('page-title', 'Tambah produk')
@section('content')
    <div class="admin-page-heading"><div><p class="eyebrow mb-3 text-rust">KATALOG / PRODUK BARU</p><h1 class="display">Ada yang baru.</h1><p class="mt-3 text-sm text-muted">Lengkapi detail produk sebelum ditampilkan di toko.</p></div><a href="{{ route('admin.products.index') }}" class="text-link"><x-icon name="arrow-left" class="size-4" /> Daftar produk</a></div>
    @include('admin.partials.feedback')
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.products.form', ['product' => null])
    </form>
@endsection
