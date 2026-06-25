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
            'deskripsi' => 'nullable|string',
            'file_materi' => 'nullable|array',
            'file_materi.*' => 'file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', // max 50MB per file
            'link_video_pembelajaran' => 'nullable|url',
        ]);

        $fileMateri = [];

        // Handle multiple file upload
        if ($request->hasFile('file_materi')) {
            foreach ($request->file('file_materi') as $file) {
                $filename = 'materi/' . Str::uuid() . '_' . $file->getClientOriginalName();
                Storage::disk('public')->put($filename, file_get_contents($file));
                $fileMateri[] = $filename;
            }
        }

        $materi = MateriPembelajaran::create([
            'id_sesi' => $request->id_sesi,
            'judul_materi' => $request->judul_materi,
            'deskripsi' => $request->deskripsi,
            'file_materi' => empty($fileMateri) ? null : $fileMateri,
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
            'deskripsi' => 'nullable|string',
            'file_materi' => 'nullable|array',
            'file_materi.*' => 'file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx', // max 50MB per file
            'kept_files' => 'nullable|array',
            'kept_files.*' => 'string',
            'has_kept_files' => 'nullable|boolean',
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

        if ($request->has('deskripsi')) {
            $updateData['deskripsi'] = $request->deskripsi;
        }

        if ($request->filled('link_video_pembelajaran')) {
            $updateData['link_video_pembelajaran'] = $request->link_video_pembelajaran;
        }

        if ($request->has('has_kept_files')) {
            $existingFiles = $materi->file_materi ?? [];
            $keptFiles = $request->input('kept_files', []);
            $filesToDelete = array_diff($existingFiles, $keptFiles);
            foreach ($filesToDelete as $fileToDelete) {
                Storage::disk('public')->delete($fileToDelete);
            }
            $finalFiles = $keptFiles;
        } else {
            $finalFiles = $materi->file_materi ?? [];
        }

        if ($request->hasFile('file_materi')) {
            foreach ($request->file('file_materi') as $file) {
                $filename = 'materi/' . Str::uuid() . '_' . $file->getClientOriginalName();
                Storage::disk('public')->put($filename, file_get_contents($file));
                $finalFiles[] = $filename;
            }
        }

        $updateData['file_materi'] = empty($finalFiles) ? null : array_values($finalFiles);

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

        // Delete files if exist
        if (!empty($materi->file_materi)) {
            foreach ($materi->file_materi as $file) {
                Storage::disk('public')->delete($file);
            }
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
     * Get semua materi berdasarkan jadwal (semua sesi dalam jadwal).
     */
    public function getByJadwal($id_jadwal): JsonResponse
    {
        $sesiIds = \App\Models\SesiPertemuan::where('id_jadwal', $id_jadwal)
            ->orderBy('pertemuan_ke', 'asc')
            ->pluck('id_sesi');

        $materi = MateriPembelajaran::whereIn('id_sesi', $sesiIds)
            ->with('sesiPertemuan:id_sesi,pertemuan_ke,judul_sesi')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id_materi,
                    'id_sesi' => $item->id_sesi,
                    'pertemuan' => $item->sesiPertemuan ? 'Pertemuan ke-' . $item->sesiPertemuan->pertemuan_ke : '-',
                    'pertemuan_ke' => $item->sesiPertemuan->pertemuan_ke ?? 0,
                    'judul' => $item->judul_materi,
                    'deskripsi' => $item->deskripsi,
                    'file_materi' => $item->file_materi,
                    'jumlah_file' => is_array($item->file_materi) ? count($item->file_materi) : 0,
                    'link_video' => $item->link_video_pembelajaran,
                    'tanggal' => $item->created_at ? $item->created_at->format('d F Y') : '-',
                    'created_at' => $item->created_at,
                ];
            });

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

        $urls = [];
        if (!empty($materi->file_materi)) {
            foreach ($materi->file_materi as $file) {
                if (Storage::disk('public')->exists($file)) {
                    // Try to generate temporary URL if supported, otherwise fallback to standard URL
                    try {
                        $url = Storage::disk('public')->temporaryUrl($file, now()->addHour());
                    } catch (\Exception $e) {
                        $url = url('storage/' . $file);
                    }
                    $urls[] = [
                        'file' => $file,
                        'url' => $url
                    ];
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'download_urls' => $urls,
        ], 200);
    }

    /**
     * Force download file materi
     */
    public function downloadFile(Request $request)
    {
        $path = $request->query('path');
        if (!$path || !Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }
        
        $filename = basename($path);
        $cleanName = preg_replace('/^[a-f0-9\-]+_/', '', $filename);

        return Storage::disk('public')->download($path, $cleanName);
    }

    public function publicDownload(Request $request)
    {
        $path = $request->query('path');
        $title = $request->query('title');

        if (!$path || !Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }
        
        $filename = basename($path);
        $cleanName = preg_replace('/^[a-f0-9\-]+_/', '', $filename);
        
        if ($title) {
            // Ensure title is safe for filename
            $safeTitle = preg_replace('/[^A-Za-z0-9\-_\s]/', '', $title);
            $cleanName = $safeTitle . '-' . $cleanName;
        }

        return Storage::disk('public')->download($path, $cleanName);
    }
}
