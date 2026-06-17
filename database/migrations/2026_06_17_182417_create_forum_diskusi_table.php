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
        Schema::create('forum_diskusi', function (Blueprint $table) {
            // Primary key UUID
            $table->uuid('id_pesan')->primary();

            // Foreign key ke tabel sesi_pertemuan
            $table->uuid('id_sesi');

            // Foreign key ke tabel pengguna (pengirim pesan)
            $table->uuid('id_pengirim');

            // Isi pesan diskusi
            $table->text('isi_pesan');

            // Waktu pengiriman pesan
            $table->timestamp('waktu_kirim')->useCurrent();

            // Self-reference untuk reply (nullable karena pesan utama tidak punya parent)
            $table->uuid('id_parent_pesan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('id_sesi')
                  ->references('id_sesi')
                  ->on('sesi_pertemuan')
                  ->onDelete('cascade');

            $table->foreign('id_pengirim')
                  ->references('id_user')
                  ->on('pengguna')
                  ->onDelete('cascade');

            // Self-reference foreign key untuk reply
            $table->foreign('id_parent_pesan')
                  ->references('id_pesan')
                  ->on('forum_diskusi')
                  ->onDelete('cascade');

            // Index untuk performa query
            $table->index('id_sesi');
            $table->index('id_parent_pesan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_diskusi');
    }
};
