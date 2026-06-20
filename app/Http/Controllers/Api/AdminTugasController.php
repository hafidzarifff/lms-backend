<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTugasController extends Controller
{
    /**
     * GET /admin/tugas - List semua tugas (Admin).
     * Menampilkan semua tugas dari semua sesi, dengan relasi sesi dan jadwal.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);

        $tugas = Tugas::with([
                'sesiPertemuan.jadwalPerkuliahan.mataKuliah',
                'sesiPertemuan.jadwalPerkuliahan.kelas',
                'sesiPertemuan.jadwalPerkuliahan.dosen',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tugas,
        ], 200);
    }
}
