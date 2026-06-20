<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kembali kolom username ke tabel pengguna.
     * Username bersifat nullable dan unik, digunakan untuk login Admin & Dosen
     * sebagai alternatif selain email.
     */
    public function up(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            // Tambahkan username setelah kolom email, nullable & unique
            $table->string('username')->nullable()->unique()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
