<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDosenRequest;
use App\Models\Pengguna;
use Illuminate\Http\JsonResponse;

class DosenController extends Controller
{
    /**
     * Memperbarui data dosen berdasarkan ID.
     *
     * @param UpdateDosenRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateDosenRequest $request, string $id): JsonResponse
    {
        // Cari pengguna berdasarkan ID
        $dosen = Pengguna::find($id);

        // Jika data tidak ditemukan atau role bukan Dosen, kembalikan 404
        if (!$dosen || $dosen->role->value !== 'Dosen') {
            return response()->json([
                'message' => 'Data dosen tidak ditemukan.'
            ], 404);
        }

        // Ambil data dari request yang sudah lolos validasi
        $validatedData = $request->validated();

        // Update data dosen (JANGAN ubah password di sini)
        $dosen->update([
            'nama_lengkap' => $validatedData['nama_lengkap'],
            'nomor_induk' => $validatedData['nomor_induk'],
            'email' => $validatedData['email'],
            'fakultas' => $validatedData['fakultas'],
            'prodi' => $validatedData['prodi'],
            'status_aktif' => $validatedData['status_aktif'],
            'status_persetujuan' => $validatedData['status_persetujuan'],
        ]);

        // Kembalikan response JSON 200
        return response()->json([
            'message' => 'Data dosen berhasil diperbarui.'
        ], 200);
    }

    /**
     * Menghapus data dosen berdasarkan ID.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        // Cari pengguna berdasarkan ID
        $dosen = Pengguna::find($id);

        // Jika data tidak ditemukan atau role bukan Dosen, kembalikan 404
        if (!$dosen || $dosen->role->value !== 'Dosen') {
            return response()->json([
                'message' => 'Data dosen tidak ditemukan.'
            ], 404);
        }

        // Lakukan proses delete record dari database
        $dosen->delete();

        // Kembalikan response JSON 200
        return response()->json([
            'message' => 'Data dosen berhasil dihapus.'
        ], 200);
    }
}
