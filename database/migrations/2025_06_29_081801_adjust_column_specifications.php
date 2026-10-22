<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Penyesuaian pada tabel 'orders'
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 20)->change();
            $table->string('payment_status', 20)->change();
            $table->string('payment_proof', 40)->nullable()->change();
            $table->string('customer_name', 30)->change();
        });

        // 2. Penyesuaian pada tabel 'users'
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->change();
            $table->string('remember_token', 50)->nullable()->change();
        });

        // 3. Penyesuaian pada tabel 'products'
        Schema::table('products', function (Blueprint $table) {
            $table->string('image', 60)->change();
        });

        // 4. Penyesuaian pada tabel 'order_product'
        Schema::table('order_product', function (Blueprint $table) {
            $table->renameColumn('oid_order_product', 'id_order_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kode untuk mengembalikan perubahan jika migrasi di-rollback
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->change(); // Kembali ke panjang default
            $table->string('payment_status')->change();
            $table->string('payment_proof')->nullable()->change();
            $table->string('customer_name')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->change();
            $table->string('remember_token', 100)->nullable()->change(); // Kembali ke default Laravel
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('image')->change();
        });

        Schema::table('order_product', function (Blueprint $table) {
            $table->renameColumn('id_order_product', 'oid_order_product');
        });
    }
};