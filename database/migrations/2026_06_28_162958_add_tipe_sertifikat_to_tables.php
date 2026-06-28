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
        Schema::table('template_sertifikat', function (Blueprint $table) {
            $table->string('tipe_sertifikat', 50)->default('kelulusan')->after('nama_template');
        });

        Schema::table('sertifikat', function (Blueprint $table) {
            $table->string('tipe_sertifikat', 50)->default('kelulusan')->after('id_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_sertifikat', function (Blueprint $table) {
            $table->dropColumn('tipe_sertifikat');
        });

        Schema::table('sertifikat', function (Blueprint $table) {
            $table->dropColumn('tipe_sertifikat');
        });
    }
};
