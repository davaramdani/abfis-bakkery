@extends('layouts.app')

@section('title', 'Roti untuk hari yang baik')

@section('content')
<section class="hero" aria-labelledby="hero-title">
    <div class="hero-copy">
        <p class="eyebrow hero-eyebrow">
            <span class="tiny-line"></span> DARI DAPUR KAMI DI BOGOR
        </p>
        <h1 id="hero-title" class="display hero-title">
            Hal-hal baik<br>dimulai dari<br><em>roti hangat.</em>
        </h1>
        <p class="hero-description">
            Untuk sarapan yang pelan, obrolan yang panjang,<br class="hidden xl:block">
            dan hal-hal kecil yang layak dirayakan.
        </p>
        <a href="#products" class="button-primary hero-cta">
            Temukan favorit Anda <x-icon name="arrow-right" class="size-4" />
        </a>
        <div class="hero-footnote">
            <span class="hero-footnote-line"></span>
            <span>Dibuat dengan hati. Dinikmati setiap hari.</span>
        </div>
    </div>

    <figure class="hero-visual">
        <img
            src="{{ asset('images/bakery-hero.jpg') }}"
            alt="Roti berkulit keemasan, ditata di atas kain linen di dapur bakery"
            fetchpriority="high"
            width="1280"
            height="853"
        >
        <div class="bakery-seal" aria-label="Abfis Bakery, dibuat dengan hati">
            <svg viewBox="0 0 120 120" aria-hidden="true">
                <defs>
                    <path id="seal-circle" d="M60,60m-44,0a44,44 0 1,1 88,0a44,44 0 1,1 -88,0"/>
                </defs>
                <text>
                    <textPath href="#seal-circle" textLength="273">DIBUAT DENGAN HATI · ABFIS BAKERY · </textPath>
                </text>
            </svg>
            <x-icon name="wheat" class="seal-wheat" />
        </div>
        <figcaption class="hero-photo-caption">
            <span class="eyebrow">THE SIMPLE PLEASURES</span>
            <span class="display">Dari oven,<br>ke meja Anda.</span>
            <x-icon name="arrow-up-right" class="size-5" />
        </figcaption>
        <span class="photo-index">01 / DAPUR ABFIS</span>
    </figure>
</section>

<div class="values-strip" aria-label="Tentang pilihan kami">
    <div>
        <x-icon name="wheat" />
        <span>Bahan pilihan, rasa yang jujur.</span>
    </div>
    <span class="strip-star" aria-hidden="true">✳</span>
    <div>
        <x-icon name="heart" />
        <span>Dibuat untuk momen sederhana.</span>
    </div>
    <span class="strip-star" aria-hidden="true">✳</span>
    <div>
        <x-icon name="coffee" />
        <span>Selalu cocok dengan secangkir kopi.</span>
    </div>
</div>

<section id="products" class="catalog-section" aria-labelledby="catalog-title">
    <div class="section-heading">
        <div>
            <p class="eyebrow mb-3">PILIHAN DARI DAPUR KAMI</p>
            <h2 id="catalog-title" class="display section-title">Cari teman <em>ngopi Anda.</em></h2>
        </div>
        <p class="section-aside">Manis, gurih, atau keduanya.<br>Ada favorit untuk setiap selera.</p>
    </div>

    @if(session('success'))
        <div role="status" class="notice mb-6">
            <x-icon name="check" class="size-4" />
            {{ session('success') }}
            <button type="button" data-dismiss-notice aria-label="Tutup notifikasi" class="ml-auto">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    @endif
    @if(session('error'))
        <div role="alert" class="notice mb-6">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('user.dashboard') }}#products" class="catalog-toolbar" role="search">
        <div class="catalog-search">
            <x-icon name="search" class="size-4" />
            <label class="sr-only" for="product-search">Cari roti atau kue</label>
            <input
                type="search"
                id="product-search"
                name="q"
                placeholder="Cari roti atau kue..."
                value="{{ $search }}"
                maxlength="100"
            >
            <button type="submit" aria-label="Cari produk">
                <x-icon name="arrow-right" class="size-4" />
            </button>
        </div>
        <div class="catalog-meta">
            <span>{{ $products->total() }} pilihan tersedia</span>
            <div class="sort-field">
                <label for="product-sort">Urutkan:</label>
                <select id="product-sort" name="sort" data-auto-submit>
                    <option value="latest" @selected($sort === 'latest')>Terbaru</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>Harga terendah</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>Harga tertinggi</option>
                    <option value="name" @selected($sort === 'name')>Nama A–Z</option>
                </select>
                <x-icon name="chevron-down" class="size-3" />
            </div>
            <button type="submit" class="filter-submit">Terapkan</button>
        </div>
    </form>

    @if($search)
        <div class="flex items-center justify-between gap-4 border-b border-line py-4 text-sm">
            <p>Hasil pencarian untuk “{{ $search }}”</p>
            <a href="{{ route('user.dashboard') }}#products" class="text-link">
                Hapus pencarian <x-icon name="x" class="size-3" />
            </a>
        </div>
    @endif

    @if($products->isEmpty())
        <div class="empty-state">
            <x-icon name="search" class="size-9" />
            <h3 class="display text-3xl">{{ $search ? 'Belum ketemu favoritnya?' : 'Dapur sedang menyiapkan pilihan.' }}</h3>
            <p>{{ $search ? 'Coba nama yang lain, atau lihat semua pilihan kami.' : 'Kunjungi kembali untuk melihat roti dan kue yang tersedia.' }}</p>
            @if($search)
                <a href="{{ route('user.dashboard') }}#products" class="button-outline">
                    Lihat semua pilihan <x-icon name="arrow-right" class="size-4" />
                </a>
            @endif
        </div>
    @else
        <div class="product-grid">
            @foreach($products as $product)
                <article class="product-item">
                    <a
                        href="{{ route('products.show', $product->id_product) }}"
                        class="product-image-link"
                        aria-label="Lihat {{ $product->name }}"
                    >
                        @if($product->image)
                            <img
                                src="{{ asset('storage/products/' . $product->image) }}"
                                alt="{{ $product->name }}"
                                loading="lazy"
                                width="480"
                                height="400"
                                data-product-image
                            >
                        @else
                            <div class="image-placeholder">
                                <x-icon name="wheat" class="size-14" />
                            </div>
                        @endif
                        <span class="product-availability"><span class="status-dot"></span> Tersedia</span>
                        <span class="product-view">
                            <x-icon name="arrow-up-right" class="size-5" />
                        </span>
                    </a>
                    <div class="product-info">
                        <p class="eyebrow product-kicker">DARI DAPUR ABFIS</p>
                        <h3>
                            <a href="{{ route('products.show', $product->id_product) }}">{{ $product->name }}</a>
                        </h3>
                        <p class="product-description">{{ Str::limit($product->description ?: 'Teman untuk momen kecil Anda.', 70) }}</p>
                        <div class="product-bottom">
                            <p class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}<span>/ pcs</span></p>
                            <form action="{{ route('cart.add', $product->id_product) }}" method="POST">
                                @csrf
                                <button class="product-add" type="submit" aria-label="Tambah {{ $product->name }} ke keranjang">
                                    <x-icon name="plus" class="size-4" />
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        @if($products->hasPages())
            <div class="catalog-pagination">{{ $products->onEachSide(1)->fragment('products')->links() }}</div>
        @endif
    @endif

    <p class="catalog-note">
        <span class="status-dot"></span>
        Pilih yang Anda suka, masukkan ke keranjang, lalu kami siapkan pesanannya.
    </p>
</section>

<section id="cerita" class="story-section" aria-labelledby="story-title">
    <div class="story-heading">
        <p class="eyebrow mb-6">CERITA ABFIS / 01</p>
        <h2 id="story-title" class="display">Tak harus mewah.<br>Yang penting,<br><em>berkesan.</em></h2>
        <x-icon name="wheat" class="story-wheat" />
    </div>
    <div class="story-copy">
        <p class="story-intro">Kami percaya, sepotong roti bisa menjadi alasan untuk duduk lebih lama.</p>
        <p>Di sela hari yang sibuk, selalu ada ruang untuk hal sederhana. Sarapan bersama keluarga. Camilan di meja kerja. Oleh-oleh saat bertamu.</p>
        <p>Itulah tempat yang ingin kami isi: menghadirkan roti dan kue untuk menemani keseharian Anda, satu gigitan pada satu waktu.</p>
        <a href="#kunjungi" class="text-link">
            Salam hangat dari Bogor <x-icon name="arrow-up-right" class="size-4" />
        </a>
        <div class="story-signature display">
            Dari kami, untuk Anda.
            <span class="eyebrow">— DAPUR ABFIS</span>
        </div>
    </div>
</section>
@endsection
