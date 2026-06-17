<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiCbt extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'nilai_cbt';
    protected $primaryKey = 'id_nilai';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_tugas',
        'id_peserta',
        'nilai',
        'waktu_sinkron',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'waktu_sinkron' => 'datetime',
    ];

    /**
     * Relasi ke tugas
     */
    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class, 'id_tugas', 'id_tugas');
    }

    /**
     * Relasi ke peserta (pengguna)
     */
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_peserta', 'id_user');
    }
}
