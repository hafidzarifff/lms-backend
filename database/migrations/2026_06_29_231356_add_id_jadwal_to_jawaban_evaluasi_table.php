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
            $table->uuid('id_jadwal')->after('id_pertanyaan');

            $table->foreign('id_jadwal')
                ->references('id_jadwal')
                ->on('jadwal_perkuliahan')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jawaban_evaluasi', function (Blueprint $table) {
            $table->dropForeign(['id_jadwal']);
            $table->dropColumn('id_jadwal');
        });
    }
};
