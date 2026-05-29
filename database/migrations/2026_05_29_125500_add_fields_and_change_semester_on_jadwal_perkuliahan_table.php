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
        // 1. Eksekusi RAW Query khusus PostgreSQL untuk mengubah tipe data dengan klausa USING
        // Kita gunakan klausa USING dan melakukan casting, jika ada text non-numeric akan diubah menjadi null/0 terlebih dahulu
        DB::statement('ALTER TABLE "jadwal_perkuliahan" ALTER COLUMN "semester" TYPE integer USING (CASE WHEN semester ~ \'^[0-9]+$\' THEN semester::integer ELSE 1 END)');

        // 2. Jalankan blueprint sisa kolom lainnya seperti biasa
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            // Definisikan ulang semester agar sinkron dengan skema Laravel
            $table->integer('semester')->change();
            
            // Menambahkan 3 field baru
            $table->string('fakultas')->nullable()->after('sks');
            $table->string('prodi')->nullable()->after('fakultas');
            $table->string('tahun', 9)->nullable()->after('prodi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_perkuliahan', function (Blueprint $table) {
            $table->string('semester')->change();
            $table->dropColumn(['fakultas', 'prodi', 'tahun']);
        });
    }
};
