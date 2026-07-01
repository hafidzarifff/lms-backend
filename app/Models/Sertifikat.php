<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sertifikat extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'sertifikat';
    protected $primaryKey = 'id_sertifikat';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id_peserta',
        'id_template',
        'tipe_sertifikat',
        'nomor_sertifikat',
        'tanggal_terbit',
        'file_url',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
    ];

    protected $appends = ['daftar_nilai'];

    /**
     * Mengambil daftar nilai peserta berdasarkan tugas-tugas dalam jadwal terkait
     */
    public function getDaftarNilaiAttribute()
    {
        $peserta = $this->peserta;
        if (!$peserta || !$peserta->id_jadwal) return [];

        $sesiList = \App\Models\SesiPertemuan::where('id_jadwal', $peserta->id_jadwal)
            ->with(['tugas'])
            ->orderBy('pertemuan_ke')
            ->get();

        $result = [];
        foreach ($sesiList as $sesi) {
            foreach ($sesi->tugas as $tugas) {
                $nilai = \App\Models\NilaiCbt::where('id_tugas', $tugas->id_tugas)
                    ->where('id_peserta', $peserta->id_mahasiswa) // NilaiCbt terhubung ke id_user mahasiswa
                    ->first();

                $result[] = [
                    'pertemuan' => $sesi->pertemuan_ke,
                    'tugas' => $tugas->judul_tugas,
                    'nilai' => $nilai ? $nilai->nilai : 0,
                ];
            }
        }
        return $result;
    }

    /**
     * Relasi ke peserta_kelas
     */
    public function peserta(): BelongsTo
    {
        return $this->belongsTo(PesertaKelas::class, 'id_peserta', 'id_peserta');
    }

    /**
     * Relasi ke template sertifikat
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateSertifikat::class, 'id_template', 'id_template');
    }

    /**
     * Generate nomor sertifikat unik
     * Format: SERT/{kode_matkul}/YYYY/MM/XXXXX
     */
    public static function generateNomorSertifikat(string $kode_matkul = 'UMUM'): string
    {
        $year = date('Y');
        $month = date('m');

        // Hitung sertifikat yang diterbitkan bulan ini untuk matkul ini
        $count = self::whereYear('tanggal_terbit', $year)
            ->whereMonth('tanggal_terbit', $month)
            ->where('nomor_sertifikat', 'like', "%/{$kode_matkul}/%")
            ->distinct('nomor_sertifikat')
            ->count('nomor_sertifikat');

        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return "SERT/{$kode_matkul}/{$year}/{$month}/{$sequence}";
    }

    /**
     * Mengecek dan membuat sertifikat jika belum ada, berdasarkan template aktif
     *
     * @param PesertaKelas $peserta
     */
    public static function generateForPeserta(PesertaKelas $peserta): void
    {
        if ($peserta->status_kelayakan !== 'Disetujui') {
            return;
        }

        $peserta->loadMissing('jadwal.mataKuliah');
        $kode_matkul = $peserta->jadwal->mataKuliah->kode_mk ?? 'UMUM';

        $sesiPertemuan = SesiPertemuan::where('id_jadwal', $peserta->id_jadwal)->get();
        $totalSesi = $sesiPertemuan->count();
        
        $jumlahHadir = Presensi::whereIn('id_sesi', $sesiPertemuan->pluck('id_sesi'))
            ->where('id_peserta', $peserta->id_peserta)
            ->where('status_kehadiran', 'hadir')
            ->count();
        
        $tugasList = Tugas::whereIn('id_sesi', $sesiPertemuan->pluck('id_sesi'))->get();
        
        $nilaiPeserta = NilaiCbt::whereIn('id_tugas', $tugasList->pluck('id_tugas'))
            ->where('id_peserta', $peserta->id_mahasiswa)
            ->get();
        
        $totalTugasDinilai = $nilaiPeserta->count();
        $rataRata = $totalTugasDinilai > 0 ? ($nilaiPeserta->sum('nilai') / $totalTugasDinilai) : 0;

        $templates = TemplateSertifikat::where('is_aktif', true)->get()->keyBy('tipe_sertifikat');
        
        $existingSertifikat = self::where('id_peserta', $peserta->id_peserta)->first();
        $nomorSertifikat = $existingSertifikat ? $existingSertifikat->nomor_sertifikat : self::generateNomorSertifikat($kode_matkul);

        // Sertifikat Pelatihan (Kehadiran >= 75%)
        $isPelatihanMet = $totalSesi > 0 && ($jumlahHadir / $totalSesi) >= 0.75;
        if ($isPelatihanMet && isset($templates['pelatihan'])) {
            self::firstOrCreate(
                ['id_peserta' => $peserta->id_peserta, 'tipe_sertifikat' => 'pelatihan'],
                [
                    'id_sertifikat' => \Illuminate\Support\Str::uuid(),
                    'id_template' => $templates['pelatihan']->id_template,
                    'nomor_sertifikat' => $nomorSertifikat,
                    'tanggal_terbit' => now(),
                ]
            );
        }

        // Sertifikat Kelulusan (Nilai Akhir >= 70)
        $isKelulusanMet = $rataRata >= 70;
        if ($isKelulusanMet && isset($templates['kelulusan'])) {
            self::firstOrCreate(
                ['id_peserta' => $peserta->id_peserta, 'tipe_sertifikat' => 'kelulusan'],
                [
                    'id_sertifikat' => \Illuminate\Support\Str::uuid(),
                    'id_template' => $templates['kelulusan']->id_template,
                    'nomor_sertifikat' => $nomorSertifikat,
                    'tanggal_terbit' => now(),
                ]
            );
        }

        // Sertifikat Daftar Nilai (Otomatis dapat jika disetujui)
        if (isset($templates['nilai'])) {
            self::firstOrCreate(
                ['id_peserta' => $peserta->id_peserta, 'tipe_sertifikat' => 'nilai'],
                [
                    'id_sertifikat' => \Illuminate\Support\Str::uuid(),
                    'id_template' => $templates['nilai']->id_template,
                    'nomor_sertifikat' => $nomorSertifikat,
                    'tanggal_terbit' => now(),
                ]
            );
        }
    }
}
