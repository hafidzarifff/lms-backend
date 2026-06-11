<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update tabel tugas agar sesuai dengan UML.
     */
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            // Rename kolom sesuai UML
            $table->renameColumn('judul', 'judul_tugas');
            $table->renameColumn('deskripsi', 'deskripsi_tugas');
            $table->renameColumn('deadline', 'batas_waktu');

            // Tambah kolom CBT (Computer Based Test)
            $table->string('link_cbt')->nullable()->after('batas_waktu');
            $table->string('token_cbt', 10)->nullable()->after('link_cbt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            // Hapus kolom CBT
            $table->dropColumn(['link_cbt', 'token_cbt']);

            // Kembalikan nama kolom
            $table->renameColumn('judul_tugas', 'judul');
            $table->renameColumn('deskripsi_tugas', 'deskripsi');
            $table->renameColumn('batas_waktu', 'deadline');
        });
    }
};
