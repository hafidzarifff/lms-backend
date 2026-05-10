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
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('fakultas')->nullable();
            $table->string('prodi')->nullable();
            $table->boolean('status_aktif')->default(false);
            $table->enum('status_persetujuan', ['Menunggu', 'Disetujui', 'Ditolak'])->default('Menunggu');
            $table->string('angkatan')->nullable();
            $table->string('foto_profil')->nullable();
            $table->timestamp('login_terakhir')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn([
                'fakultas',
                'prodi',
                'status_aktif',
                'status_persetujuan',
                'angkatan',
                'foto_profil',
                'login_terakhir'
            ]);
        });
    }
};
