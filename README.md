# Abfis Bakkery

Aplikasi toko Abfis Bakery dengan halaman pelanggan dan panel admin. Dibangun dengan Laravel 12, Blade, Tailwind CSS 4, dan Vite, dengan tampilan Warm Editorial.

## Fitur

- Katalog dengan pencarian, pengurutan, stok, dan detail produk.
- Registrasi, login, keranjang, checkout, riwayat pesanan, serta unggah bukti pembayaran.
- Panel admin untuk ringkasan toko, produk, pesanan, konfirmasi pembayaran, dan aktivitas.
- Formulir responsif, pratinjau foto, perhitungan total, dan penyesuaian stok.

## Menjalankan secara lokal

Siapkan PHP 8.2 atau lebih baru, Composer, MySQL, serta Node.js dan npm.

```sh
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Atur `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env` sesuai database lokal. Kemudian jalankan:

```sh
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

- Toko: `http://127.0.0.1:8000`
- Login admin: `http://127.0.0.1:8000/admin`
- Pengembangan aset: `npm run dev`

Database, akun lokal, unggahan pelanggan, dan bukti pembayaran tidak disertakan dalam repository. Gunakan konfigurasi serta data milik instalasi Anda sendiri.

Seeder pengguna lama memerlukan `SEED_ADMIN_PASSWORD` dan `SEED_USER_PASSWORD`, masing-masing minimal 8 karakter, dari lingkungan lokal. Tidak ada password bawaan dalam source code. Seeder contoh mengganti data, dan tidak dijalankan dalam langkah instalasi di atas.

## Pengujian

```sh
php artisan test
```

Pengujian fitur menggunakan SQLite di memori, terpisah dari database toko. Cakupannya meliputi katalog, akun, transaksi, panel admin, stok, validasi, dan perhitungan total.

## Struktur utama

- `app/Http/Controllers/`: logika halaman dan transaksi.
- `resources/views/user/`: halaman pelanggan.
- `resources/views/admin/`: panel admin.
- `resources/css/` dan `resources/js/`: gaya dan interaksi antarmuka.
- `database/migrations/`: skema database.
- `tests/Feature/`: pengujian fitur.

File `.env`, dependensi, hasil build, database lokal, cache, dan unggahan tidak masuk Git. Dependensi dapat dipasang kembali melalui `composer.lock` dan `package-lock.json`.
