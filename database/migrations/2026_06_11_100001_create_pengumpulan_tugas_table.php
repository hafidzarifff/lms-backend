<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumpulan_tugas', function (Blueprint $table) {
            $table->uuid('id_pengumpulan')->primary();
            $table->uuid('id_tugas');
            $table->uuid('id_mahasiswa');
            $table->foreign('id_tugas')->references('id_tugas')->on('tugas')->onDelete('cascade');
            $table->foreign('id_mahasiswa')->references('id_user')->on('pengguna')->onDelete('cascade');
            $table->string('file_url', 500);
            $table->integer('nilai')->nullable();
            $table->text('catatan_dosen')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['id_tugas', 'id_mahasiswa']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_tugas');
    }
};
