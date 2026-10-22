@extends('layouts.admin')
@section('page-title', 'Produk')
@section('content')
    <div class="admin-page-heading">
        <div><p class="eyebrow mb-3 text-rust">KATALOG ABFIS</p><h1 class="display">Pilihan dari dapur.</h1><p class="mt-3 text-sm text-muted">Atur produk, perbarui harga, dan jaga stok tetap tercatat.</p></div>
        <a href="{{ route('admin.products.create') }}" class="button-primary"><x-icon name="plus" class="size-4" /> Tambah produk</a>
    </div>
    @include('admin.partials.feedback')
    <section class="admin-panel" aria-labelledby="products-title">
        <div class="admin-panel-heading"><h2 id="products-title" class="display">Daftar produk <span class="admin-count">{{ $inventoryCounts['all'] }}</span></h2><p class="hidden text-xs text-muted sm:block">{{ $inventoryCounts['out'] }} stok habis · {{ $inventoryCounts['low'] }} stok menipis</p></div>
        <form method="GET" action="{{ route('admin.products.index') }}" class="admin-filter-bar" role="search">
            <div class="admin-search"><x-icon name="search" class="size-4" /><label for="product-search" class="sr-only">Cari produk</label><input id="product-search" type="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="Cari nama produk..."></div>
            <div class="flex flex-wrap items-center gap-3"><label for="stock-filter" class="sr-only">Filter stok</label><select id="stock-filter" name="stock" class="admin-filter-select"><option value="">Semua stok</option><option value="available" @selected(request('stock') === 'available')>Tersedia</option><option value="low" @selected(request('stock') === 'low')>Stok ≤ 5</option><option value="out" @selected(request('stock') === 'out')>Stok habis</option></select><button type="submit" class="admin-small-button">Terapkan</button>@if(request()->filled('search') || request()->filled('stock'))<a href="{{ route('admin.products.index') }}" class="p-2 text-xs text-muted underline underline-offset-4">Reset</a>@endif</div>
        </form>
        <div class="admin-table-scroll">
            <table class="admin-table admin-products-table">
                <thead><tr><th scope="col">Produk</th><th scope="col">Harga satuan</th><th scope="col">Stok</th><th scope="col">Ketersediaan</th><th scope="col">Ditambahkan</th><th scope="col" class="text-right">Tindakan</th></tr></thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td><div class="flex items-center gap-4"><a href="{{ route('admin.products.edit', $product) }}" class="admin-product-thumb" aria-label="Edit {{ $product->name }}">@if($product->image)<img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}" width="56" height="56" loading="lazy" data-product-image>@else<x-icon name="wheat" class="size-6 text-rust" />@endif</a><div><a href="{{ route('admin.products.edit', $product) }}" class="font-medium hover:text-rust">{{ $product->name }}</a><p class="mt-1 max-w-[230px] truncate text-xs text-muted">{{ $product->description ?: 'Belum ada deskripsi' }}</p><p class="mt-1 text-[10px] text-muted">PRD-{{ str_pad($product->id_product, 3, '0', STR_PAD_LEFT) }}</p></div></div></td>
                            <td class="whitespace-nowrap tabular-nums">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td><span class="font-medium tabular-nums">{{ $product->stock }}</span><span class="ml-1 text-xs text-muted">buah</span></td>
                            <td><span @class(['admin-status', 'status-rejected' => $product->stock === 0, 'status-pending' => $product->stock > 0 && $product->stock <= 5, 'status-approved' => $product->stock > 5])><span></span>{{ $product->stock === 0 ? 'Habis' : ($product->stock <= 5 ? 'Stok menipis' : 'Tersedia') }}</span></td>
                            <td class="whitespace-nowrap text-xs text-muted">{{ $product->created_at?->format('d M Y') ?? '—' }}</td>
                            <td><div class="flex items-center justify-end gap-2"><a href="{{ route('admin.products.edit', $product) }}" class="admin-icon-button" aria-label="Edit {{ $product->name }}"><x-icon name="edit" class="size-4" /></a><form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Hapus produk ini dari katalog? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button type="submit" class="admin-icon-button text-[#9b5945]" aria-label="Hapus {{ $product->name }}"><x-icon name="trash" class="size-4" /></button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-table-empty"><x-icon name="package" class="mb-3 size-8" /><p class="mb-3">{{ request()->filled('search') || request()->filled('stock') ? 'Tidak ada produk yang sesuai pencarian.' : 'Katalog masih kosong.' }}</p><a href="{{ route('admin.products.create') }}" class="text-link">Tambah produk pertama <x-icon name="plus" class="size-3" /></a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-table-footer"><span>{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk</span>@if($products->hasPages()){{ $products->onEachSide(1)->links('admin.partials.pagination') }}@endif</div>
    </section>
@endsection
