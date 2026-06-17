<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertanyaan_evaluasi', function (Blueprint $table) {
            $table->uuid('id_pertanyaan')->primary();
            $table->string('kategori', 50);
            $table->text('teks_pertanyaan');
            $table->integer('urutan');
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kategori', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertanyaan_evaluasi');
    }
};
