<?php

namespace App\Http\Controllers;

use App\Models\PertanyaanEvaluasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PertanyaanEvaluasiController extends Controller
{
    /**
     * Get semua pertanyaan evaluasi
     */
    public function index(Request $request)
    {
        $query = PertanyaanEvaluasi::query();

        // Filter by kategori
        if ($request->has('kategori')) {
            $query->kategori($request->kategori);
        }

        // Filter by status aktif
        if ($request->has('aktif')) {
            if ($request->aktif === 'true') {
                $query->aktif();
            }
        }

        $pertanyaan = $query->orderBy('kategori')
            ->orderBy('urutan')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pertanyaan
        ]);
    }

    /**
     * Get pertanyaan aktif saja (untuk form evaluasi mahasiswa)
     */
    public function getAktif()
    {
        $pertanyaan = PertanyaanEvaluasi::aktif()
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->get()
            ->groupBy('kategori');

        return response()->json([
            'status' => 'success',
            'data' => $pertanyaan
        ]);
    }

    /**
     * Get detail pertanyaan
     */
    public function show($id_pertanyaan)
    {
        $pertanyaan = PertanyaanEvaluasi::find($id_pertanyaan);

        if (!$pertanyaan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertanyaan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pertanyaan
        ]);
    }

    /**
     * Create pertanyaan baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori' => 'required|string|max:50',
            'teks_pertanyaan' => 'required|string',
            'urutan' => 'required|integer|min:1',
            'is_aktif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $pertanyaan = PertanyaanEvaluasi::create([
            'id_pertanyaan' => Str::uuid(),
            'kategori' => $request->kategori,
            'teks_pertanyaan' => $request->teks_pertanyaan,
            'urutan' => $request->urutan,
            'is_aktif' => $request->is_aktif ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pertanyaan evaluasi berhasil dibuat',
            'data' => $pertanyaan
        ], 201);
    }

    /**
     * Update pertanyaan
     */
    public function update(Request $request, $id_pertanyaan)
    {
        $pertanyaan = PertanyaanEvaluasi::find($id_pertanyaan);

        if (!$pertanyaan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertanyaan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kategori' => 'nullable|string|max:50',
            'teks_pertanyaan' => 'nullable|string',
            'urutan' => 'nullable|integer|min:1',
            'is_aktif' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $pertanyaan->update($request->only([
            'kategori',
            'teks_pertanyaan',
            'urutan',
            'is_aktif',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Pertanyaan berhasil diupdate',
            'data' => $pertanyaan
        ]);
    }

    /**
     * Delete pertanyaan (soft delete)
     */
    public function destroy($id_pertanyaan)
    {
        $pertanyaan = PertanyaanEvaluasi::find($id_pertanyaan);

        if (!$pertanyaan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertanyaan tidak ditemukan'
            ], 404);
        }

        $pertanyaan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pertanyaan berhasil dihapus'
        ]);
    }

    /**
     * Toggle status aktif pertanyaan
     */
    public function toggleAktif($id_pertanyaan)
    {
        $pertanyaan = PertanyaanEvaluasi::find($id_pertanyaan);

        if (!$pertanyaan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertanyaan tidak ditemukan'
            ], 404);
        }

        $pertanyaan->update([
            'is_aktif' => !$pertanyaan->is_aktif
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status aktif berhasil diubah',
            'data' => $pertanyaan
        ]);
    }

    /**
     * Get list kategori yang tersedia
     */
    public function getKategori()
    {
        $kategori = PertanyaanEvaluasi::select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return response()->json([
            'status' => 'success',
            'data' => $kategori
        ]);
    }

    /**
     * Bulk update urutan pertanyaan
     */
    public function bulkUpdateUrutan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'urutan' => 'required|array',
            'urutan.*.id_pertanyaan' => 'required|uuid|exists:pertanyaan_evaluasi,id_pertanyaan',
            'urutan.*.urutan' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->urutan as $item) {
            PertanyaanEvaluasi::where('id_pertanyaan', $item['id_pertanyaan'])
                ->update(['urutan' => $item['urutan']]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Urutan pertanyaan berhasil diupdate'
        ]);
    }
}
