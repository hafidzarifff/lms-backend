<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan database index pada kolom-kolom yang sering digunakan
     * untuk query filter, search, dan sorting.
     * Ini signifikan mempercepat query saat data sudah ribuan record.
     */
    public function up(): void
    {
        // Index pada tabel pengguna
        Schema::table('pengguna', function (Blueprint $table) {
            $table->index('role', 'idx_pengguna_role');
            $table->index('status_persetujuan', 'idx_pengguna_status_persetujuan');
            $table->index('status_aktif', 'idx_pengguna_status_aktif');
            $table->index(['role', 'status_persetujuan'], 'idx_pengguna_role_status');
            $table->index('angkatan', 'idx_pengguna_angkatan');
        });

        // Index pada tabel jadwal_perkuliahan
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            $table->index('semester', 'idx_jadwal_semester');
            $table->index('hari', 'idx_jadwal_hari');
            $table->index('tahun', 'idx_jadwal_tahun');
        });

        // Index pada tabel master_kelas
        Schema::table('master_kelas', function (Blueprint $table) {
            $table->index('tahun_angkatan', 'idx_kelas_tahun_angkatan');
        });

        // Index pada tabel master_mata_kuliah
        Schema::table('master_mata_kuliah', function (Blueprint $table) {
            $table->index('semester', 'idx_mk_semester');
            $table->index('sks', 'idx_mk_sks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropIndex('idx_pengguna_role');
            $table->dropIndex('idx_pengguna_status_persetujuan');
            $table->dropIndex('idx_pengguna_status_aktif');
            $table->dropIndex('idx_pengguna_role_status');
            $table->dropIndex('idx_pengguna_angkatan');
        });

        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            $table->dropIndex('idx_jadwal_semester');
            $table->dropIndex('idx_jadwal_hari');
            $table->dropIndex('idx_jadwal_tahun');
        });

        Schema::table('master_kelas', function (Blueprint $table) {
            $table->dropIndex('idx_kelas_tahun_angkatan');
        });

        Schema::table('master_mata_kuliah', function (Blueprint $table) {
            $table->dropIndex('idx_mk_semester');
            $table->dropIndex('idx_mk_sks');
        });
    }
};
