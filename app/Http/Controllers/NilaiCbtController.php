<?php

namespace App\Http\Controllers;

use App\Models\NilaiCbt;
use App\Models\Tugas;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NilaiCbtController extends Controller
{
    /**
     * Input nilai CBT (single atau bulk)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nilai' => 'required|array|min:1',
            'nilai.*.id_tugas' => 'required|uuid|exists:tugas,id_tugas',
            'nilai.*.id_peserta' => 'required|uuid|exists:pengguna,id_user',
            'nilai.*.nilai' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $inserted = [];

            foreach ($request->nilai as $item) {
                // Cek apakah sudah ada nilai untuk tugas + peserta ini
                $existing = NilaiCbt::where('id_tugas', $item['id_tugas'])
                    ->where('id_peserta', $item['id_peserta'])
                    ->first();

                if ($existing) {
                    // Update jika sudah ada
                    $existing->update([
                        'nilai' => $item['nilai'],
                        'waktu_sinkron' => now(),
                    ]);
                    $inserted[] = $existing;
                } else {
                    // Insert baru
                    $nilai = NilaiCbt::create([
                        'id_nilai' => Str::uuid(),
                        'id_tugas' => $item['id_tugas'],
                        'id_peserta' => $item['id_peserta'],
                        'nilai' => $item['nilai'],
                        'waktu_sinkron' => now(),
                    ]);
                    $inserted[] = $nilai;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Nilai CBT berhasil disimpan',
                'data' => $inserted
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get semua nilai untuk tugas tertentu
     */
    public function getByTugas($id_tugas)
    {
        $nilai = NilaiCbt::where('id_tugas', $id_tugas)
            ->with(['peserta:id_user,nama_lengkap,nim,email', 'tugas:id_tugas,judul_tugas'])
            ->orderBy('nilai', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $nilai
        ]);
    }

    /**
     * Get semua nilai untuk peserta tertentu
     */
    public function getByPeserta($id_peserta)
    {
        $nilai = NilaiCbt::where('id_peserta', $id_peserta)
            ->with(['tugas:id_tugas,judul_tugas,batas_waktu', 'peserta:id_user,nama_lengkap,nim'])
            ->orderBy('waktu_sinkron', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $nilai
        ]);
    }

    /**
     * Get nilai spesifik (tugas + peserta)
     */
    public function show($id_tugas, $id_peserta)
    {
        $nilai = NilaiCbt::where('id_tugas', $id_tugas)
            ->where('id_peserta', $id_peserta)
            ->with(['tugas', 'peserta'])
            ->first();

        if (!$nilai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nilai tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $nilai
        ]);
    }

    /**
     * Update nilai
     */
    public function update(Request $request, $id_nilai)
    {
        $validator = Validator::make($request->all(), [
            'nilai' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $nilai = NilaiCbt::find($id_nilai);

        if (!$nilai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nilai tidak ditemukan'
            ], 404);
        }

        $nilai->update([
            'nilai' => $request->nilai,
            'waktu_sinkron' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Nilai berhasil diupdate',
            'data' => $nilai
        ]);
    }

    /**
     * Delete nilai (soft delete)
     */
    public function destroy($id_nilai)
    {
        $nilai = NilaiCbt::find($id_nilai);

        if (!$nilai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nilai tidak ditemukan'
            ], 404);
        }

        $nilai->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Nilai berhasil dihapus'
        ]);
    }

    /**
     * Get statistik nilai untuk tugas tertentu
     */
    public function getStatistik($id_tugas)
    {
        $stats = DB::table('nilai_cbt')
            ->where('id_tugas', $id_tugas)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_peserta,
                AVG(nilai) as rata_rata,
                MIN(nilai) as nilai_terendah,
                MAX(nilai) as nilai_tertinggi,
                SUM(CASE WHEN nilai >= 70 THEN 1 ELSE 0 END) as lulus,
                SUM(CASE WHEN nilai < 70 THEN 1 ELSE 0 END) as tidak_lulus
            ')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_peserta' => $stats->total_peserta,
                'rata_rata' => round($stats->rata_rata, 2),
                'nilai_terendah' => $stats->nilai_terendah,
                'nilai_tertinggi' => $stats->nilai_tertinggi,
                'lulus' => $stats->lulus,
                'tidak_lulus' => $stats->tidak_lulus,
                'persentase_kelulusan' => $stats->total_peserta > 0
                    ? round(($stats->lulus / $stats->total_peserta) * 100, 2)
                    : 0
            ]
        ]);
    }

    /**
     * Get ranking untuk tugas tertentu
     */
    public function getRanking($id_tugas, $limit = 10)
    {
        $ranking = DB::table('nilai_cbt')
            ->join('pengguna', 'nilai_cbt.id_peserta', '=', 'pengguna.id_user')
            ->where('nilai_cbt.id_tugas', $id_tugas)
            ->whereNull('nilai_cbt.deleted_at')
            ->select(
                'pengguna.id_user',
                'pengguna.nama_lengkap',
                'pengguna.nim',
                'nilai_cbt.nilai',
                'nilai_cbt.waktu_sinkron'
            )
            ->orderBy('nilai_cbt.nilai', 'desc')
            ->orderBy('nilai_cbt.waktu_sinkron', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($item, $index) {
                $item->ranking = $index + 1;
                return $item;
            });

        return response()->json([
            'status' => 'success',
            'data' => $ranking
        ]);
    }
}
