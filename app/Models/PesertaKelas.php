<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaKelas extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Nama tabel yang digunakan oleh model ini.
     */
    protected $table = 'peserta_kelas';

    /**
     * Primary key menggunakan UUID (bukan auto-increment integer).
     */
    protected $primaryKey = 'id_peserta';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Kolom yang boleh diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_jadwal',
        'id_mahasiswa',
        'tanggal_daftar',
        'evaluasi_selesai',
        'kehadiran',
        'nilai_akhir',
        'status_kelayakan',
    ];

    /**
     * Casting tipe data kolom agar Laravel otomatis mengkonversi
     * tipe data saat membaca/menulis ke database.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_daftar'   => 'datetime',
            'evaluasi_selesai' => 'boolean',
            'nilai_akhir'      => 'decimal:2',
        ];
    }

    // ============================================================
    // RELASI: Eager Loading untuk mengambil data terkait
    // ============================================================

    /**
     * Relasi ke tabel jadwal_perkuliahan.
     * Menghubungkan id_jadwal di peserta_kelas dengan id_jadwal di jadwal_perkuliahan.
     */
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalPerkuliahan::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * Relasi ke tabel pengguna (Mahasiswa).
     * Menghubungkan id_mahasiswa di peserta_kelas dengan id_user di pengguna.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_mahasiswa', 'id_user');
    }
}
