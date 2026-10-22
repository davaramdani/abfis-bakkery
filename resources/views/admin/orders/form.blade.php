<div class="admin-order-form-grid">
    <div class="space-y-6">
        <section class="admin-panel admin-form-section" aria-labelledby="customer-heading">
            <div class="admin-form-section-heading"><span class="eyebrow text-rust">01</span><div><h2 id="customer-heading" class="display text-3xl">Pelanggan & pengiriman</h2><p class="mt-2 text-xs leading-6 text-muted">Pesanan dihubungkan ke akun pelanggan yang dipilih.</p></div></div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div><label class="field-label" for="id_user">Akun pelanggan <span class="text-rust">*</span></label><select id="id_user" name="id_user" class="field-input" required data-customer-select><option value="">Pilih akun pelanggan</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string)old('id_user', $order?->id_user) === (string)$user->id)>{{ $user->name }}</option>@endforeach</select></div>
                <div><label class="field-label" for="customer_name">Nama penerima <span class="text-rust">*</span></label><input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', $order?->customer_name) }}" maxlength="30" class="field-input" placeholder="Nama lengkap penerima" required></div>
                <div><label class="field-label" for="phone">Nomor telepon <span class="text-rust">*</span></label><input id="phone" name="phone" type="tel" value="{{ old('phone', $order?->phone) }}" maxlength="20" class="field-input" placeholder="0812 3456 7890" required></div>
                <div><label class="field-label" for="order_date">Tanggal pesanan <span class="text-rust">*</span></label><input id="order_date" name="order_date" type="date" value="{{ old('order_date', $order?->order_date?->format('Y-m-d') ?? now('Asia/Jakarta')->format('Y-m-d')) }}" class="field-input" required></div>
                <div class="sm:col-span-2"><label class="field-label" for="address">Alamat lengkap <span class="text-rust">*</span></label><textarea id="address" name="address" rows="3" maxlength="500" class="field-input resize-y" placeholder="Jalan, nomor rumah, kelurahan, kecamatan, dan kota" required>{{ old('address', $order?->address) }}</textarea></div>
            </div>
        </section>
        <section class="admin-panel admin-form-section" aria-labelledby="order-products-heading">
            <div class="admin-form-section-heading"><span class="eyebrow text-rust">02</span><div><h2 id="order-products-heading" class="display text-3xl">Isi pesanan</h2><p class="mt-2 text-xs leading-6 text-muted">{{ $order ? 'Isi jumlah 0 untuk menghapus produk saat disimpan.' : 'Harga dihitung dari katalog. Setidaknya satu produk harus dipilih.' }}</p></div></div>
            @if($order)
                <div class="space-y-4" data-order-rows>
                    @foreach($order->products as $item)
                        <div class="admin-order-existing-row" data-order-row data-fixed-price="{{ $item->pivot->price_at_order }}"><div><p class="text-sm font-medium">{{ $item->name }}</p><p class="mt-1 text-xs text-muted">Rp {{ number_format($item->pivot->price_at_order, 0, ',', '.') }} / buah</p></div><div><label for="qty-{{ $item->id_product }}" class="field-label text-[10px]">Jumlah</label><input type="number" id="qty-{{ $item->id_product }}" name="quantities[{{ $item->id_product }}]" min="0" max="{{ $item->stock + $item->pivot->quantity }}" step="1" value="{{ old('quantities.' . $item->id_product, $item->pivot->quantity) }}" class="field-input" data-row-quantity required></div><output class="text-right text-sm tabular-nums" data-row-subtotal>Rp {{ number_format($item->pivot->quantity * $item->pivot->price_at_order, 0, ',', '.') }}</output></div>
                    @endforeach
                    <div class="border-t border-line pt-5" data-order-row>
                        <p class="mb-4 text-xs font-medium">Tambahkan produk lain <span class="font-normal text-muted">(opsional)</span></p>
                        <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_90px]"><div><label for="new_product_id" class="field-label text-xs">Produk tambahan</label><select id="new_product_id" name="new_product_id" class="field-input" data-row-product><option value="">Pilih produk tambahan</option>@foreach($products as $product)@if(!$order->products->contains('id_product', $product->id))<option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}" @selected((string)old('new_product_id') === (string)$product->id) @disabled($product->stock <= 0)>{{ $product->name }} · Rp {{ number_format($product->price, 0, ',', '.') }} · {{ $product->stock }} tersedia</option>@endif @endforeach</select></div><div><label for="new_quantity" class="field-label text-xs">Jumlah</label><input type="number" id="new_quantity" name="new_quantity" value="{{ old('new_quantity', 1) }}" min="1" step="1" class="field-input" data-row-quantity></div></div>
                    </div>
                </div>
            @else
                <div class="space-y-4" data-order-rows>
                    @foreach(old('products', [['id' => '', 'quantity' => 1]]) as $index => $row)
                        @include('admin.orders.product-row', ['rowIndex' => $index, 'selectedProduct' => $row['id'] ?? '', 'selectedQuantity' => $row['quantity'] ?? 1])
                    @endforeach
                </div>
                <template data-order-row-template>@include('admin.orders.product-row', ['rowIndex' => '__INDEX__', 'selectedProduct' => '', 'selectedQuantity' => 1])</template>
                <button type="button" class="admin-small-button mt-5 inline-flex items-center gap-2" data-add-order-row><x-icon name="plus" class="size-3.5" /> Tambah produk</button>
            @endif
        </section>
    </div>
    <aside class="space-y-6">
        <section class="admin-panel admin-form-section" aria-labelledby="payment-heading">
            <div class="admin-form-section-heading"><span class="eyebrow text-rust">03</span><div><h2 id="payment-heading" class="display text-3xl">Pembayaran</h2></div></div>
            <div class="space-y-5">
                <div><label for="payment_method" class="field-label">Metode pembayaran <span class="text-rust">*</span></label><select id="payment_method" name="payment_method" class="field-input" required>@foreach(['bank_transfer' => 'Transfer bank', 'cash' => 'Tunai', 'credit_card' => 'Kartu kredit', 'e_wallet' => 'E-wallet', 'cod' => 'Bayar di tempat (COD)'] as $value => $label)<option value="{{ $value }}" @selected(old('payment_method', $order?->payment_method) === $value)>{{ $label }}</option>@endforeach</select></div>
                <div><label for="payment_status" class="field-label">Status pembayaran <span class="text-rust">*</span></label><select id="payment_status" name="payment_status" class="field-input" required>@foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'completed' => 'Selesai'] as $value => $label)<option value="{{ $value }}" @selected(old('payment_status', $order?->payment_status ?? 'pending') === $value)>{{ $label }}</option>@endforeach</select></div>
                @if($order?->payment_proof)<a href="{{ asset('storage/proofs/' . $order->payment_proof) }}" target="_blank" rel="noopener noreferrer" class="text-link">Lihat bukti saat ini <x-icon name="arrow-up-right" class="size-3.5" /></a>@endif
                <div><label for="payment_proof" class="field-label">{{ $order ? 'Ganti bukti pembayaran' : 'Bukti pembayaran' }} <span class="font-normal text-muted">(opsional)</span></label><input id="payment_proof" name="payment_proof" type="file" accept=".jpg,.jpeg,.png,.pdf" class="field-input admin-file-input"><p class="mt-2 text-xs leading-6 text-muted">JPG, PNG, atau PDF. Maksimal 2 MB.</p></div>
            </div>
        </section>
        <section class="admin-order-summary"><p class="eyebrow mb-5">RINGKASAN PESANAN</p><div class="flex items-center justify-between border-b border-[#d6ccba] pb-4 text-xs"><span class="text-muted">Jumlah produk</span><span data-order-count>—</span></div><p class="mt-5 text-xs text-muted">Total pesanan</p><output class="mt-2 block text-3xl font-medium tracking-tight tabular-nums" data-order-total>Rp {{ $order ? number_format($order->products->sum(fn($p) => $p->pivot->quantity * $p->pivot->price_at_order), 0, ',', '.') : '0' }}</output><p class="mt-4 text-xs leading-6 text-muted">Stok akan disesuaikan setelah perubahan disimpan.</p></section>
    </aside>
</div>
<div class="admin-form-actions"><p class="text-xs text-muted"><span class="text-rust">*</span> Wajib diisi</p><div class="flex gap-3"><a href="{{ route('admin.orders.index') }}" class="button-outline">Batal</a><button type="submit" name="action" value="add_product" class="button-primary">{{ $order ? 'Simpan perubahan' : 'Simpan pesanan' }}<x-icon name="check" class="size-4" /></button></div></div>
