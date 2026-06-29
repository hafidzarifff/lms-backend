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
        Schema::table('pertanyaan_evaluasi', function (Blueprint $table) {
            $table->enum('tipe_pertanyaan', ['skala', 'teks'])->default('skala')->after('teks_pertanyaan');
        });

        Schema::table('jawaban_evaluasi', function (Blueprint $table) {
            $table->integer('skor')->nullable()->change();
            $table->text('jawaban_teks')->nullable()->after('skor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jawaban_evaluasi', function (Blueprint $table) {
            $table->dropColumn('jawaban_teks');
            $table->integer('skor')->nullable(false)->change();
        });

        Schema::table('pertanyaan_evaluasi', function (Blueprint $table) {
            $table->dropColumn('tipe_pertanyaan');
        });
    }
};
