<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\PesertaKelas;
use App\Models\SesiPertemuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresensiController extends Controller
{
    /**
     * Catat presensi mahasiswa untuk suatu sesi.
     */
    public function catat(Request $request): JsonResponse
    {
        $request->validate([
            'id_sesi' => 'required|uuid|exists:sesi_pertemuan,id_sesi',
            'id_peserta' => 'required|uuid|exists:peserta_kelas,id_peserta',
            'status_kehadiran' => 'required|in:hadir,izin,sakit,alpha',
        ]);

        // Cek apakah sudah ada presensi untuk peserta ini di sesi ini
        $existing = Presensi::where('id_sesi', $request->id_sesi)
            ->where('id_peserta', $request->id_peserta)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Presensi sudah tercatat untuk peserta ini di sesi ini.',
            ], 422);
        }

        $presensi = Presensi::create([
            'id_sesi' => $request->id_sesi,
            'id_peserta' => $request->id_peserta,
            'status_kehadiran' => $request->status_kehadiran,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Presensi berhasil dicatat.',
            'data' => $presensi,
        ], 201);
    }

    /**
     * Update status kehadiran presensi.
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status_kehadiran' => 'required|in:hadir,izin,sakit,alpha',
        ]);

        $presensi = Presensi::find($id);

        if (!$presensi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data presensi tidak ditemukan.',
            ], 404);
        }

        $presensi->update([
            'status_kehadiran' => $request->status_kehadiran,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status kehadiran berhasil diupdate.',
            'data' => $presensi,
        ], 200);
    }

    /**
     * Get presensi berdasarkan sesi.
     */
    public function getBySesi($id_sesi): JsonResponse
    {
        $presensi = Presensi::where('id_sesi', $id_sesi)
            ->with('pesertaKelas.mahasiswa')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $presensi,
        ], 200);
    }

    /**
     * Get presensi berdasarkan peserta.
     */
    public function getByPeserta($id_peserta): JsonResponse
    {
        $presensi = Presensi::where('id_peserta', $id_peserta)
            ->with('sesiPertemuan')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $presensi,
        ], 200);
    }

    /**
     * Hitung persentase kehadiran peserta untuk suatu jadwal.
     */
    public function hitungPersentase(Request $request): JsonResponse
    {
        $request->validate([
            'id_peserta' => 'required|uuid|exists:peserta_kelas,id_peserta',
            'id_jadwal' => 'required|uuid|exists:jadwal_perkuliahan,id_jadwal',
        ]);

        // Get semua sesi untuk jadwal ini
        $sesiIds = SesiPertemuan::where('id_jadwal', $request->id_jadwal)
            ->pluck('id_sesi');

        $totalSesi = $sesiIds->count();

        if ($totalSesi === 0) {
            return response()->json([
                'status' => 'success',
                'persentase' => 0,
                'message' => 'Belum ada sesi untuk jadwal ini.',
            ], 200);
        }

        // Hitung jumlah kehadiran
        $jumlahHadir = Presensi::whereIn('id_sesi', $sesiIds)
            ->where('id_peserta', $request->id_peserta)
            ->where('status_kehadiran', 'hadir')
            ->count();

        $persentase = round(($jumlahHadir / $totalSesi) * 100, 2);

        return response()->json([
            'status' => 'success',
            'persentase' => $persentase,
            'jumlah_hadir' => $jumlahHadir,
            'total_sesi' => $totalSesi,
        ], 200);
    }

    /**
     * Rekap kehadiran untuk suatu jadwal.
     */
    public function rekapKehadiran(Request $request): JsonResponse
    {
        $request->validate([
            'id_jadwal' => 'required|uuid|exists:jadwal_perkuliahan,id_jadwal',
        ]);

        // Get semua peserta untuk jadwal ini
        $peserta = PesertaKelas::where('id_jadwal', $request->id_jadwal)
            ->with('mahasiswa')
            ->get();

        // Get semua sesi untuk jadwal ini
        $sesiIds = SesiPertemuan::where('id_jadwal', $request->id_jadwal)
            ->pluck('id_sesi');

        $totalSesi = $sesiIds->count();

        $rekap = [];
        foreach ($peserta as $p) {
            $hadir = Presensi::whereIn('id_sesi', $sesiIds)
                ->where('id_peserta', $p->id_peserta)
                ->where('status_kehadiran', 'hadir')
                ->count();

            $izin = Presensi::whereIn('id_sesi', $sesiIds)
                ->where('id_peserta', $p->id_peserta)
                ->where('status_kehadiran', 'izin')
                ->count();

            $sakit = Presensi::whereIn('id_sesi', $sesiIds)
                ->where('id_peserta', $p->id_peserta)
                ->where('status_kehadiran', 'sakit')
                ->count();

            $alpha = Presensi::whereIn('id_sesi', $sesiIds)
                ->where('id_peserta', $p->id_peserta)
                ->where('status_kehadiran', 'alpha')
                ->count();

            $persentase = $totalSesi > 0 ? round(($hadir / $totalSesi) * 100, 2) : 0;

            $rekap[] = [
                'id_peserta' => $p->id_peserta,
                'mahasiswa' => $p->mahasiswa,
                'hadir' => $hadir,
                'izin' => $izin,
                'sakit' => $sakit,
                'alpha' => $alpha,
                'persentase' => $persentase,
            ];
        }

        return response()->json([
            'status' => 'success',
            'total_sesi' => $totalSesi,
            'rekap' => $rekap,
        ], 200);
    }
}
