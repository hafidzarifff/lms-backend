<?php

namespace App\Http\Controllers;

use App\Models\SesiPertemuan;
use App\Http\Requests\StoreSesiPertemuanRequest;
use App\Http\Requests\UpdateSesiPertemuanRequest;
use Illuminate\Http\JsonResponse;

class SesiPertemuanController extends Controller
{
    /**
     * Menampilkan daftar sesi pertemuan dengan pagination dan filter.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $perPage = request('per_page', 10);
        $idJadwal = request('id_jadwal');
        $tanggal = request('tanggal');
        $metode = request('metode_pertemuan');

        $query = SesiPertemuan::with('jadwalPerkuliahan');

        if ($idJadwal) {
            $query->where('id_jadwal', $idJadwal);
        }

        if ($tanggal) {
            $query->where('tanggal_pelaksanaan', $tanggal);
        }

        if ($metode) {
            $query->where('metode_pertemuan', $metode);
        }

        $sesiPertemuans = $query->orderBy('tanggal_pelaksanaan', 'desc')
            ->orderBy('pertemuan_ke', 'asc')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $sesiPertemuans
        ], 200);
    }

    /**
     * Menampilkan detail satu sesi pertemuan berdasarkan ID.
     *
     * @param string $id_sesi
     * @return JsonResponse
     */
    public function show(string $id_sesi): JsonResponse
    {
        $sesiPertemuan = SesiPertemuan::with(['jadwalPerkuliahan.mataKuliah', 'jadwalPerkuliahan.kelas'])->find($id_sesi);

        if (!$sesiPertemuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi pertemuan tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $sesiPertemuan
        ], 200);
    }

    /**
     * Menyimpan sesi pertemuan baru.
     *
     * @param StoreSesiPertemuanRequest $request
     * @return JsonResponse
     */
    public function store(StoreSesiPertemuanRequest $request)
    {
        $validatedData = $request->validated();

        $duplicatePertemuanKe = SesiPertemuan::where('id_jadwal', $validatedData['id_jadwal'])
            ->where('pertemuan_ke', $validatedData['pertemuan_ke'])
            ->exists();

        if ($duplicatePertemuanKe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertemuan ke-'.$validatedData['pertemuan_ke'].' sudah ada untuk jadwal ini.'
            ], 422);
        }

        $overlappingSesi = SesiPertemuan::where('id_jadwal', $validatedData['id_jadwal'])
            ->where('tanggal_pelaksanaan', $validatedData['tanggal_pelaksanaan'])
            ->where(function ($query) use ($validatedData) {
                $query->where('jam_mulai', '<', $validatedData['jam_berakhir'])
                      ->where('jam_berakhir', '>', $validatedData['jam_mulai']);
            })
            ->exists();

        if ($overlappingSesi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waktu sesi bentrok dengan sesi lain pada tanggal yang sama.'
            ], 422);
        }

        $sesiPertemuan = SesiPertemuan::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi pertemuan berhasil dibuat.',
            'data' => $sesiPertemuan
        ], 201);
    }

    /**
     * Memperbarui sesi pertemuan yang sudah ada.
     *
     * @param UpdateSesiPertemuanRequest $request
     * @param string $id_sesi
     * @return JsonResponse
     */
    public function update(UpdateSesiPertemuanRequest $request, string $id_sesi): JsonResponse
    {
        $sesiPertemuan = SesiPertemuan::find($id_sesi);

        if (!$sesiPertemuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi pertemuan tidak ditemukan.'
            ], 404);
        }

        $validatedData = $request->validated();

        // Cek duplikasi pertemuan_ke (exclude current record)
        $duplicatePertemuanKe = SesiPertemuan::where('id_jadwal', $sesiPertemuan->id_jadwal)
            ->where('pertemuan_ke', $validatedData['pertemuan_ke'])
            ->where('id_sesi', '!=', $id_sesi)
            ->exists();

        if ($duplicatePertemuanKe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertemuan ke-'.$validatedData['pertemuan_ke'].' sudah ada untuk jadwal ini.'
            ], 422);
        }

        // Cek overlap waktu (exclude current record)
        $overlappingSesi = SesiPertemuan::where('id_jadwal', $sesiPertemuan->id_jadwal)
            ->where('tanggal_pelaksanaan', $validatedData['tanggal_pelaksanaan'])
            ->where('id_sesi', '!=', $id_sesi)
            ->where(function ($query) use ($validatedData) {
                $query->where('jam_mulai', '<', $validatedData['jam_berakhir'])
                      ->where('jam_berakhir', '>', $validatedData['jam_mulai']);
            })
            ->exists();

        if ($overlappingSesi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waktu sesi bentrok dengan sesi lain pada tanggal yang sama.'
            ], 422);
        }

        $sesiPertemuan->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi pertemuan berhasil diperbarui.',
            'data' => $sesiPertemuan
        ], 200);
    }

    /**
     * Menghapus sesi pertemuan.
     *
     * @param string $id_sesi
     * @return JsonResponse
     */
    public function destroy(string $id_sesi): JsonResponse
    {
        $sesiPertemuan = SesiPertemuan::find($id_sesi);

        if (!$sesiPertemuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi pertemuan tidak ditemukan.'
            ], 404);
        }

        $sesiPertemuan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi pertemuan berhasil dihapus.'
        ], 200);
    }

    /**
     * Mendapatkan semua sesi berdasarkan jadwal.
     *
     * @param string $id_jadwal
     * @return JsonResponse
     */
    public function getByJadwal(string $id_jadwal): JsonResponse
    {
        $sesiPertemuans = SesiPertemuan::where('id_jadwal', $id_jadwal)
            ->orderBy('pertemuan_ke', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sesiPertemuans
        ], 200);
    }

    /**
     * Cek apakah sesi sedang aktif (berlangsung saat ini).
     *
     * @param string $id_sesi
     * @return JsonResponse
     */
    public function cekSesiAktif(string $id_sesi): JsonResponse
    {
        $sesiPertemuan = SesiPertemuan::find($id_sesi);

        if (!$sesiPertemuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi pertemuan tidak ditemukan.'
            ], 404);
        }

        $aktif = $sesiPertemuan->cekSesiAktif();

        return response()->json([
            'status' => 'success',
            'aktif' => $aktif,
            'data' => [
                'id_sesi' => $sesiPertemuan->id_sesi,
                'judul_sesi' => $sesiPertemuan->judul_sesi,
                'tanggal_pelaksanaan' => $sesiPertemuan->tanggal_pelaksanaan->format('Y-m-d'),
                'jam_mulai' => $sesiPertemuan->jam_mulai->format('H:i:s'),
                'jam_berakhir' => $sesiPertemuan->jam_berakhir->format('H:i:s'),
            ]
        ], 200);
    }
}
