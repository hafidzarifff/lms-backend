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
            'per_page' => 'nullable|integer|min:1|max:200',
        ]);

        $perPage = $request->input('per_page', 100);

        $pesan = ForumDiskusi::where('id_sesi', $idSesi)
            ->with(['pengirim', 'parentPesan.pengirim', 'sesi'])
            ->withExists(['reads as is_read' => function($q) {
                $q->where('id_user', Auth::id());
            }])
            ->orderBy('waktu_kirim', 'asc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pesan,
        ]);
    }

    /**
     * Get semua pesan untuk satu jadwal (seluruh sesi di dalamnya)
     */
    public function getByJadwal(Request $request, $idJadwal)
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:200',
        ]);

        $perPage = $request->input('per_page', 100);

        // Ambil semua id_sesi dari jadwal tersebut
        $sesiIds = \App\Models\SesiPertemuan::where('id_jadwal', $idJadwal)
            ->pluck('id_sesi');

        $pesan = ForumDiskusi::whereIn('id_sesi', $sesiIds)
            ->with(['pengirim', 'parentPesan.pengirim', 'sesi'])
            ->orderBy('waktu_kirim', 'desc') // Biasa untuk list table, urut dari yang terbaru
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pesan,
        ]);
    }

    /**
     * Get semua pesan untuk dosen yang login (diambil dari semua jadwal yang dia ajar)
     */
    public function getAllForDosen(Request $request)
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:1000',
        ]);

        $perPage = $request->input('per_page', 100);

        // Ambil semua id_jadwal yang diajar oleh Dosen
        $jadwalIds = \App\Models\JadwalPerkuliahan::where('id_dosen', Auth::id())
            ->pluck('id_jadwal');

        // Ambil semua id_sesi dari jadwal-jadwal tersebut
        $sesiIds = \App\Models\SesiPertemuan::whereIn('id_jadwal', $jadwalIds)
            ->pluck('id_sesi');

        $pesan = ForumDiskusi::whereIn('id_sesi', $sesiIds)
            ->with(['pengirim', 'parentPesan.pengirim', 'sesi.jadwalPerkuliahan.mataKuliah', 'sesi.jadwalPerkuliahan.kelas'])
            ->withExists(['reads as is_read' => function($q) {
                $q->where('id_user', Auth::id());
            }])
            ->orderBy('waktu_kirim', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pesan,
        ]);
    }

    /**
     * Tandai semua pesan di satu sesi sebagai sudah dibaca
     */
    public function markAsRead(Request $request, $idSesi)
    {
        $userId = Auth::id();
        $pesanIds = ForumDiskusi::where('id_sesi', $idSesi)->pluck('id_pesan');
        
        $existingReads = \App\Models\ForumDiskusiRead::whereIn('id_pesan', $pesanIds)
            ->where('id_user', $userId)
            ->pluck('id_pesan')
            ->toArray();
            
        $newReads = [];
        foreach($pesanIds as $idPesan) {
            if(!in_array($idPesan, $existingReads)) {
                $newReads[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'id_pesan' => $idPesan,
                    'id_user' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if(count($newReads) > 0) {
            \App\Models\ForumDiskusiRead::insert($newReads);
        }
        
        return response()->json(['success' => true]);
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
        $pesan = ForumDiskusi::with(['pengirim', 'parentPesan', 'replies.pengirim', 'sesi'])
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

        // Cek apakah user adalah pengirim atau admin
        if ($pesan->id_pengirim !== Auth::id() && Auth::user()->role->value !== 'Admin') {
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
            ->with(['pengirim', 'parentPesan', 'sesi'])
            ->withCount('replies')
            ->orderBy('waktu_kirim', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $pesan,
        ]);
    }
}
