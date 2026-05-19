<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel master_mata_kuliah untuk menyimpan data referensi mata kuliah.
     */
    public function up(): void
    {
        Schema::create('master_mata_kuliah', function (Blueprint $table) {
            // Primary key UUID, di-generate otomatis oleh trait HasUuids di Model
            $table->uuid('id_mk')->primary();

            // Kode resmi mata kuliah kampus (harus unik di seluruh tabel)
            $table->string('kode_mk', 10)->unique();

            // Nama mata kuliah
            $table->string('nama_mk', 100);

            // Bobot Satuan Kredit Semester (SKS)
            $table->integer('sks');

            // Penjelasan atau silabus mata kuliah (boleh dikosongkan)
            $table->text('deskripsi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Rollback: hapus tabel master_mata_kuliah.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_mata_kuliah');
    }
};
