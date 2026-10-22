<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan pengecekan foreign key
        Schema::disableForeignKeyConstraints();

        DB::table('orders')->truncate();
        DB::table('order_product')->truncate();

        // Aktifkan kembali pengecekan foreign key
        Schema::enableForeignKeyConstraints();

        // Membuat satu order untuk user dengan id_user = 2
        DB::table('orders')->insert([
            'id_user' => 2,
            'total_price' => 50000,
            'status' => 'pending',

            // PENYESUAIAN: Tambahkan data untuk kolom-kolom baru
            'customer_name' => 'User Biasa',
            'phone' => '081234567890',
            'address' => 'Jl. Pahlawan No. 123, Kota Fiksi',
            'payment_method' => 'cod', // Atau 'bank_transfer', dll.
            'payment_status' => 'pending',
            'order_date' => '2025-06-16',
            
            'created_at' => '2025-06-16 01:21:01',
            'updated_at' => '2025-06-16 01:21:01',
        ]);

        // Mengisi tabel pivot order_product
        DB::table('order_product')->insert([
            [
                'id_order' => 1,
                'id_product' => 1,
                'quantity' => 1,
                'created_at' => '2025-06-16 01:21:01',
                'updated_at' => '2025-06-16 01:21:01',
            ],
            [
                'id_order' => 1,
                'id_product' => 2,
                'quantity' => 2,
                'created_at' => '2025-06-16 01:21:01',
                'updated_at' => '2025-06-16 01:21:01',
            ],
        ]);
    }
}