<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterKelas;
use App\Models\MasterMataKuliah;
use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Mengambil data statistik dashboard dalam satu request.
     * Menggunakan COUNT query (bukan load semua data) agar ringan dan cepat.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'mahasiswa'   => Pengguna::where('role', 'Mahasiswa')->count(),
            'dosen'       => Pengguna::where('role', 'Dosen')
                                     ->where('status_persetujuan', 'Disetujui')
                                     ->count(),
            'kelas'       => MasterKelas::count(),
            'mata_kuliah' => MasterMataKuliah::count(),
        ];

        // 10 pengajuan dosen terbaru untuk widget verifikasi
        $dosenTerbaru = Pengguna::where('role', 'Dosen')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats'         => $stats,
            'dosen_terbaru' => $dosenTerbaru,
        ], 200);
    }
}
