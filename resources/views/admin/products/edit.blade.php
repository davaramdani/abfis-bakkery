@extends('layouts.admin')
@section('page-title', 'Edit produk')
@section('content')
    <div class="admin-page-heading"><div><p class="eyebrow mb-3 text-rust">KATALOG / PRD-{{ str_pad($product->id_product, 3, '0', STR_PAD_LEFT) }}</p><h1 class="display">Rapikan pilihan.</h1><p class="mt-3 text-sm text-muted">Perbarui detail {{ $product->name }} di katalog Anda.</p></div><a href="{{ route('admin.products.index') }}" class="text-link"><x-icon name="arrow-left" class="size-4" /> Daftar produk</a></div>
    @include('admin.partials.feedback')
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.products.form')
    </form>
@endsection
