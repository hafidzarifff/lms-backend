<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_evaluasi', function (Blueprint $table) {
            $table->uuid('id_evaluasi')->primary();
            $table->uuid('id_pertanyaan');
            $table->uuid('id_peserta');
            $table->integer('skor');
            $table->timestamp('waktu_submit')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('id_pertanyaan')
                ->references('id_pertanyaan')
                ->on('pertanyaan_evaluasi')
                ->onDelete('cascade');

            $table->foreign('id_peserta')
                ->references('id_user')
                ->on('pengguna')
                ->onDelete('cascade');

            // Unique constraint: 1 peserta = 1 jawaban per pertanyaan
            $table->unique(['id_pertanyaan', 'id_peserta'], 'unique_pertanyaan_peserta');

            // Index untuk performa
            $table->index('id_peserta');
            $table->index('id_pertanyaan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_evaluasi');
    }
};
