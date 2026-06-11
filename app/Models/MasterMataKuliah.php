<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MasterMataKuliah extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Nama tabel yang digunakan oleh model ini.
     */
    protected $table = 'master_mata_kuliah';

    /**
     * Primary key menggunakan UUID (bukan auto-increment integer).
     */
    protected $primaryKey = 'id_mk';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Kolom yang boleh diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode_mk',
        'nama_mk',
        'sks',
        'deskripsi',
        'semester',
        'fakultas',
        'prodi',
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
        ];
    }
}
