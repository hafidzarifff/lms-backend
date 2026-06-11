<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTugasRequest;
use App\Http\Requests\UpdateTugasRequest;
use App\Http\Requests\KumpulTugasRequest;
use App\Http\Requests\BeriNilaiRequest;
use App\Models\SesiPertemuan;
use App\Models\Tugas;
use App\Models\PengumpulanTugas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    // ============================================================
    // DOSEN: CRUD Tugas
    // ============================================================

    /**
     * POST /sesi/:sesi_id/tugas — Dosen membuat tugas baru di sesi tertentu.
     */
    public function store(StoreTugasRequest $request, string $sesi_id): JsonResponse
    {
        $sesi = SesiPertemuan::with('jadwalPerkuliahan')->find($sesi_id);

        if (!$sesi) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi pertemuan tidak ditemukan.',
            ], 404);
        }

        // Pastikan dosen yang mengakses adalah pemilik sesi
        $user = $request->user();
        if ($sesi->jadwalPerkuliahan->id_dosen !== $user->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke sesi ini.',
            ], 403);
        }

        $tugas = Tugas::create([
            'id_sesi'   => $sesi_id,
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'deadline'  => $request->deadline,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dibuat.',
            'data'    => $tugas,
        ], 201);
    }

    /**
     * GET /sesi/:sesi_id/tugas — List tugas di sesi (Dosen & Mahasiswa).
     */
    public function index(Request $request, string $sesi_id): JsonResponse
    {
        $sesi = SesiPertemuan::find($sesi_id);

        if (!$sesi) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi pertemuan tidak ditemukan.',
            ], 404);
        }

        $paginator = Tugas::where('id_sesi', $sesi_id)
            ->orderBy('deadline', 'asc')
            ->paginate(min($request->input('per_page', 20), 20));

        return response()->json([
            'success' => true,
            'message' => 'Daftar tugas berhasil diambil.',
            'data'    => $paginator->items(),
            'meta'    => [
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total'    => $paginator->total(),
            ],
        ], 200);
    }

    /**
     * GET /tugas/:id — Detail tugas.
     */
    public function show(string $id): JsonResponse
    {
        $tugas = Tugas::with('sesiPertemuan.jadwalPerkuliahan')->find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $tugas,
        ], 200);
    }

    /**
     * PUT /tugas/:id — Dosen mengedit tugas.
     */
    public function update(UpdateTugasRequest $request, string $id): JsonResponse
    {
        $tugas = Tugas::with('sesiPertemuan.jadwalPerkuliahan')->find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();
        if ($tugas->sesiPertemuan->jadwalPerkuliahan->id_dosen !== $user->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke tugas ini.',
            ], 403);
        }

        $tugas->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diperbarui.',
            'data'    => $tugas->fresh(),
        ], 200);
    }

    /**
     * DELETE /tugas/:id — Soft delete tugas.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $tugas = Tugas::with('sesiPertemuan.jadwalPerkuliahan')->find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();
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

    // ============================================================
    // DOSEN: Lihat Pengumpulan & Beri Nilai
    // ============================================================

    /**
     * GET /tugas/:id/pengumpulan — Dosen melihat semua pengumpulan mahasiswa.
     */
    public function pengumpulan(Request $request, string $id): JsonResponse
    {
        $tugas = Tugas::with('sesiPertemuan.jadwalPerkuliahan')->find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();
        if ($tugas->sesiPertemuan->jadwalPerkuliahan->id_dosen !== $user->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke tugas ini.',
            ], 403);
        }

        $paginator = PengumpulanTugas::with('mahasiswa')
            ->where('id_tugas', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(min($request->input('per_page', 20), 20));

        return response()->json([
            'success' => true,
            'message' => 'Daftar pengumpulan tugas berhasil diambil.',
            'data'    => $paginator->items(),
            'meta'    => [
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total'    => $paginator->total(),
            ],
        ], 200);
    }

    /**
     * PUT /pengumpulan/:id/nilai — Dosen memberi nilai dan catatan.
     */
    public function beriNilai(BeriNilaiRequest $request, string $id): JsonResponse
    {
        $pengumpulan = PengumpulanTugas::with('tugas.sesiPertemuan.jadwalPerkuliahan')->find($id);

        if (!$pengumpulan) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengumpulan tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();
        $idDosen = $pengumpulan->tugas->sesiPertemuan->jadwalPerkuliahan->id_dosen;
        if ($idDosen !== $user->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menilai pengumpulan ini.',
            ], 403);
        }

        $pengumpulan->update([
            'nilai'          => $request->nilai,
            'catatan_dosen'  => $request->catatan_dosen,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil diberikan.',
            'data'    => $pengumpulan->fresh(),
        ], 200);
    }

    // ============================================================
    // MAHASISWA: Kumpulkan Tugas & Lihat Status
    // ============================================================

    /**
     * POST /tugas/:id/kumpul — Mahasiswa mengumpulkan tugas.
     */
    public function kumpul(KumpulTugasRequest $request, string $id): JsonResponse
    {
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        // Cek apakah sudah melewati deadline
        if (now()->greaterThan($tugas->deadline)) {
            return response()->json([
                'success' => false,
                'message' => 'Deadline pengumpulan sudah terlewat.',
            ], 403);
        }

        $user = $request->user();

        // Cek apakah sudah pernah mengumpulkan (mencegah duplikat)
        $sudahKumpul = PengumpulanTugas::where('id_tugas', $id)
            ->where('id_mahasiswa', $user->id_user)
            ->exists();

        if ($sudahKumpul) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mengumpulkan tugas ini.',
            ], 422);
        }

        // Simpan file ke storage
        $filePath = $request->file('file')->store('tugas', 'local');

        $pengumpulan = PengumpulanTugas::create([
            'id_tugas'     => $id,
            'id_mahasiswa' => $user->id_user,
            'file_url'     => $filePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dikumpulkan.',
            'data'    => $pengumpulan,
        ], 201);
    }

    /**
     * GET /tugas/:id/pengumpulan/saya — Mahasiswa melihat status pengumpulan miliknya.
     */
    public function statusPengumpulan(Request $request, string $id): JsonResponse
    {
        $tugas = Tugas::find($id);

        if (!$tugas) {
            return response()->json([
                'success' => false,
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        $user = $request->user();

        $pengumpulan = PengumpulanTugas::where('id_tugas', $id)
            ->where('id_mahasiswa', $user->id_user)
            ->first();

        if (!$pengumpulan) {
            return response()->json([
                'success' => true,
                'message' => 'Anda belum mengumpulkan tugas ini.',
                'data'    => [
                    'status'       => 'belum_dikumpulkan',
                    'pengumpulan'  => null,
                ],
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status pengumpulan berhasil diambil.',
            'data'    => [
                'status'       => $pengumpulan->nilai !== null ? 'sudah_dinilai' : 'menunggu_penilaian',
                'pengumpulan'  => $pengumpulan,
            ],
        ], 200);
    }
}
