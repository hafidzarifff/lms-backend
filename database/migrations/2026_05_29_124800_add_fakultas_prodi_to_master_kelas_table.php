<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom fakultas dan prodi ke tabel master_kelas.
     * Kolom ditambahkan setelah kolom 'tahun_angkatan' dan bersifat nullable agar aman untuk data existing.
     */
    public function up(): void
    {
        Schema::table('master_kelas', function (Blueprint $table) {
            // Nama fakultas penyelenggara kelas
            $table->string('fakultas')->nullable()->after('tahun_angkatan');

            // Nama program studi penyelenggara kelas
            $table->string('prodi')->nullable()->after('fakultas');
        });
    }

    /**
     * Rollback: hapus kolom fakultas dan prodi dari tabel master_kelas.
     */
    public function down(): void
    {
        Schema::table('master_kelas', function (Blueprint $table) {
            $table->dropColumn(['fakultas', 'prodi']);
        });
    }
};
