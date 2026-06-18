<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_sertifikat', function (Blueprint $table) {
            $table->uuid('id_template')->primary();
            $table->string('nama_template', 100);
            $table->string('file_background', 300)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_sertifikat');
    }
};
