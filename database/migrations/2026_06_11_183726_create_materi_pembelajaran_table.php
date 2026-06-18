<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel materi pembelajaran untuk menyimpan materi per sesi.
     */
    public function up(): void
    {
        Schema::create('materi_pembelajaran', function (Blueprint $table) {
            // Primary key UUID
            $table->uuid('id_materi')->primary();

            // Foreign key ke tabel sesi_pertemuan
            $table->uuid('id_sesi');

            // Judul materi
            $table->string('judul_materi', 200);

            // Path file materi (nullable)
            $table->string('file_materi', 500)->nullable();

            // Link video pembelajaran (nullable)
            $table->string('link_video_pembelajaran', 500)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Definisi foreign key constraint
            $table->foreign('id_sesi')
                  ->references('id_sesi')
                  ->on('sesi_pertemuan')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_pembelajaran');
    }
};
