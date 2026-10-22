<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema; // Tambahkan ini

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminPassword = env('SEED_ADMIN_PASSWORD');
        $userPassword = env('SEED_USER_PASSWORD');
        if (!is_string($adminPassword) || strlen($adminPassword) < 8 || !is_string($userPassword) || strlen($userPassword) < 8) {
            throw new \RuntimeException('Set SEED_ADMIN_PASSWORD and SEED_USER_PASSWORD locally, each with at least 8 characters, before seeding.');
        }

        // PERBAIKAN: Nonaktifkan pengecekan foreign key
        Schema::disableForeignKeyConstraints();

        DB::table('users')->truncate();

        // PERBAIKAN: Aktifkan kembali pengecekan foreign key
        Schema::enableForeignKeyConstraints();

        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make($adminPassword),
                'is_admin' => 1,
                'created_at' => '2025-06-16 01:21:01',
                'updated_at' => '2025-06-16 01:21:01',
            ],
            [
                'name' => 'User',
                'email' => 'user@gmail.com',
                'password' => Hash::make($userPassword),
                'is_admin' => 0,
                'created_at' => '2025-06-16 01:21:01',
                'updated_at' => '2025-06-16 01:21:01',
            ],
        ]);
    }
}
