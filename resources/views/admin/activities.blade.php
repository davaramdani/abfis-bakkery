@extends('layouts.admin')
@section('page-title', 'Aktivitas')
@section('content')
    <div class="admin-page-heading"><div><p class="eyebrow mb-3 text-rust">JEJAK TOKO</p><h1 class="display">Yang baru di Abfis.</h1><p class="mt-3 text-sm text-muted">Pendaftaran pelanggan, produk baru, dan pesanan terbaru dalam satu catatan.</p></div><a href="{{ route('admin.dashboard') }}" class="button-outline"><x-icon name="arrow-left" class="size-4" /> Ringkasan</a></div>
    <section class="admin-panel"><div class="admin-panel-heading"><h2 class="display">Aktivitas terakhir</h2><span class="text-xs text-muted">{{ $activities->count() }} catatan terbaru</span></div>@include('admin.partials.activities')</section>
@endsection
