<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PertanyaanEvaluasi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'pertanyaan_evaluasi';
    protected $primaryKey = 'id_pertanyaan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'kategori',
        'teks_pertanyaan',
        'urutan',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    /**
     * Scope untuk pertanyaan aktif saja
     */
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Scope untuk kategori tertentu
     */
    public function scopeKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Relasi ke jawaban evaluasi
     */
    public function jawabanEvaluasi(): HasMany
    {
        return $this->hasMany(JawabanEvaluasi::class, 'id_pertanyaan', 'id_pertanyaan');
    }
}
