<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sertifikat', function (Blueprint $table) {
            $table->uuid('id_sertifikat')->primary();
            $table->uuid('id_peserta');
            $table->uuid('id_template');
            $table->string('nomor_sertifikat', 50)->unique();
            $table->date('tanggal_terbit');
            $table->string('file_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('id_peserta')
                ->references('id_user')
                ->on('pengguna')
                ->onDelete('cascade');

            $table->foreign('id_template')
                ->references('id_template')
                ->on('template_sertifikat')
                ->onDelete('cascade');

            // Index untuk performa
            $table->index('id_peserta');
            $table->index('id_template');
            $table->index('tanggal_terbit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikat');
    }
};
