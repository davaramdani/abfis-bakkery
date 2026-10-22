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
        Schema::create('order_product', function (Blueprint $table) {
            // Sesuai permintaan Anda: Primary Key INT dengan nama oid_order_product
            $table->increments('oid_order_product');

            // Foreign key dengan tipe data INTEGER, disamakan dengan tabel induk
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id');

            $table->integer('quantity');
            $table->integer('price_at_order');
            $table->timestamps();

            // Mendefinisikan relasi ke kolom yang BENAR
            // 'order_id' -> terhubung ke 'id_order' di tabel 'orders'
            $table->foreign('order_id')->references('id_order')->on('orders')->onDelete('cascade');
            
            // 'product_id' -> terhubung ke 'id_product' di tabel 'products'
            $table->foreign('product_id')->references('id_product')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_product');
    }
};