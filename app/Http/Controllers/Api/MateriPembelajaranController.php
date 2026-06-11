<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MateriPembelajaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MateriPembelajaranController extends Controller
{
    /**
     * Upload materi pembelajaran untuk suatu sesi.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'id_sesi' => 'required|uuid|exists:sesi_pertemuan,id_sesi',
            'judul_materi' => 'required|string|max:200',
            'file_materi' => 'nullable|file|max:51200', // max 50MB
            'link_video_pembelajaran' => 'nullable|url',
        ]);

        $fileMateri = null;

        // Handle file upload
        if ($request->hasFile('file_materi')) {
            $file = $request->file('file_materi');
            $filename = 'materi/' . Str::uuid() . '_' . $file->getClientOriginalName();
            Storage::disk('public')->put($filename, file_get_contents($file));
            $fileMateri = $filename;
        }

        $materi = MateriPembelajaran::create([
            'id_sesi' => $request->id_sesi,
            'judul_materi' => $request->judul_materi,
            'file_materi' => $fileMateri,
            'link_video_pembelajaran' => $request->link_video_pembelajaran,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Materi pembelajaran berhasil diupload.',
            'data' => $materi,
        ], 201);
    }

    /**
     * Update materi pembelajaran.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'judul_materi' => 'nullable|string|max:200',
            'file_materi' => 'nullable|file|max:51200', // max 50MB
            'link_video_pembelajaran' => 'nullable|url',
        ]);

        $materi = MateriPembelajaran::find($id);

        if (!$materi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Materi tidak ditemukan.',
            ], 404);
        }

        $updateData = [];

        if ($request->filled('judul_materi')) {
            $updateData['judul_materi'] = $request->judul_materi;
        }

        if ($request->filled('link_video_pembelajaran')) {
            $updateData['link_video_pembelajaran'] = $request->link_video_pembelajaran;
        }

        // Handle file upload
        if ($request->hasFile('file_materi')) {
            // Delete old file
            if ($materi->file_materi) {
                Storage::disk('public')->delete($materi->file_materi);
            }

            $file = $request->file('file_materi');
            $filename = 'materi/' . Str::uuid() . '_' . $file->getClientOriginalName();
            Storage::disk('public')->put($filename, file_get_contents($file));
            $updateData['file_materi'] = $filename;
        }

        $materi->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Materi pembelajaran berhasil diupdate.',
            'data' => $materi,
        ], 200);
    }

    /**
     * Hapus materi pembelajaran (soft delete).
     */
    public function hapus($id): JsonResponse
    {
        $materi = MateriPembelajaran::find($id);

        if (!$materi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Materi tidak ditemukan.',
            ], 404);
        }

        // Delete file if exists
        if ($materi->file_materi) {
            Storage::disk('public')->delete($materi->file_materi);
        }

        $materi->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Materi pembelajaran berhasil dihapus.',
        ], 200);
    }

    /**
     * Get materi berdasarkan sesi.
     */
    public function getBySesi($id_sesi): JsonResponse
    {
        $materi = MateriPembelajaran::where('id_sesi', $id_sesi)->get();

        return response()->json([
            'status' => 'success',
            'data' => $materi,
        ], 200);
    }

    /**
     * Generate link download untuk materi.
     */
    public function generateLinkDownload($id): JsonResponse
    {
        $materi = MateriPembelajaran::find($id);

        if (!$materi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Materi tidak ditemukan.',
            ], 404);
        }

        if (!$materi->file_materi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Materi tidak memiliki file.',
            ], 404);
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($materi->file_materi)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File materi tidak ditemukan.',
            ], 404);
        }

        // Generate temporary download URL (valid for 1 hour)
        $downloadUrl = Storage::disk('public')->temporaryUrl(
            $materi->file_materi,
            now()->addHour()
        );

        return response()->json([
            'status' => 'success',
            'download_url' => $downloadUrl,
            'expired_at' => now()->addHour()->toIso8601String(),
        ], 200);
    }
}
