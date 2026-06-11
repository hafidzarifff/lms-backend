<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJadwalRequest;
use App\Http\Requests\UpdateJadwalRequest;
use App\Models\JadwalPerkuliahan;
use App\Models\MasterMataKuliah;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class JadwalPerkuliahanController extends Controller
{
    /**
     * Mengambil daftar seluruh jadwal perkuliahan (Read All).
     * Menggunakan Eager Loading untuk memuat relasi mataKuliah, kelas, dan dosen
     * agar data nama_mk, nama_kelas, dan nama_lengkap dosen ikut terbawa dalam response JSON.
     * Data diurutkan dari yang terbaru dan menggunakan pagination 10 data per halaman.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $request = request();

        $perPage = min((int) $request->query('per_page', 20), 100);

        $jadwal = JadwalPerkuliahan::with([
                'mataKuliah:id_mk,kode_mk,nama_mk,sks',
                'kelas:id_kelas,kode_kelas,nama_kelas',
                'dosen:id_user,nama_lengkap,nomor_induk',
            ])
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('fakultas', 'ilike', "%{$search}%")
                        ->orWhere('prodi', 'ilike', "%{$search}%")
                        ->orWhereHas('mataKuliah', function ($mk) use ($search) {
                            $mk->where('nama_mk', 'ilike', "%{$search}%");
                        })
                        ->orWhereHas('kelas', function ($k) use ($search) {
                            $k->where('nama_kelas', 'ilike', "%{$search}%");
                        })
                        ->orWhereHas('dosen', function ($d) use ($search) {
                            $d->where('nama_lengkap', 'ilike', "%{$search}%")
                              ->orWhere('nomor_induk', 'ilike', "%{$search}%");
                        });
                });
            })
            ->when($request->query('semester'), function ($q, $semester) {
                $q->where('semester', $semester);
            })
            ->when($request->query('tahun'), function ($q, $tahun) {
                $q->where('tahun', $tahun);
            })
            ->when($request->query('hari'), function ($q, $hari) {
                $q->where('hari', $hari);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($jadwal, 200);
    }

    /**
     * Mengambil detail satu jadwal perkuliahan berdasarkan ID (Read One).
     * Sertakan Eager Loading agar data relasi ikut tampil.
     *
     * @param string $id_jadwal
     * @return JsonResponse
     */
    public function show(string $id_jadwal): JsonResponse
    {
        // Eager Loading relasi saat mengambil detail satu jadwal
        $jadwal = JadwalPerkuliahan::with(['mataKuliah', 'kelas', 'dosen'])
            ->find($id_jadwal);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$jadwal) {
            return response()->json([
                'message' => 'Data jadwal perkuliahan tidak ditemukan.'
            ], 404);
        }

        return response()->json($jadwal, 200);
    }

    /**
     * Menyimpan data jadwal perkuliahan baru (Create).
     * - SKS diambil otomatis dari tabel master_mata_kuliah berdasarkan id_mk yang dipilih.
     * - Token enrollment di-generate otomatis sebagai string acak kapital 6 karakter unik.
     *
     * @param StoreJadwalRequest $request
     * @return JsonResponse
     */
    public function store(StoreJadwalRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // ============================================================
        // Penarikan SKS otomatis dari tabel master_mata_kuliah
        // Admin tidak perlu input SKS manual, cukup pilih mata kuliah
        // ============================================================
        $mataKuliah = MasterMataKuliah::find($validatedData['id_mk']);
        $validatedData['sks'] = $mataKuliah->sks;

        // ============================================================
        // Pembuatan token enrollment unik
        // Token berupa 6 karakter huruf kapital acak (contoh: "ABCXYZ")
        // Loop do-while memastikan token yang dihasilkan belum ada di database
        // ============================================================
        do {
            $token = Str::upper(Str::random(6));
        } while (JadwalPerkuliahan::where('token_enrollment', $token)->exists());

        $validatedData['token_enrollment'] = $token;

        // Simpan data baru ke tabel jadwal_perkuliahan
        // (id_jadwal UUID akan di-generate otomatis oleh trait HasUuids di Model)
        $jadwal = JadwalPerkuliahan::create($validatedData);

        // Muat relasi agar response lengkap dengan data terkait
        $jadwal->load(['mataKuliah', 'kelas', 'dosen']);

        return response()->json([
            'message' => 'Jadwal perkuliahan berhasil ditambahkan.',
            'data'    => $jadwal,
        ], 201);
    }

    /**
     * Memperbarui data jadwal perkuliahan yang sudah ada (Update).
     * Token enrollment tidak diubah.
     * Jika id_mk berubah, maka SKS akan otomatis menyesuaikan dari mata kuliah baru.
     *
     * @param UpdateJadwalRequest $request
     * @param string $id_jadwal
     * @return JsonResponse
     */
    public function update(UpdateJadwalRequest $request, string $id_jadwal): JsonResponse
    {
        $jadwal = JadwalPerkuliahan::find($id_jadwal);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$jadwal) {
            return response()->json([
                'message' => 'Data jadwal perkuliahan tidak ditemukan.'
            ], 404);
        }

        $validatedData = $request->validated();

        // ============================================================
        // Cek apakah id_mk berubah dari data sebelumnya
        // Jika berubah, tarik ulang SKS dari mata kuliah yang baru dipilih
        // Jika tidak berubah, SKS tetap menggunakan nilai lama
        // ============================================================
        if ($validatedData['id_mk'] !== $jadwal->id_mk) {
            $mataKuliah = MasterMataKuliah::find($validatedData['id_mk']);
            $validatedData['sks'] = $mataKuliah->sks;
        }

        // Update data jadwal (token_enrollment tidak diubah karena tidak ada di validated data)
        $jadwal->update($validatedData);

        return response()->json([
            'message' => 'Jadwal perkuliahan berhasil diperbarui.',
        ], 200);
    }

    /**
     * Menghapus data jadwal perkuliahan dari database (Delete).
     *
     * @param string $id_jadwal
     * @return JsonResponse
     */
    public function destroy(string $id_jadwal): JsonResponse
    {
        $jadwal = JadwalPerkuliahan::find($id_jadwal);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$jadwal) {
            return response()->json([
                'message' => 'Data jadwal perkuliahan tidak ditemukan.'
            ], 404);
        }

        // Lakukan proses delete record dari database
        $jadwal->delete();

        return response()->json([
            'message' => 'Jadwal perkuliahan berhasil dihapus.',
        ], 200);
    }
}
