<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel peserta_kelas untuk menyimpan data enrollment mahasiswa ke jadwal perkuliahan.
     */
    public function up(): void
    {
        Schema::create('peserta_kelas', function (Blueprint $table) {
            // Primary key UUID
            $table->uuid('id_peserta')->primary();

            // Foreign key ke tabel jadwal_perkuliahan
            $table->uuid('id_jadwal');

            // Foreign key ke tabel pengguna (mahasiswa yang enroll)
            $table->uuid('id_mahasiswa');

            // Tanggal dan waktu mahasiswa mendaftar ke kelas
            $table->timestamp('tanggal_daftar')->useCurrent();

            // Status apakah mahasiswa sudah mengisi evaluasi akhir
            $table->boolean('evaluasi_selesai')->default(false);

            // Ringkasan kehadiran mahasiswa (contoh: '12/16')
            $table->string('kehadiran', 10)->default('0/0');

            // Nilai akhir akumulatif mahasiswa (skala 0.00 - 100.00)
            $table->decimal('nilai_akhir', 5, 2)->default(0.00);

            // Status kelayakan sertifikat (Lulus / Tidak Lulus / Belum Ditentukan)
            $table->string('status_kelayakan')->default('Belum Ditentukan');

            $table->timestamps();

            // ============================================================
            // Unique constraint: mencegah mahasiswa yang sama mendaftar
            // dua kali di jadwal yang sama
            // ============================================================
            $table->unique(['id_jadwal', 'id_mahasiswa'], 'unique_jadwal_mahasiswa');

            // Definisi foreign key constraints
            $table->foreign('id_jadwal')
                  ->references('id_jadwal')
                  ->on('jadwal_perkuliahan')
                  ->onDelete('cascade');

            $table->foreign('id_mahasiswa')
                  ->references('id_user')
                  ->on('pengguna')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_kelas');
    }
};
