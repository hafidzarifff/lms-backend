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

    public function jadwalPerkuliahan()
    {
        return $this->belongsTo(JadwalPerkuliahan::class, 'id_jadwal', 'id_jadwal');
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'id_sesi', 'id_sesi');
    }
}
