<?php

namespace App\Http\Controllers;

use App\Models\JawabanEvaluasi;
use App\Models\PertanyaanEvaluasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class JawabanEvaluasiController extends Controller
{
    /**
     * Submit jawaban evaluasi (bulk support)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_peserta' => 'required|uuid|exists:pengguna,id_user',
            'id_jadwal' => 'required|uuid|exists:jadwal_perkuliahan,id_jadwal',
            'jawaban' => 'required|array|min:1',
            'jawaban.*.id_pertanyaan' => 'required|uuid|exists:pertanyaan_evaluasi,id_pertanyaan',
            'jawaban.*.skor' => 'nullable|integer|min:1|max:5',
            'jawaban.*.jawaban_teks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $inserted = [];
            $waktuSubmit = now();

            foreach ($request->jawaban as $item) {
                // Cek apakah sudah ada jawaban untuk pertanyaan + peserta + jadwal ini
                $existing = JawabanEvaluasi::where('id_pertanyaan', $item['id_pertanyaan'])
                    ->where('id_peserta', $request->id_peserta)
                    ->where('id_jadwal', $request->id_jadwal)
                    ->first();

                if ($existing) {
                    // Update jika sudah ada (resubmit)
                    $existing->update([
                        'skor' => $item['skor'] ?? null,
                        'jawaban_teks' => $item['jawaban_teks'] ?? null,
                        'waktu_submit' => $waktuSubmit,
                    ]);
                    $inserted[] = $existing;
                } else {
                    // Insert baru
                    $jawaban = JawabanEvaluasi::create([
                        'id_evaluasi' => Str::uuid(),
                        'id_pertanyaan' => $item['id_pertanyaan'],
                        'id_peserta' => $request->id_peserta,
                        'id_jadwal' => $request->id_jadwal,
                        'skor' => $item['skor'] ?? null,
                        'jawaban_teks' => $item['jawaban_teks'] ?? null,
                        'waktu_submit' => $waktuSubmit,
                    ]);
                    $inserted[] = $jawaban;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Evaluasi berhasil disimpan',
                'data' => $inserted,
                'total' => count($inserted)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan evaluasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get semua jawaban untuk peserta tertentu
     */
    public function getByPeserta($id_peserta)
    {
        $jawaban = JawabanEvaluasi::where('id_peserta', $id_peserta)
            ->with(['pertanyaan:id_pertanyaan,kategori,teks_pertanyaan', 'peserta:id_user,nama_lengkap,nim'])
            ->orderBy('waktu_submit', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $jawaban
        ]);
    }

    /**
     * Get semua jawaban untuk pertanyaan tertentu
     */
    public function getByPertanyaan($id_pertanyaan)
    {
        $jawaban = JawabanEvaluasi::where('id_pertanyaan', $id_pertanyaan)
            ->with(['pertanyaan:id_pertanyaan,kategori,teks_pertanyaan', 'peserta:id_user,nama_lengkap,nim'])
            ->orderBy('waktu_submit', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $jawaban
        ]);
    }

    /**
     * Get jawaban spesifik (pertanyaan + peserta)
     */
    public function show($id_pertanyaan, $id_peserta)
    {
        $jawaban = JawabanEvaluasi::where('id_pertanyaan', $id_pertanyaan)
            ->where('id_peserta', $id_peserta)
            ->with(['pertanyaan', 'peserta'])
            ->first();

        if (!$jawaban) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jawaban tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $jawaban
        ]);
    }

    /**
     * Update jawaban evaluasi
     */
    public function update(Request $request, $id_evaluasi)
    {
        $validator = Validator::make($request->all(), [
            'skor' => 'nullable|integer|min:1|max:5',
            'jawaban_teks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $jawaban = JawabanEvaluasi::find($id_evaluasi);

        if (!$jawaban) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jawaban tidak ditemukan'
            ], 404);
        }

        $jawaban->update([
            'skor' => $request->skor ?? null,
            'jawaban_teks' => $request->jawaban_teks ?? null,
            'waktu_submit' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jawaban berhasil diupdate',
            'data' => $jawaban
        ]);
    }

    /**
     * Delete jawaban evaluasi (soft delete)
     */
    public function destroy($id_evaluasi)
    {
        $jawaban = JawabanEvaluasi::find($id_evaluasi);

        if (!$jawaban) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jawaban tidak ditemukan'
            ], 404);
        }

        $jawaban->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Jawaban berhasil dihapus'
        ]);
    }

    /**
     * Get statistik jawaban per pertanyaan
     */
    public function getStatistikPertanyaan($id_pertanyaan)
    {
        $stats = DB::table('jawaban_evaluasi')
            ->where('id_pertanyaan', $id_pertanyaan)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_responden,
                AVG(skor) as rata_rata,
                MIN(skor) as skor_terendah,
                MAX(skor) as skor_tertinggi,
                SUM(CASE WHEN skor = 1 THEN 1 ELSE 0 END) as skor_1,
                SUM(CASE WHEN skor = 2 THEN 1 ELSE 0 END) as skor_2,
                SUM(CASE WHEN skor = 3 THEN 1 ELSE 0 END) as skor_3,
                SUM(CASE WHEN skor = 4 THEN 1 ELSE 0 END) as skor_4,
                SUM(CASE WHEN skor = 5 THEN 1 ELSE 0 END) as skor_5
            ')
            ->first();

        $pertanyaan = PertanyaanEvaluasi::find($id_pertanyaan);

        return response()->json([
            'status' => 'success',
            'data' => [
                'pertanyaan' => $pertanyaan,
                'statistik' => [
                    'total_responden' => $stats->total_responden,
                    'rata_rata' => round($stats->rata_rata, 2),
                    'skor_terendah' => $stats->skor_terendah,
                    'skor_tertinggi' => $stats->skor_tertinggi,
                    'distribusi' => [
                        'skor_1' => $stats->skor_1,
                        'skor_2' => $stats->skor_2,
                        'skor_3' => $stats->skor_3,
                        'skor_4' => $stats->skor_4,
                        'skor_5' => $stats->skor_5,
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get statistik evaluasi per kategori
     */
    public function getStatistikKategori()
    {
        $stats = DB::table('jawaban_evaluasi')
            ->join('pertanyaan_evaluasi', 'jawaban_evaluasi.id_pertanyaan', '=', 'pertanyaan_evaluasi.id_pertanyaan')
            ->whereNull('jawaban_evaluasi.deleted_at')
            ->whereNull('pertanyaan_evaluasi.deleted_at')
            ->selectRaw('
                pertanyaan_evaluasi.kategori,
                COUNT(DISTINCT jawaban_evaluasi.id_peserta) as total_responden,
                AVG(jawaban_evaluasi.skor) as rata_rata,
                COUNT(DISTINCT pertanyaan_evaluasi.id_pertanyaan) as jumlah_pertanyaan
            ')
            ->groupBy('pertanyaan_evaluasi.kategori')
            ->orderBy('kategori')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Cek apakah peserta sudah mengisi evaluasi
     */
    public function checkStatus($id_peserta)
    {
        $totalPertanyaan = PertanyaanEvaluasi::aktif()->count();
        $totalDijawab = JawabanEvaluasi::where('id_peserta', $id_peserta)
            ->join('pertanyaan_evaluasi', 'jawaban_evaluasi.id_pertanyaan', '=', 'pertanyaan_evaluasi.id_pertanyaan')
            ->where('pertanyaan_evaluasi.is_aktif', true)
            ->whereNull('jawaban_evaluasi.deleted_at')
            ->count('jawaban_evaluasi.id_evaluasi');

        $persentase = $totalPertanyaan > 0 ? round(($totalDijawab / $totalPertanyaan) * 100, 2) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'id_peserta' => $id_peserta,
                'total_pertanyaan' => $totalPertanyaan,
                'total_dijawab' => $totalDijawab,
                'persentase' => $persentase,
                'status' => $totalDijawab === $totalPertanyaan && $totalPertanyaan > 0 ? 'lengkap' : 'belum_lengkap'
            ]
        ]);
    }

    /**
     * Get rekap evaluasi keseluruhan
     */
    public function getRekap()
    {
        $rekap = [
            'total_responden' => JawabanEvaluasi::distinct('id_peserta')->count('id_peserta'),
            'total_jawaban' => JawabanEvaluasi::count(),
            'rata_rata_keseluruhan' => round(JawabanEvaluasi::avg('skor') ?? 0, 2),
            'evaluasi_per_kategori' => $this->getStatistikKategori()->getData()->data,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $rekap
        ]);
    }

    /**
     * Get hasil evaluasi anonim khusus untuk Dosen
     */
    public function getHasilByDosen($id_dosen)
    {
        // 1. Ambil daftar jadwal (kelas) yang diajar oleh dosen tersebut
        $jadwal = DB::table('jadwal_perkuliahan')
            ->join('master_mata_kuliah', 'jadwal_perkuliahan.id_mk', '=', 'master_mata_kuliah.id_mk')
            ->join('master_kelas', 'jadwal_perkuliahan.id_kelas', '=', 'master_kelas.id_kelas')
            ->where('jadwal_perkuliahan.id_dosen', $id_dosen)
            ->select(
                'jadwal_perkuliahan.id_jadwal',
                'master_mata_kuliah.nama_mk',
                'master_mata_kuliah.kode_mk',
                'master_kelas.nama_kelas',
                'jadwal_perkuliahan.semester',
                'jadwal_perkuliahan.fakultas',
                'jadwal_perkuliahan.prodi'
            )
            ->get();

        if ($jadwal->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        $hasil = [];

        foreach ($jadwal as $j) {
            // Ambil pertanyaan beserta rerata skor (untuk skala) dan list teks (untuk essay) 
            // KHUSUS untuk id_jadwal ini
            $pertanyaanList = PertanyaanEvaluasi::aktif()->orderBy('kategori')->orderBy('urutan')->get();
            
            $detailPertanyaan = [];
            foreach ($pertanyaanList as $p) {
                $jawabanJadwalIni = DB::table('jawaban_evaluasi')
                    ->where('id_pertanyaan', $p->id_pertanyaan)
                    ->where('id_jadwal', $j->id_jadwal)
                    ->whereNull('deleted_at');

                $totalResponden = (clone $jawabanJadwalIni)->count();

                if ($p->tipe_pertanyaan === 'skala') {
                    $rataRata = (clone $jawabanJadwalIni)->avg('skor');
                    $detailPertanyaan[] = [
                        'id_pertanyaan' => $p->id_pertanyaan,
                        'kategori' => $p->kategori,
                        'teks_pertanyaan' => $p->teks_pertanyaan,
                        'tipe_pertanyaan' => $p->tipe_pertanyaan,
                        'total_responden' => $totalResponden,
                        'rata_rata' => $rataRata ? round($rataRata, 2) : 0,
                    ];
                } else {
                    $listTeks = (clone $jawabanJadwalIni)
                        ->whereNotNull('jawaban_teks')
                        ->pluck('jawaban_teks');
                        
                    $detailPertanyaan[] = [
                        'id_pertanyaan' => $p->id_pertanyaan,
                        'kategori' => $p->kategori,
                        'teks_pertanyaan' => $p->teks_pertanyaan,
                        'tipe_pertanyaan' => $p->tipe_pertanyaan,
                        'total_responden' => $totalResponden,
                        'jawaban_teks' => $listTeks,
                    ];
                }
            }

            // Hitung rata-rata keseluruhan untuk kelas/jadwal ini
            $rataKeseluruhan = DB::table('jawaban_evaluasi')
                ->join('pertanyaan_evaluasi', 'jawaban_evaluasi.id_pertanyaan', '=', 'pertanyaan_evaluasi.id_pertanyaan')
                ->where('jawaban_evaluasi.id_jadwal', $j->id_jadwal)
                ->where('pertanyaan_evaluasi.tipe_pertanyaan', 'skala')
                ->whereNull('jawaban_evaluasi.deleted_at')
                ->avg('jawaban_evaluasi.skor');

            $hasil[] = [
                'id_jadwal' => $j->id_jadwal,
                'mata_kuliah' => $j->nama_mk . ' (' . $j->kode_mk . ')',
                'kelas' => $j->nama_kelas,
                'semester' => $j->semester,
                'fakultas' => $j->fakultas,
                'prodi' => $j->prodi,
                'rata_rata_keseluruhan' => $rataKeseluruhan ? round($rataKeseluruhan, 2) : 0,
                'detail_pertanyaan' => $detailPertanyaan,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $hasil
        ]);
    }
}
