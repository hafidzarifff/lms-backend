<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_perkuliahan', function (Blueprint $table) {
            // Primary key UUID
            $table->uuid('id_jadwal')->primary();

            // Foreign keys ke tabel terkait
            $table->uuid('id_mk');
            $table->uuid('id_kelas');
            $table->uuid('id_dosen');

            // Bobot SKS (diambil otomatis dari master_mata_kuliah)
            $table->integer('sks');

            // Semester dengan format "YYYY - Ganjil" atau "YYYY - Genap"
            $table->string('semester', 20);

            // Hari perkuliahan (Senin, Selasa, dst)
            $table->string('hari', 10);

            // Waktu mulai dan berakhir perkuliahan
            $table->time('waktu_mulai');
            $table->time('waktu_berakhir');

            // Token unik untuk enrollment mahasiswa (6 karakter huruf kapital)
            $table->string('token_enrollment', 10)->unique();

            $table->timestamps();

            // Definisi foreign key constraints
            $table->foreign('id_mk')->references('id_mk')->on('master_mata_kuliah')->onDelete('cascade');
            $table->foreign('id_kelas')->references('id_kelas')->on('master_kelas')->onDelete('cascade');
            $table->foreign('id_dosen')->references('id_user')->on('pengguna')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_perkuliahan');
    }
};
