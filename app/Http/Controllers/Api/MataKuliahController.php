<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMataKuliahRequest;
use App\Http\Requests\UpdateMataKuliahRequest;
use App\Models\MasterMataKuliah;
use Illuminate\Http\JsonResponse;

class MataKuliahController extends Controller
{
    /**
     * Mengambil daftar seluruh data master mata kuliah (Read All).
     * Data diurutkan dari yang terbaru dan menggunakan pagination 10 data per halaman.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $request = request();

        $perPage = min((int) $request->query('per_page', 20), 100);

        $mataKuliah = MasterMataKuliah::query()
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('kode_mk', 'ilike', "%{$search}%")
                        ->orWhere('nama_mk', 'ilike', "%{$search}%")
                        ->orWhere('fakultas', 'ilike', "%{$search}%")
                        ->orWhere('prodi', 'ilike', "%{$search}%");
                });
            })
            ->when($request->query('semester'), function ($q, $semester) {
                $q->where('semester', $semester);
            })
            ->when($request->query('sks'), function ($q, $sks) {
                $q->where('sks', $sks);
            })
            ->when($request->query('fakultas'), function ($q, $fakultas) {
                $q->where('fakultas', 'ilike', "%{$fakultas}%");
            })
            ->when($request->query('prodi'), function ($q, $prodi) {
                $q->where('prodi', 'ilike', "%{$prodi}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($mataKuliah, 200);
    }

    /**
     * Mengambil detail satu data mata kuliah berdasarkan ID (Read One).
     *
     * @param string $id_mk
     * @return JsonResponse
     */
    public function show(string $id_mk): JsonResponse
    {
        // Cari data mata kuliah berdasarkan primary key (id_mk)
        $mataKuliah = MasterMataKuliah::find($id_mk);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$mataKuliah) {
            return response()->json([
                'message' => 'Data mata kuliah tidak ditemukan.'
            ], 404);
        }

        // Jika ditemukan, kembalikan detail data mata kuliah
        return response()->json($mataKuliah, 200);
    }

    /**
     * Menyimpan data master mata kuliah baru (Create).
     *
     * @param StoreMataKuliahRequest $request
     * @return JsonResponse
     */
    public function store(StoreMataKuliahRequest $request): JsonResponse
    {
        // Ambil data yang sudah lolos validasi dari StoreMataKuliahRequest
        $validatedData = $request->validated();

        // Simpan data baru ke tabel master_mata_kuliah
        // (id_mk UUID akan di-generate otomatis oleh trait HasUuids di Model)
        $mataKuliah = MasterMataKuliah::create($validatedData);

        // Kembalikan response JSON 201 (Created) beserta data yang baru dibuat
        return response()->json([
            'message' => 'Master mata kuliah berhasil ditambahkan.',
            'data'    => $mataKuliah,
        ], 201);
    }

    /**
     * Memperbarui data master mata kuliah yang sudah ada (Update).
     *
     * @param UpdateMataKuliahRequest $request
     * @param string $id_mk
     * @return JsonResponse
     */
    public function update(UpdateMataKuliahRequest $request, string $id_mk): JsonResponse
    {
        // Cari data mata kuliah berdasarkan primary key
        $mataKuliah = MasterMataKuliah::find($id_mk);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$mataKuliah) {
            return response()->json([
                'message' => 'Data mata kuliah tidak ditemukan.'
            ], 404);
        }

        // Ambil data yang sudah lolos validasi dari UpdateMataKuliahRequest
        $validatedData = $request->validated();

        // Perbarui data mata kuliah di database
        $mataKuliah->update($validatedData);

        // Kembalikan response JSON 200 beserta pesan sukses
        return response()->json([
            'message' => 'Master mata kuliah berhasil diperbarui.',
        ], 200);
    }

    /**
     * Menghapus data master mata kuliah dari database (Delete).
     *
     * @param string $id_mk
     * @return JsonResponse
     */
    public function destroy(string $id_mk): JsonResponse
    {
        // Cari data mata kuliah berdasarkan primary key
        $mataKuliah = MasterMataKuliah::find($id_mk);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$mataKuliah) {
            return response()->json([
                'message' => 'Data mata kuliah tidak ditemukan.'
            ], 404);
        }

        // Lakukan proses delete record dari database
        $mataKuliah->delete();

        // Kembalikan response JSON 200 beserta pesan sukses
        return response()->json([
            'message' => 'Master mata kuliah berhasil dihapus.',
        ], 200);
    }
}
