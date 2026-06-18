<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateSertifikat extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'template_sertifikat';
    protected $primaryKey = 'id_template';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nama_template',
        'file_background',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    /**
     * Scope untuk template aktif saja
     */
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Relasi ke sertifikat yang diterbitkan
     */
    public function sertifikats(): HasMany
    {
        return $this->hasMany(Sertifikat::class, 'id_template', 'id_template');
    }
}
