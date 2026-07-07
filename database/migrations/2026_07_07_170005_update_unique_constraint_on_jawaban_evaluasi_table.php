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
        Schema::table('jawaban_evaluasi', function (Blueprint $table) {
            $table->dropUnique('unique_pertanyaan_peserta');
            $table->unique(['id_pertanyaan', 'id_peserta', 'id_jadwal'], 'unique_pertanyaan_peserta_jadwal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jawaban_evaluasi', function (Blueprint $table) {
            $table->dropUnique('unique_pertanyaan_peserta_jadwal');
            $table->unique(['id_pertanyaan', 'id_peserta'], 'unique_pertanyaan_peserta');
        });
    }
};
