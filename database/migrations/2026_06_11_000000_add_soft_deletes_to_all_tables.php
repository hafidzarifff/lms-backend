<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom deleted_at untuk soft delete pada semua tabel.
     * Soft delete memungkinkan data tidak hilang permanen saat dihapus,
     * hanya mengubah kolom deleted_at dengan timestamp saat penghapusan.
     */
    public function up(): void
    {
        // Tabel pengguna (user/admin/dosen/mahasiswa)
        Schema::table('pengguna', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Tabel master_kelas
        Schema::table('master_kelas', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Tabel master_mata_kuliah
        Schema::table('master_mata_kuliah', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Tabel jadwal_perkuliahan
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Tabel peserta_kelas
        Schema::table('peserta_kelas', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Tabel sesi_pertemuan
        Schema::table('sesi_pertemuan', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse: hapus kolom deleted_at dari semua tabel.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('master_kelas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('master_mata_kuliah', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('peserta_kelas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('sesi_pertemuan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
