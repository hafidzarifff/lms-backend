<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumDiskusi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForumDiskusiController extends Controller
{
    /**
     * Get semua pesan top-level (bukan reply) untuk sesi tertentu
     */
    public function index(Request $request, $idSesi)
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = $request->input('per_page', 10);

        $pesan = ForumDiskusi::where('id_sesi', $idSesi)
            ->topLevel()
            ->with(['pengirim', 'replies.pengirim'])
            ->withCount('replies')
            ->orderBy('waktu_kirim', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pesan,
        ]);
    }

    /**
     * Kirim pesan baru (top-level atau reply)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_sesi' => 'required|uuid|exists:sesi_pertemuan,id_sesi',
            'isi_pesan' => 'required|string|max:5000',
            'id_parent_pesan' => 'nullable|uuid|exists:forum_diskusi,id_pesan',
        ]);

        $validated['id_pengirim'] = Auth::id();
        $validated['waktu_kirim'] = now();

        $pesan = ForumDiskusi::create($validated);
        $pesan->load(['pengirim', 'parentPesan']);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dikirim',
            'data' => $pesan,
        ], 201);
    }

    /**
     * Get detail pesan tertentu
     */
    public function show($idPesan)
    {
        $pesan = ForumDiskusi::with(['pengirim', 'parentPesan', 'replies.pengirim'])
            ->withCount('replies')
            ->findOrFail($idPesan);

        return response()->json([
            'success' => true,
            'data' => $pesan,
        ]);
    }

    /**
     * Get replies dari pesan tertentu
     */
    public function getReplies(Request $request, $idPesan)
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = $request->input('per_page', 10);

        $replies = ForumDiskusi::where('id_parent_pesan', $idPesan)
            ->with(['pengirim', 'replies.pengirim'])
            ->withCount('replies')
            ->orderBy('waktu_kirim', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $replies,
        ]);
    }

    /**
     * Update pesan (hanya pengirim yang bisa edit)
     */
    public function update(Request $request, $idPesan)
    {
        $pesan = ForumDiskusi::findOrFail($idPesan);

        // Cek apakah user adalah pengirim
        if ($pesan->id_pengirim !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengedit pesan ini',
            ], 403);
        }

        $validated = $request->validate([
            'isi_pesan' => 'required|string|max:5000',
        ]);

        $pesan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil diupdate',
            'data' => $pesan,
        ]);
    }

    /**
     * Delete pesan (soft delete, hanya pengirim yang bisa delete)
     */
    public function destroy($idPesan)
    {
        $pesan = ForumDiskusi::findOrFail($idPesan);

        // Cek apakah user adalah pengirim
        if ($pesan->id_pengirim !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus pesan ini',
            ], 403);
        }

        $pesan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dihapus',
        ]);
    }

    /**
     * Search pesan dalam sesi tertentu
     */
    public function search(Request $request, $idSesi)
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = $request->input('per_page', 10);
        $keyword = $request->input('q');

        $pesan = ForumDiskusi::where('id_sesi', $idSesi)
            ->where('isi_pesan', 'ilike', "%{$keyword}%")
            ->with(['pengirim', 'parentPesan'])
            ->withCount('replies')
            ->orderBy('waktu_kirim', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pesan,
        ]);
    }
}
