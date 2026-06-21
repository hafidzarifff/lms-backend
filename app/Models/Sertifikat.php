<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sertifikat extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'sertifikat';
    protected $primaryKey = 'id_sertifikat';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_peserta',
        'id_template',
        'nomor_sertifikat',
        'tanggal_terbit',
        'file_url',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
    ];

    /**
     * Relasi ke peserta_kelas
     */
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(PesertaKelas::class, 'id_peserta', 'id_peserta');
    }

    /**
     * Relasi ke template sertifikat
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateSertifikat::class, 'id_template', 'id_template');
    }

    /**
     * Generate nomor sertifikat unik
     * Format: SERT/YYYY/MM/XXXXX
     */
    public static function generateNomorSertifikat(): string
    {
        $year = date('Y');
        $month = date('m');

        // Hitung sertifikat yang diterbitkan bulan ini
        $count = self::whereYear('tanggal_terbit', $year)
            ->whereMonth('tanggal_terbit', $month)
            ->count();

        $sequence = str_pad($count + 1, 5, '0', STR_PAD_LEFT);

        return "SERT/{$year}/{$month}/{$sequence}";
    }
}
