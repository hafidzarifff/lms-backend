<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_pertemuan', function (Blueprint $table) {
            $table->uuid('id_sesi')->primary();
            $table->uuid('id_jadwal');
            $table->foreign('id_jadwal')->references('id_jadwal')->on('jadwal_perkuliahan')->onDelete('cascade');
            $table->integer('pertemuan_ke');
            $table->string('judul_sesi', 100);
            $table->date('tanggal_pelaksanaan');
            $table->time('jam_mulai');
            $table->time('jam_berakhir');
            $table->enum('metode_pertemuan', ['synchronous', 'asynchronous']);
            $table->text('link_kelas_daring')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_pertemuan');
    }
};
