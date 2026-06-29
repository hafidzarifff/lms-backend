<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Pengguna;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        // Remove sensitive fields just in case
        $user->makeHidden(['password', 'status_aktif', 'status_persetujuan']);
        
        // Add full URL for foto_profil if exists
        if ($user->foto_profil) {
            $user->foto_profil_url = Storage::disk('public')->url('foto-profil/' . $user->foto_profil);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:pengguna,email,' . $user->id_user . ',id_user',
            'nomor_telepon' => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user
        ], 200);
    }

    /**
     * Upload a new profile picture.
     */
    public function uploadFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $user->id_user . '.' . $file->getClientOriginalExtension();
            
            // Pastikan direktori sudah ada (aman dipanggil berulang kali)
            Storage::disk('public')->makeDirectory('foto-profil');

            // Hapus foto lama jika ada
            if ($user->foto_profil && Storage::disk('public')->exists('foto-profil/' . $user->foto_profil)) {
                Storage::disk('public')->delete('foto-profil/' . $user->foto_profil);
            }

            // Simpan foto baru ke public disk
            $file->storeAs('foto-profil', $filename, 'public');

            $user->update(['foto_profil' => $filename]);
            $user->foto_profil_url = Storage::disk('public')->url('foto-profil/' . $filename);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui.',
                'data' => [
                    'foto_profil' => $filename,
                    'foto_profil_url' => $user->foto_profil_url
                ]
            ], 200);
        }

        return response()->json(['success' => false, 'message' => 'Tidak ada file foto yang diunggah.'], 400);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.min' => 'Kata sandi baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi lama tidak sesuai.'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diperbarui.'
        ], 200);
    }

    /**
     * Onboarding Mahasiswa: Update email and password, then set is_first_login = false.
     */
    public function onboarding(Request $request)
    {
        $user = $request->user();

        // Only for users who haven't completed first login
        if (!$user->is_first_login) {
            return response()->json([
                'success' => false,
                'message' => 'Onboarding sudah diselesaikan sebelumnya.'
            ], 400);
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:pengguna,email,' . $user->id_user . ',id_user',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.min' => 'Kata sandi baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user->update([
            'email' => $validated['email'],
            'password' => Hash::make($validated['new_password']),
            'is_first_login' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembaruan data berhasil. Akun Anda siap digunakan.'
        ], 200);
    }
}
