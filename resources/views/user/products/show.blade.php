@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="detail-page">
    <a href="{{ route('user.dashboard') }}#products" class="text-link mb-8 inline-flex">
        <x-icon name="arrow-left" class="size-4" /> Kembali ke pilihan kami
    </a>

    @if(session('success'))
        <div role="status" class="notice mb-6">
            <x-icon name="check" class="size-4" />
            {{ session('success') }}
            <a href="{{ route('cart.index') }}" class="text-link ml-auto">
                Lihat keranjang <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>
    @endif
    @if(session('error'))
        <div role="alert" class="notice mb-6">{{ session('error') }}</div>
    @endif

    <section class="grid gap-10 lg:grid-cols-2 lg:gap-20">
        <div class="detail-image">
            @if($product->image)
                <img
                    src="{{ asset('storage/products/' . $product->image) }}"
                    alt="{{ $product->name }}"
                    width="700"
                    height="700"
                    data-product-image
                >
            @else
                <div class="image-placeholder">
                    <x-icon name="wheat" class="size-24" />
                </div>
            @endif
        </div>
        <div class="flex flex-col justify-center">
            <p class="eyebrow mb-5">DARI DAPUR ABFIS</p>
            <h1 class="display detail-title">{{ $product->name }}</h1>
            <p class="mt-6 text-2xl">
                Rp {{ number_format($product->price, 0, ',', '.') }}<span class="ml-2 text-sm text-muted">/ pcs</span>
            </p>
            <p class="mt-6 leading-8 text-muted">{{ $product->description ?: 'Pilihan dari dapur kami, untuk menemani momen sederhana Anda.' }}</p>
            <div class="my-8 flex items-center gap-2 border-y border-line py-5 text-sm">
                <span class="status-dot"></span>
                {{ $product->stock > 0 ? $product->stock . ' tersedia untuk dipesan' : 'Saat ini stok habis' }}
            </div>

            @if($product->stock > 0)
                <form action="{{ route('cart.add', $product->id_product) }}" method="POST">
                    @csrf
                    <button type="submit" class="button-primary w-full justify-between">
                        Tambah ke keranjang <x-icon name="bag" class="size-5" />
                    </button>
                </form>
            @else
                <button type="button" class="button-primary w-full justify-center" disabled>Stok habis</button>
            @endif

            <p class="mt-5 text-xs leading-6 text-muted">
                @guest
                    Masuk ke akun Anda untuk menyimpan pilihan di keranjang.
                @else
                    Lanjutkan ke keranjang untuk mengatur jumlah dan menyelesaikan pesanan.
                @endguest
            </p>
            <a href="{{ route('user.dashboard') }}#products" class="text-link mt-8 self-start">
                Lihat pilihan lainnya <x-icon name="arrow-right" class="size-4" />
            </a>
        </div>
    </section>
</div>
@endsection
