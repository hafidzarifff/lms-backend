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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->uuid('id_notifikasi')->primary();
            $table->uuid('id_user');
            $table->string('judul');
            $table->text('pesan');
            $table->string('tipe'); // misal: 'forum', 'sertifikat'
            $table->uuid('id_referensi')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('pengguna')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
