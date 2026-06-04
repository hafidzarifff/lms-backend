<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalPerkuliahan;
use App\Models\MasterMataKuliah;
use App\Models\MasterKelas;
use App\Models\Pengguna;
use Illuminate\Support\Str;
use App\Enums\RolePengguna;

class JadwalPerkuliahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan ada data Master Mata Kuliah
        $mataKuliah = MasterMataKuliah::first();
        if (!$mataKuliah) {
            $mataKuliah = MasterMataKuliah::create([
                'kode_mk' => 'MK-101',
                'nama_mk' => 'Dasar Pemrograman',
                'sks' => 3,
                'deskripsi' => 'Belajar pemrograman dasar',
                'semester' => 1,
                'fakultas' => 'Teknik',
                'prodi' => 'Informatika'
            ]);
        }

        // 2. Pastikan ada data Master Kelas
        $kelas = MasterKelas::first();
        if (!$kelas) {
            $kelas = MasterKelas::create([
                'nama_kelas' => 'TI-1A',
                'kode_kelas' => 'TI1A',
                'tahun_angkatan' => '2024',
                'fakultas' => 'Teknik',
                'prodi' => 'Informatika'
            ]);
        }

        // 3. Pastikan ada data Dosen
        $dosen = Pengguna::where('role', RolePengguna::Dosen)->first();
        if (!$dosen) {
            $dosen = Pengguna::create([
                'nama_lengkap' => 'Budi Dosen',
                'role' => RolePengguna::Dosen,
                'email' => 'budi.dosen@kampus.ac.id',
                'password' => bcrypt('password'),
                'status_aktif' => true,
                'status_persetujuan' => 'Disetujui',
                'nomor_induk' => 'DSN10101'
            ]);
        }

        // 4. Generate Token Enrollment yang unik
        do {
            $token = Str::upper(Str::random(6));
        } while (JadwalPerkuliahan::where('token_enrollment', $token)->exists());

        // 5. Insert Jadwal Perkuliahan
        JadwalPerkuliahan::create([
            'id_mk' => $mataKuliah->id_mk,
            'id_kelas' => $kelas->id_kelas,
            'id_dosen' => $dosen->id_user,
            'sks' => $mataKuliah->sks,
            'fakultas' => $kelas->fakultas ?? 'Teknik',
            'prodi' => $kelas->prodi ?? 'Informatika',
            'tahun' => '2024/2025',
            'semester' => 1,
            'hari' => 'Senin',
            'waktu_mulai' => '08:00',
            'waktu_berakhir' => '10:00',
            'token_enrollment' => $token
        ]);

        $this->command->info('Jadwal Perkuliahan berhasil di-seed.');
    }
}
