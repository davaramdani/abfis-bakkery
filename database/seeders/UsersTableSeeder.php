<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $adminPassword = env('SEED_ADMIN_PASSWORD');
        $userPassword = env('SEED_USER_PASSWORD');
        if (!is_string($adminPassword) || strlen($adminPassword) < 8 || !is_string($userPassword) || strlen($userPassword) < 8) {
            throw new \RuntimeException('Set SEED_ADMIN_PASSWORD and SEED_USER_PASSWORD locally, each with at least 8 characters, before seeding.');
        }

        // Buat pengguna admin
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make($adminPassword),
            'role' => 'admin',
        ]);

        // Buat pengguna user
        User::create([
            'name' => 'user',
            'email' => 'user@gmail.com',
            'password' => Hash::make($userPassword),
            'role' => 'user',
        ]);
    }
}
