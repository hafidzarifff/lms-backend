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
            $table->text('materi')->nullable()->after('metode_pertemuan');
            $table->string('status', 50)->default('TERJADWAL')->after('materi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_pertemuan', function (Blueprint $table) {
            $table->dropColumn(['materi', 'status']);
        });
    }
};
