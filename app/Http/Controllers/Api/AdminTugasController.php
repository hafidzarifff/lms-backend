<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use App\Models\PengumpulanTugas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTugasController extends Controller
{
    /**
     * GET /admin/tugas — Admin melihat semua tugas lintas sesi dengan pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = Tugas::with(['sesiPertemuan.jadwalPerkuliahan'])
            ->orderBy('created_at', 'desc')
            ->paginate(min($request->input('per_page', 20), 20));

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua tugas berhasil diambil.',
            'data'    => $paginator->items(),
            'meta'    => [
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total'    => $paginator->total(),
            ],
        ], 200);
    }

    /**
     * DELETE /admin/pengumpulan/:id — Admin menghapus pengumpulan (soft delete).
     */
    public function deletePengumpulan(string $id): JsonResponse
    {
        $pengumpulan = PengumpulanTugas::find($id);

        if (!$pengumpulan) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengumpulan tidak ditemukan.',
            ], 404);
        }

        $pengumpulan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pengumpulan berhasil dihapus.',
        ], 200);
    }
}
