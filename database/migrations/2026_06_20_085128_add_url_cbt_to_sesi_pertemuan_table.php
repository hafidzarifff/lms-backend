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
        Schema::table('sesi_pertemuan', function (Blueprint $table) {
            $table->text('url_cbt')->nullable()->after('materi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_pertemuan', function (Blueprint $table) {
            $table->dropColumn('url_cbt');
        });
    }
};
