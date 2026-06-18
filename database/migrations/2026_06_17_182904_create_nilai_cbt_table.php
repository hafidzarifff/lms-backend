<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_cbt', function (Blueprint $table) {
            $table->uuid('id_nilai')->primary();
            $table->uuid('id_tugas');
            $table->uuid('id_peserta');
            $table->decimal('nilai', 8, 2);
            $table->timestamp('waktu_sinkron')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('id_tugas')
                ->references('id_tugas')
                ->on('tugas')
                ->onDelete('cascade');

            $table->foreign('id_peserta')
                ->references('id_user')
                ->on('pengguna')
                ->onDelete('cascade');

            // Index untuk performa query
            $table->index(['id_tugas', 'id_peserta']);
            $table->index('id_peserta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_cbt');
    }
};
