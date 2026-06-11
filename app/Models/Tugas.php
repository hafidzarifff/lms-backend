<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Tugas extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tugas';
    protected $primaryKey = 'id_tugas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_sesi',
        'judul_tugas',
        'deskripsi_tugas',
        'batas_waktu',
        'link_cbt',
        'token_cbt',
    ];

    protected function casts(): array
    {
        return [
            'batas_waktu' => 'datetime',
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
     * Cek apakah tugas sudah melewati batas waktu.
     */
    public function cekDeadline(): bool
    {
        return now()->greaterThan($this->batas_waktu);
    }

    /**
     * Generate launch URL untuk CBT berdasarkan peserta.
     * Hanya bisa jika tugas memiliki link_cbt dan token_cbt.
     */
    public function getLaunchUrl(string $id_peserta): ?string
    {
        if (!$this->link_cbt || !$this->token_cbt) {
            return null;
        }

        // Generate launch URL dengan parameter token dan peserta
        $url = $this->link_cbt;
        $separator = str_contains($url, '?') ? '&' : '?';
        $url .= $separator . http_build_query([
            'token' => $this->token_cbt,
            'id_peserta' => $id_peserta,
            'id_tugas' => $this->id_tugas,
        ]);

        return $url;
    }

    /**
     * Generate token CBT baru (6 karakter alphanumeric).
     */
    public static function generateTokenCbt(): string
    {
        return strtoupper(Str::random(6));
    }
}
