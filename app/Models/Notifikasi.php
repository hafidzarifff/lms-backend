<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Notifikasi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_notifikasi',
        'id_user',
        'judul',
        'pesan',
        'tipe',
        'id_referensi',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_user', 'id_user');
    }
}
