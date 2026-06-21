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
        Schema::create('forum_diskusi_reads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_pesan');
            $table->uuid('id_user');
            $table->timestamps();

            $table->foreign('id_pesan')->references('id_pesan')->on('forum_diskusi')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('pengguna')->onDelete('cascade');
            
            // Seorang user hanya bisa membaca pesan satu kali
            $table->unique(['id_pesan', 'id_user']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_diskusi_reads');
    }
};
