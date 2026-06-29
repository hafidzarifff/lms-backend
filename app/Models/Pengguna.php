<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\RolePengguna;

class Pengguna extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $table = 'pengguna';
    
    protected $primaryKey = 'id_user';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_user',
        'nama_lengkap',
        'role',
        'email',
        'username',
        'nomor_induk',
        'password',
        'fakultas',
        'prodi',
        'angkatan',
        'foto_profil',
        'status_aktif',
        'status_persetujuan',
        'nomor_telepon',
        'tanggal_lahir',
        'alamat',
        'login_terakhir',
        'is_first_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => RolePengguna::class,
            'password' => 'hashed',
            'status_aktif' => 'boolean',
            'is_first_login' => 'boolean',
        ];
    }

    /**
     * Relasi ke forum diskusi (pesan yang dikirim oleh pengguna ini)
     */
    public function forumDiskusi(): HasMany
    {
        return $this->hasMany(ForumDiskusi::class, 'id_pengirim', 'id_user');
    }
}
