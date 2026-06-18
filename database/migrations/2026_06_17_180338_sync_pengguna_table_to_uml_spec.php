<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sinkronisasi tabel pengguna sesuai spesifikasi UML.
     * - Tambah: nomor_telepon, tanggal_lahir, alamat
     * - Hapus: username (tidak ada di spesifikasi UML)
     */
    public function up(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            // Tambah field baru sesuai UML
            $table->string('nomor_telepon')->nullable()->after('status_persetujuan');
            $table->date('tanggal_lahir')->nullable()->after('nomor_telepon');
            $table->text('alamat')->nullable()->after('tanggal_lahir');

            // Hapus field username (tidak ada di spesifikasi UML)
            $table->dropColumn('username');
        });
    }

    /**
     * Reverse: kembalikan ke kondisi sebelum sync.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            // Hapus field baru
            $table->dropColumn(['nomor_telepon', 'tanggal_lahir', 'alamat']);

            // Kembalikan field username
            $table->string('username')->nullable()->unique();
        });
    }
};
