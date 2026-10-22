<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; 

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('products')->truncate();

        Schema::enableForeignKeyConstraints();

        DB::table('products')->insert([
            [
                'name' => 'Product 1',
                'description' => 'Description for product 1',
                'price' => 10000,
                'stock' => 50,
                'image' => 'product1.jpg',
                'created_at' => '2025-06-16 01:21:01',
                'updated_at' => '2025-06-16 01:21:01',
            ],
            [
                'name' => 'Product 2',
                'description' => 'Description for product 2',
                'price' => 20000,
                'stock' => 100,
                'image' => 'product2.jpg',
                'created_at' => '2025-06-16 01:21:01',
                'updated_at' => '2025-06-16 01:21:01',
            ],
        ]);
    }
}