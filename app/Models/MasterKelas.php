<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterKelas extends Model
{
    use HasFactory, HasUuids;

    /**
     * Nama tabel yang digunakan oleh model ini.
     */
    protected $table = 'master_kelas';

    /**
     * Primary key menggunakan UUID (bukan auto-increment integer).
     */
    protected $primaryKey = 'id_kelas';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Kolom yang boleh diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_kelas',
        'kode_kelas',
        'tahun_angkatan',
        'fakultas',
        'prodi',
    ];
}
