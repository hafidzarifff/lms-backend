<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom semester, fakultas, dan prodi ke tabel master_mata_kuliah.
     * Kolom ditambahkan setelah kolom 'deskripsi' dan bersifat nullable agar aman untuk data existing.
     */
    public function up(): void
    {
        Schema::table('master_mata_kuliah', function (Blueprint $table) {
            // Semester perkuliahan (1-14), nullable agar data lama tidak terdampak
            $table->integer('semester')->nullable()->after('deskripsi');

            // Nama fakultas penyelenggara mata kuliah
            $table->string('fakultas')->nullable()->after('semester');

            // Nama program studi penyelenggara mata kuliah
            $table->string('prodi')->nullable()->after('fakultas');
        });
    }

    /**
     * Rollback: hapus kolom semester, fakultas, dan prodi dari tabel master_mata_kuliah.
     */
    public function down(): void
    {
        Schema::table('master_mata_kuliah', function (Blueprint $table) {
            $table->dropColumn(['semester', 'fakultas', 'prodi']);
        });
    }
};
