<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tugas extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tugas';
    protected $primaryKey = 'id_tugas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_sesi',
        'judul',
        'deskripsi',
        'deadline',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
        ];
    }

    /**
     * Relasi ke sesi_pertemuan.
     */
    public function sesiPertemuan(): BelongsTo
    {
        return $this->belongsTo(SesiPertemuan::class, 'id_sesi', 'id_sesi');
    }

    /**
     * Relasi ke pengumpulan_tugas (semua pengumpulan oleh mahasiswa).
     */
    public function pengumpulanTugas(): HasMany
    {
        return $this->hasMany(PengumpulanTugas::class, 'id_tugas', 'id_tugas');
    }
}
