<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengumpulanTugas extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'pengumpulan_tugas';
    protected $primaryKey = 'id_pengumpulan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_tugas',
        'id_mahasiswa',
        'file_url',
        'nilai',
        'catatan_dosen',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'integer',
        ];
    }

    /**
     * Relasi ke tugas.
     */
    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class, 'id_tugas', 'id_tugas');
    }

    /**
     * Relasi ke mahasiswa (pengguna).
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_mahasiswa', 'id_user');
    }
}
