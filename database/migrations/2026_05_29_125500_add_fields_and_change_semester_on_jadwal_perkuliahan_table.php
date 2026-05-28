<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom fakultas, prodi, tahun ke tabel jadwal_perkuliahan,
     * dan mengubah tipe data kolom semester dari string menjadi integer.
     */
    public function up(): void
    {
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            // Nama fakultas penyelenggara jadwal perkuliahan
            $table->string('fakultas')->nullable()->after('sks');

            // Nama program studi penyelenggara jadwal perkuliahan
            $table->string('prodi')->nullable()->after('fakultas');

            // Tahun ajaran (contoh: "2025/2026"), maksimal 9 karakter
            $table->string('tahun', 9)->nullable()->after('prodi');

            // Ubah tipe data semester dari string menjadi integer (1-14)
            $table->integer('semester')->change();
        });
    }

    /**
     * Rollback: hapus kolom fakultas, prodi, tahun dan kembalikan semester ke string.
     */
    public function down(): void
    {
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            $table->dropColumn(['fakultas', 'prodi', 'tahun']);

            // Kembalikan semester ke tipe string seperti semula
            $table->string('semester', 20)->change();
        });
    }
};
