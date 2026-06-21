<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumDiskusiRead extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'forum_diskusi_reads';

    protected $fillable = [
        'id_pesan',
        'id_user',
    ];

    /**
     * Relasi ke forum diskusi
     */
    public function pesan(): BelongsTo
    {
        return $this->belongsTo(ForumDiskusi::class, 'id_pesan', 'id_pesan');
    }

    /**
     * Relasi ke user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_user', 'id_user');
    }
}
