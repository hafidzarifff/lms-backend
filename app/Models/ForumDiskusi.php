<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumDiskusi extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'forum_diskusi';
    protected $primaryKey = 'id_pesan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_sesi',
        'id_pengirim',
        'isi_pesan',
        'waktu_kirim',
        'id_parent_pesan',
    ];

    protected $casts = [
        'waktu_kirim' => 'datetime',
    ];

    /**
     * Relasi ke sesi pertemuan
     */
    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiPertemuan::class, 'id_sesi', 'id_sesi');
    }

    /**
     * Relasi ke pengirim (pengguna)
     */
    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengirim', 'id_user');
    }

    /**
     * Relasi ke parent pesan (untuk reply)
     */
    public function parentPesan(): BelongsTo
    {
        return $this->belongsTo(ForumDiskusi::class, 'id_parent_pesan', 'id_pesan');
    }

    /**
     * Relasi ke replies (pesan balasan)
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ForumDiskusi::class, 'id_parent_pesan', 'id_pesan');
    }

    /**
     * Relasi ke reads (status baca pesan)
     */
    public function reads(): HasMany
    {
        return $this->hasMany(ForumDiskusiRead::class, 'id_pesan', 'id_pesan');
    }

    /**
     * Scope untuk mendapatkan pesan utama (bukan reply)
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('id_parent_pesan');
    }

    /**
     * Scope untuk mendapatkan replies dari pesan tertentu
     */
    public function scopeRepliesOf($query, $idParent)
    {
        return $query->where('id_parent_pesan', $idParent);
    }
}
