<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel presensi untuk mencatat kehadiran mahasiswa per sesi.
     */
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            // Primary key UUID
            $table->uuid('id_presensi')->primary();

            // Foreign key ke tabel sesi_pertemuan
            $table->uuid('id_sesi');

            // Foreign key ke tabel peserta_kelas
            $table->uuid('id_peserta');

            // Status kehadiran: hadir, izin, sakit, alpha
            $table->enum('status_kehadiran', ['hadir', 'izin', 'sakit', 'alpha']);

            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: mencegah duplikasi presensi untuk peserta yang sama di sesi yang sama
            $table->unique(['id_sesi', 'id_peserta'], 'unique_sesi_peserta');

            // Definisi foreign key constraints
            $table->foreign('id_sesi')
                  ->references('id_sesi')
                  ->on('sesi_pertemuan')
                  ->onDelete('cascade');

            $table->foreign('id_peserta')
                  ->references('id_peserta')
                  ->on('peserta_kelas')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
