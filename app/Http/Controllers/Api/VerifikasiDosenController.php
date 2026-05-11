<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\UpdateVerifikasiDosenRequest;
use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;

class VerifikasiDosenController extends Controller
{
    /**
     * Menampilkan daftar antrean registrasi dosen.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Inisiasi query untuk mengambil data dari tabel pengguna dengan role 'Dosen'
        $query = Pengguna::where('role', 'Dosen');

        // 2. Filter opsional berdasarkan status_persetujuan (Menunggu / Disetujui / Ditolak)
        if ($request->has('status')) {
            $query->where('status_persetujuan', $request->query('status'));
        }

        // 3. Urutkan berdasarkan data terbaru dan terapkan pagination 50 data per halaman
        $dosen = $query->orderBy('created_at', 'desc')->paginate(50);

        // 4. Kembalikan response JSON berisi list data dosen
        return response()->json($dosen, 200);
    }

    /**
     * Memproses persetujuan atau penolakan registrasi dosen.
     *
     * @param  UpdateVerifikasiDosenRequest  $request
     * @param  string  $id_user
     * @return JsonResponse
     */
    public function updateStatus(UpdateVerifikasiDosenRequest $request, $id_user): JsonResponse
    {
        // 1. Cari data pengguna berdasarkan ID dan pastikan rolenya 'Dosen'
        $dosen = Pengguna::where('id_user', $id_user)
            ->where('role', 'Dosen')
            ->first();

        // 2. Jika tidak ditemukan, return response JSON (404)
        if (!$dosen) {
            return response()->json([
                'message' => 'Data dosen tidak ditemukan.'
            ], 404);
        }

        $validatedData = $request->validated();
        $statusBaru = $validatedData['status_persetujuan'];

        // 3. Lakukan pengecekan status yang dikirimkan Admin
        if ($statusBaru === 'Disetujui') {
            $dosen->status_persetujuan = 'Disetujui';
            $dosen->status_aktif = true; // Langsung bisa login dan mengajar
            $message = 'Registrasi dosen berhasil disetujui.';
        } else {
            // Asumsi nilainya pasti 'Ditolak' karena sudah divalidasi oleh Request in:Disetujui,Ditolak
            $dosen->status_persetujuan = 'Ditolak';
            $dosen->status_aktif = false; // Tetap tidak bisa login
            $message = 'Registrasi dosen telah ditolak.';
        }

        // 4. Simpan perubahan ke database
        $dosen->save();

        // 5. Return response JSON (200) dengan pesan dinamis
        return response()->json([
            'message' => $message
        ], 200);
    }
}
