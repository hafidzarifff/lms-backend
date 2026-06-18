<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTugasRequest;
use App\Http\Requests\UpdateTugasRequest;
use App\Models\Tugas;
use App\Models\SesiPertemuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TugasController extends Controller
{
    /**
     * GET /sesi/:sesi_id/tugas - List tugas di sesi (Dosen & Mahasiswa)
     */
    public function index(Request $request, string $sesi_id): JsonResponse
    {
        $sesi = SesiPertemuan::find($sesi_id);

        if (!$sesi) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak ditemukan.',
            ], 404);
        }

        $perPage = $request->query('per_page', 10);
        $tugas = Tugas::where('id_sesi', $sesi_id)
            ->orderBy('batas_waktu', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tugas,
        ], 200);
    }

    /**
     * POST /sesi/:sesi_id/tugas - Buat tugas baru (Dosen)
     */
    public function store(StoreTugasRequest $request, string $sesi_id): JsonResponse
    {
        $sesi = SesiPertemuan::find($sesi_id);

        if (!$sesi) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak ditemukan.',
            ], 404);
        }

        // Validasi dosen pemilik sesi
        $user = auth()->user();
        if ($sesi->jadwalPerkuliahan->id_dosen !== $user->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke sesi ini.',
            ], 403);
        }

        $validated = $request->validated();

        // Generate token CBT jika ada link CBT tapi tidak ada token
        if (isset($validated['link_cbt']) && $validated['link_cbt'] && !isset($validated['token_cbt'])) {
            $validated['token_cbt'] = Tugas::generateTokenCbt();
        }

        $validated['id_sesi'] = $sesi_id;

        $tugas = Tugas::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dibuat.',
            'data' => $tugas,
        ], 201);
    }

    /**
     * GET /tugas/:id - Detail tugas
     */
    public function show(string $id): JsonResponse
    {
        $tugas = Tugas::with('sesiPertemuan')->find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tugas,
        ], 200);
    }

    /**
     * PUT /tugas/:id - Update tugas (Dosen)
     */
    public function update(UpdateTugasRequest $request, string $id): JsonResponse
    {
        $tugas = Tugas::with('sesiPertemuan')->find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        // Validasi dosen pemilik sesi
        $user = auth()->user();
        if ($tugas->sesiPertemuan->jadwalPerkuliahan->id_dosen !== $user->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke tugas ini.',
            ], 403);
        }

        $validated = $request->validated();

        // Generate token CBT jika ada link CBT baru tapi tidak ada token
        if (isset($validated['link_cbt']) && $validated['link_cbt'] && !isset($validated['token_cbt'])) {
            $validated['token_cbt'] = Tugas::generateTokenCbt();
        }

        $tugas->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diupdate.',
            'data' => $tugas->fresh(),
        ], 200);
    }

    /**
     * DELETE /tugas/:id - Hapus tugas (Dosen)
     */
    public function destroy(string $id): JsonResponse
    {
        $tugas = Tugas::with('sesiPertemuan')->find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        // Validasi dosen pemilik sesi
        $user = auth()->user();
        if ($tugas->sesiPertemuan->jadwalPerkuliahan->id_dosen !== $user->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke tugas ini.',
            ], 403);
        }

        $tugas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dihapus.',
        ], 200);
    }

    /**
     * GET /tugas/:id/deadline - Cek deadline tugas
     */
    public function cekDeadline(string $id): JsonResponse
    {
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        $melewatiDeadline = $tugas->cekDeadline();

        return response()->json([
            'success' => true,
            'melewati_deadline' => $melewatiDeadline,
            'batas_waktu' => $tugas->batas_waktu,
            'waktu_sekarang' => now(),
        ], 200);
    }

    /**
     * GET /tugas/:id/launch/:id_peserta - Generate launch URL untuk CBT
     */
    public function getLaunchUrl(string $id, string $id_peserta): JsonResponse
    {
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        // Validasi tugas memiliki link CBT
        if (!$tugas->link_cbt) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas ini tidak memiliki link CBT.',
            ], 400);
        }

        $launchUrl = $tugas->getLaunchUrl($id_peserta);

        return response()->json([
            'success' => true,
            'launch_url' => $launchUrl,
            'judul_tugas' => $tugas->judul_tugas,
        ], 200);
    }
}
