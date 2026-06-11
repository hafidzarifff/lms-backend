<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriPembelajaran extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'materi_pembelajaran';
    protected $primaryKey = 'id_materi';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_sesi',
        'judul_materi',
        'file_materi',
        'link_video_pembelajaran',
    ];

    /**
     * Relasi ke tabel sesi_pertemuan.
     */
    public function sesiPertemuan(): BelongsTo
    {
        return $this->belongsTo(SesiPertemuan::class, 'id_sesi', 'id_sesi');
    }
}
