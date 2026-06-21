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
        Schema::table('sertifikat', function (Blueprint $table) {
            // Drop foreign key lama yang mengarah ke tabel pengguna
            $table->dropForeign(['id_peserta']);

            // Buat foreign key baru yang mengarah ke tabel peserta_kelas
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
        Schema::table('sertifikat', function (Blueprint $table) {
            // Drop foreign key baru
            $table->dropForeign(['id_peserta']);

            // Kembalikan foreign key lama yang mengarah ke pengguna
            $table->foreign('id_peserta')
                ->references('id_user')
                ->on('pengguna')
                ->onDelete('cascade');
        });
    }
};
