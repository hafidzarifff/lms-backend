<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKelasRequest;
use App\Http\Requests\UpdateKelasRequest;
use App\Models\MasterKelas;
use Illuminate\Http\JsonResponse;

class KelasController extends Controller
{
    /**
     * Mengambil daftar seluruh data master kelas (Read All).
     * Data diurutkan dari yang terbaru dan menggunakan pagination 10 data per halaman.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $request = request();

        $perPage = min((int) $request->query('per_page', 20), 100);

        $kelas = MasterKelas::query()
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_kelas', 'ilike', "%{$search}%")
                        ->orWhere('kode_kelas', 'ilike', "%{$search}%")
                        ->orWhere('fakultas', 'ilike', "%{$search}%")
                        ->orWhere('prodi', 'ilike', "%{$search}%");
                });
            })
            ->when($request->query('tahun_angkatan'), function ($q, $tahun) {
                $q->where('tahun_angkatan', $tahun);
            })
            ->when($request->query('fakultas'), function ($q, $fakultas) {
                $q->where('fakultas', 'ilike', "%{$fakultas}%");
            })
            ->when($request->query('prodi'), function ($q, $prodi) {
                $q->where('prodi', 'ilike', "%{$prodi}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($kelas, 200);
    }

    /**
     * Mengambil detail satu data kelas berdasarkan ID (Read One).
     *
     * @param string $id_kelas
     * @return JsonResponse
     */
    public function show(string $id_kelas): JsonResponse
    {
        // Cari data kelas berdasarkan primary key (id_kelas)
        $kelas = MasterKelas::find($id_kelas);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$kelas) {
            return response()->json([
                'message' => 'Data kelas tidak ditemukan.'
            ], 404);
        }

        // Jika ditemukan, kembalikan detail data kelas
        return response()->json($kelas, 200);
    }

    /**
     * Menyimpan data master kelas baru (Create).
     *
     * @param StoreKelasRequest $request
     * @return JsonResponse
     */
    public function store(StoreKelasRequest $request): JsonResponse
    {
        // Ambil data yang sudah lolos validasi dari StoreKelasRequest
        $validatedData = $request->validated();

        // Simpan data baru ke tabel master_kelas
        // (id_kelas UUID akan di-generate otomatis oleh trait HasUuids di Model)
        $kelas = MasterKelas::create($validatedData);

        // Kembalikan response JSON 201 (Created) beserta data yang baru dibuat
        return response()->json([
            'message' => 'Master kelas berhasil ditambahkan.',
            'data'    => $kelas,
        ], 201);
    }

    /**
     * Memperbarui data master kelas yang sudah ada (Update).
     *
     * @param UpdateKelasRequest $request
     * @param string $id_kelas
     * @return JsonResponse
     */
    public function update(UpdateKelasRequest $request, string $id_kelas): JsonResponse
    {
        // Cari data kelas berdasarkan primary key
        $kelas = MasterKelas::find($id_kelas);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$kelas) {
            return response()->json([
                'message' => 'Data kelas tidak ditemukan.'
            ], 404);
        }

        // Ambil data yang sudah lolos validasi dari UpdateKelasRequest
        $validatedData = $request->validated();

        // Perbarui data kelas di database
        $kelas->update($validatedData);

        // Kembalikan response JSON 200 beserta pesan sukses
        return response()->json([
            'message' => 'Master kelas berhasil diperbarui.',
        ], 200);
    }

    /**
     * Menghapus data master kelas dari database (Delete).
     *
     * @param string $id_kelas
     * @return JsonResponse
     */
    public function destroy(string $id_kelas): JsonResponse
    {
        // Cari data kelas berdasarkan primary key
        $kelas = MasterKelas::find($id_kelas);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$kelas) {
            return response()->json([
                'message' => 'Data kelas tidak ditemukan.'
            ], 404);
        }

        // Lakukan proses delete record dari database
        $kelas->delete();

        // Kembalikan response JSON 200 beserta pesan sukses
        return response()->json([
            'message' => 'Master kelas berhasil dihapus.',
        ], 200);
    }
}
