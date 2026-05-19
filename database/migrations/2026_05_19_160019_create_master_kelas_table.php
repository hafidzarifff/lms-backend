<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel master_kelas untuk menyimpan data referensi kelas.
     */
    public function up(): void
    {
        Schema::create('master_kelas', function (Blueprint $table) {
            // Primary key UUID, di-generate otomatis oleh trait HasUuids di Model
            $table->uuid('id_kelas')->primary();

            // Nama kelas (contoh: Kelas A, Kelas B)
            $table->string('nama_kelas', 50);

            // Kode unik identifikasi kelas (harus unik di seluruh tabel)
            $table->string('kode_kelas', 10)->unique();

            // Tahun angkatan yang diizinkan mengisi kelas ini
            $table->string('tahun_angkatan');

            $table->timestamps();
        });
    }

    /**
     * Rollback: hapus tabel master_kelas.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_kelas');
    }
};
