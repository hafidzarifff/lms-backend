<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Mengambil daftar notifikasi milik pengguna yang sedang login
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $notifikasi = Notifikasi::where('id_user', Auth::id())
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifikasi
        ], 200);
    }

    /**
     * Mengubah status notifikasi menjadi sudah dibaca
     */
    public function markAsRead($id)
    {
        $notifikasi = Notifikasi::where('id_notifikasi', $id)
            ->where('id_user', Auth::id())
            ->first();

        if (!$notifikasi) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan atau tidak memiliki akses'
            ], 404);
        }

        $notifikasi->is_read = true;
        $notifikasi->save();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca'
        ], 200);
    }
}
