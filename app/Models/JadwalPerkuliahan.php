<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalPerkuliahan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Nama tabel yang digunakan oleh model ini.
     */
    protected $table = 'jadwal_perkuliahan';

    /**
     * Primary key menggunakan UUID (bukan auto-increment integer).
     */
    protected $primaryKey = 'id_jadwal';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Kolom yang boleh diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_mk',
        'id_kelas',
        'id_dosen',
        'sks',
        'fakultas',
        'prodi',
        'tahun',
        'semester',
        'hari',
        'waktu_mulai',
        'waktu_berakhir',
        'token_enrollment',
        'tanggal_mulai',
        'akses_bebas',
    ];

    /**
     * Casting tipe data kolom.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sks' => 'integer',
            'semester' => 'integer',
            'akses_bebas' => 'boolean',
        ];
    }

    // ============================================================
    // RELASI: Eager Loading untuk mengambil data terkait
    // ============================================================

    /**
     * Relasi ke tabel master_mata_kuliah.
     * Menghubungkan id_mk di jadwal dengan id_mk di master_mata_kuliah.
     */
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MasterMataKuliah::class, 'id_mk', 'id_mk');
    }

    /**
     * Relasi ke tabel master_kelas.
     * Menghubungkan id_kelas di jadwal dengan id_kelas di master_kelas.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(MasterKelas::class, 'id_kelas', 'id_kelas');
    }

    /**
     * Relasi ke tabel pengguna (khusus Dosen).
     * Menghubungkan id_dosen di jadwal dengan id_user di pengguna.
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_dosen', 'id_user');
    }

    /**
     * Relasi ke tabel peserta_kelas.
     * Satu jadwal dapat memiliki banyak peserta (mahasiswa yang terdaftar).
     */
    public function pesertaKelas(): HasMany
    {
        return $this->hasMany(PesertaKelas::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * Relasi ke tabel sesi_pertemuan.
     * Satu jadwal dapat memiliki banyak sesi pertemuan.
     */
    public function sesiPertemuan(): HasMany
    {
        return $this->hasMany(SesiPertemuan::class, 'id_jadwal', 'id_jadwal');
    }
}
