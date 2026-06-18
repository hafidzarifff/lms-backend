<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'presensi';
    protected $primaryKey = 'id_presensi';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_sesi',
        'id_peserta',
        'status_kehadiran',
    ];

    /**
     * Relasi ke tabel sesi_pertemuan.
     */
    public function sesiPertemuan(): BelongsTo
    {
        return $this->belongsTo(SesiPertemuan::class, 'id_sesi', 'id_sesi');
    }

    /**
     * Relasi ke tabel peserta_kelas.
     */
    public function pesertaKelas(): BelongsTo
    {
        return $this->belongsTo(PesertaKelas::class, 'id_peserta', 'id_peserta');
    }
}
