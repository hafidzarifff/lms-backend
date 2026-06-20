<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SesiPertemuan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'sesi_pertemuan';
    protected $primaryKey = 'id_sesi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_jadwal',
        'pertemuan_ke',
        'judul_sesi',
        'tanggal_pelaksanaan',
        'jam_mulai',
        'jam_berakhir',
        'metode_pertemuan',
        'link_kelas_daring'
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'date',
        'jam_mulai' => 'datetime:H:i:s',
        'jam_berakhir' => 'datetime:H:i:s',
    ];

    /**
     * Cek apakah sesi sedang aktif (berlangsung saat ini).
     * Sesi aktif jika: tanggal hari ini DAN waktu sekarang di antara jam_mulai dan jam_berakhir.
     */
    public function cekSesiAktif(): bool
    {
        $sekarang = now();
        $tanggalSesi = $this->tanggal_pelaksanaan;

        // Cek apakah hari ini
        if (!$sekarang->isSameDay($tanggalSesi)) {
            return false;
        }

        // Cek apakah waktu sekarang di antara jam_mulai dan jam_berakhir
        $waktuSekarang = $sekarang->format('H:i:s');
        $jamMulai = $this->jam_mulai->format('H:i:s');
        $jamBerakhir = $this->jam_berakhir->format('H:i:s');

        return $waktuSekarang >= $jamMulai && $waktuSekarang <= $jamBerakhir;
    }

    public function jadwalPerkuliahan()
    {
        return $this->belongsTo(JadwalPerkuliahan::class, 'id_jadwal', 'id_jadwal');
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'id_sesi', 'id_sesi');
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'id_sesi', 'id_sesi');
    }

    public function materiPembelajaran()
    {
        return $this->hasMany(MateriPembelajaran::class, 'id_sesi', 'id_sesi');
    }
<<<<<<< HEAD
=======

    public function forumDiskusi()
    {
        return $this->hasMany(ForumDiskusi::class, 'id_sesi', 'id_sesi');
    }
>>>>>>> 5a6992ff7dab70d031aeaf89582083163a1fb51a
}
