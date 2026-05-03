<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengguna;
use App\Enums\RolePengguna;
use Illuminate\Support\Facades\Hash;

class PenggunaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password123');

        // 1. Admin
        Pengguna::create([
            'nama_lengkap' => 'Administrator',
            'role' => RolePengguna::Admin,
            'email' => 'admin@lms.com',
            'username' => 'admin_lms',
            'nomor_induk' => null,
            'password' => $password,
        ]);

        // 2. Dosen
        Pengguna::create([
            'nama_lengkap' => 'Dr. Budi Santoso',
            'role' => RolePengguna::Dosen,
            'email' => 'budi@lms.com',
            'username' => null,
            'nomor_induk' => '1234567890',
            'password' => $password,
        ]);

        // 3. Mahasiswa
        Pengguna::create([
            'nama_lengkap' => 'Andi Wijaya',
            'role' => RolePengguna::Mahasiswa,
            'email' => 'andi@lms.com',
            'username' => null,
            'nomor_induk' => '2024001',
            'password' => $password,
        ]);
    }
}
