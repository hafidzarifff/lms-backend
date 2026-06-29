<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle the callback from Google OAuth
     */
    public function callback(Request $request)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find user by email
            $user = Pengguna::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Return to frontend with error (email not registered)
                return redirect()->away($frontendUrl . '/auth/callback?error=Akun belum terdaftar di sistem. Hubungi Admin.');
            }

            if (!$user->status_aktif || $user->status_persetujuan !== 'Disetujui') {
                return redirect()->away($frontendUrl . '/auth/callback?error=Akun Anda tidak aktif atau sedang menunggu verifikasi.');
            }

            // Generate token
            $token = $user->createToken('google-auth-token')->plainTextToken;

            // Redirect back to frontend with the token
            return redirect()->away($frontendUrl . '/auth/callback?token=' . $token);
        } catch (\Exception $e) {
            return redirect()->away($frontendUrl . '/auth/callback?error=Terjadi kesalahan saat otentikasi Google. Silakan coba lagi.');
        }
    }
}
