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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->after('total_price');
            $table->string('phone', 20)->after('customer_name');
            $table->text('address')->after('phone');
            $table->string('payment_method')->after('address');
            $table->string('payment_status')->default('pending')->after('payment_method');
            $table->string('payment_proof')->nullable()->after('payment_status');
            
            $table->date('order_date')->nullable()->after('payment_proof');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'phone',
                'address',
                'payment_method',
                'payment_status',
                'payment_proof',
                'order_date'
            ]);
        });
    }
};