<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanEvaluasi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'jawaban_evaluasi';
    protected $primaryKey = 'id_evaluasi';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_pertanyaan',
        'id_peserta',
        'id_jadwal',
        'skor',
        'jawaban_teks',
        'waktu_submit',
    ];

    protected $casts = [
        'skor' => 'integer',
        'waktu_submit' => 'datetime',
    ];

    /**
     * Relasi ke pertanyaan evaluasi
     */
    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(PertanyaanEvaluasi::class, 'id_pertanyaan', 'id_pertanyaan');
    }

    /**
     * Relasi ke peserta (mahasiswa)
     */
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_peserta', 'id_user');
    }

    /**
     * Relasi ke jadwal perkuliahan
     */
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalPerkuliahan::class, 'id_jadwal', 'id_jadwal');
    }
}
